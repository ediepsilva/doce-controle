<?php
require_once 'config.php';
$temImagemReceita = doce_coluna_existe($pdo, 'receitas', 'imagem_produto');
$temMostrarCardapio = doce_coluna_existe($pdo, 'receitas', 'mostrar_cardapio');
$temDescricaoPublica = doce_coluna_existe($pdo, 'receitas', 'descricao_publica');
$temIngredientesTexto = doce_coluna_existe($pdo, 'receitas', 'ingredientes_texto');
$temModoPreparo = doce_coluna_existe($pdo, 'receitas', 'modo_preparo');

function salvar_imagem_produto($campo, $imagemAtual = '')
{
    if (empty($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $imagemAtual;
    }

    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        return $imagemAtual;
    }

    if ($_FILES[$campo]['size'] > 3 * 1024 * 1024) {
        return $imagemAtual;
    }

    $tmp = $_FILES[$campo]['tmp_name'];
    $info = @getimagesize($tmp);
    if (!$info) {
        return $imagemAtual;
    }

    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = $info['mime'] ?? '';
    if (!isset($extensoes[$mime])) {
        return $imagemAtual;
    }

    $pasta = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'produtos';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeArquivo = 'produto_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extensoes[$mime];
    $destino = $pasta . DIRECTORY_SEPARATOR . $nomeArquivo;

    if (!move_uploaded_file($tmp, $destino)) {
        return $imagemAtual;
    }

    if ($imagemAtual && strpos($imagemAtual, 'uploads/produtos/') === 0) {
        $arquivoAntigo = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagemAtual);
        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }

    return 'uploads/produtos/' . $nomeArquivo;
}

function normalizar_texto_ingrediente($texto)
{
    return trim(preg_replace('/\s+/', ' ', (string)$texto));
}

function extrair_ingrediente_do_texto($texto)
{
    $linha = normalizar_texto_ingrediente($texto);
    if ($linha === '') {
        return null;
    }

    $linha = preg_replace('/^[\-\*•]\s*/', '', $linha);
    $linha = trim($linha);

    $quantidade = 1.0;
    $unidade = 'un';
    $nome = $linha;

    if (preg_match('/^([0-9]+(?:[.,][0-9]+)?)\s*(kg|g|mg|ml|l|litro|litros|colher|colheres|xícara|xícaras|pacote|pacotes|copo|copos|un|und|unidade|unidades)?\s*(.+)$/i', $linha, $m)) {
        $quantidade = floatval(str_replace(',', '.', $m[1]));
        $unidade = strtolower($m[2] ?? 'un');
        $nome = trim($m[3] ?? '');
    } elseif (preg_match('/^([0-9]+(?:[.,][0-9]+)?)\s*(.+)$/', $linha, $m)) {
        $quantidade = floatval(str_replace(',', '.', $m[1]));
        $nome = trim($m[2] ?? '');
    }

    $mapaUnidades = [
        'kg' => 'kg',
        'g' => 'g',
        'mg' => 'mg',
        'ml' => 'ml',
        'l' => 'l',
        'litro' => 'l',
        'litros' => 'l',
        'colher' => 'colher',
        'colheres' => 'colher',
        'xícara' => 'xícara',
        'xícaras' => 'xícara',
        'pacote' => 'pacote',
        'pacotes' => 'pacote',
        'copo' => 'copo',
        'copos' => 'copo',
        'un' => 'un',
        'und' => 'un',
        'unidade' => 'un',
        'unidades' => 'un',
    ];

    if ($nome === '') {
        return null;
    }

    return [
        'nome' => $nome,
        'quantidade' => $quantidade > 0 ? $quantidade : 1.0,
        'unidade' => $mapaUnidades[$unidade] ?? 'un',
    ];
}

function criar_ou_recuperar_insumo($pdo, $user_id, $nome, $unidade)
{
    $nome = trim((string)$nome);
    if ($nome === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id FROM estoque WHERE user_id = ? AND LOWER(item_nome) = LOWER(?) LIMIT 1");
    $stmt->execute([$user_id, $nome]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        return intval($item['id']);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO estoque (user_id, item_nome, unidade_medida, preco_unitario, quantidade_atual, estoque_minimo)
         VALUES (?, ?, ?, 0, 0, 0)"
    );
    $stmt->execute([$user_id, $nome, $unidade ?: 'un']);

    return intval($pdo->lastInsertId());
}

function receita_tem_todos_ingredientes_com_preco($pdo, $receita_id)
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total_itens,
                SUM(CASE WHEN e.preco_unitario IS NULL OR e.preco_unitario <= 0 THEN 1 ELSE 0 END) AS itens_sem_preco
         FROM receitas_itens ri
         LEFT JOIN estoque e ON e.id = ri.insumo_id
         WHERE ri.receita_id = ?"
    );
    $stmt->execute([$receita_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalItens = intval($dados['total_itens'] ?? 0);
    $itensSemPreco = intval($dados['itens_sem_preco'] ?? 0);

    return $totalItens > 0 && $itensSemPreco === 0;
}

function importar_ingredientes_receita_publica($pdo, $user_id, $receita_id, $receita_publica_id)
{
    if ($receita_publica_id <= 0) {
        return;
    }

    if (!defined('DOCE_APP_RETURN_CATALOGO')) {
        define('DOCE_APP_RETURN_CATALOGO', true);
    }
    $catalogo = include __DIR__ . '/api_receitas_publicas.php';

    $receitaPublica = null;
    foreach ($catalogo as $receitaCatalogo) {
        if (intval($receitaCatalogo['id']) === $receita_publica_id) {
            $receitaPublica = $receitaCatalogo;
            break;
        }
    }

    if (!$receitaPublica || empty($receitaPublica['ingredientes']) || !function_exists('chave_ingrediente_custo')) {
        return;
    }

    $stmt = $pdo->prepare("SELECT id, item_nome, unidade_medida, preco_unitario FROM estoque WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $estoquePorChave = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $itemEstoque) {
        $chave = chave_ingrediente_custo($itemEstoque['item_nome']);
        if (!isset($estoquePorChave[$chave]) || floatval($itemEstoque['preco_unitario']) > floatval($estoquePorChave[$chave]['preco_unitario'])) {
            $estoquePorChave[$chave] = $itemEstoque;
        }
    }

    $insertEstoque = $pdo->prepare(
        "INSERT INTO estoque (user_id, item_nome, unidade_medida, preco_unitario, quantidade_atual, estoque_minimo)
         VALUES (?, ?, ?, 0, 0, 0)"
    );
    $insertItem = $pdo->prepare(
        "INSERT INTO receitas_itens (receita_id, insumo_id, quantidade_usada)
         VALUES (?, ?, ?)"
    );

    foreach ($receitaPublica['ingredientes'] as $ingrediente) {
        $chave = chave_ingrediente_custo($ingrediente['item_nome']);
        $itemEstoque = $estoquePorChave[$chave] ?? null;

        if (!$itemEstoque) {
            $insertEstoque->execute([$user_id, $ingrediente['item_nome'], $ingrediente['unidade_medida'] ?: 'un']);
            $itemEstoque = [
                'id' => $pdo->lastInsertId(),
                'item_nome' => $ingrediente['item_nome'],
                'unidade_medida' => $ingrediente['unidade_medida'] ?: 'un',
                'preco_unitario' => 0,
            ];
            $estoquePorChave[$chave] = $itemEstoque;
        }

        $insertItem->execute([
            $receita_id,
            $itemEstoque['id'],
            floatval($ingrediente['quantidade_usada']),
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !doce_validar_csrf()) {
    header('Location: receitas.php');
    exit;
}

{
    $user_id = $_SESSION['user_id'];
    $nome = filter_input(INPUT_POST, 'nome_receita', FILTER_SANITIZE_SPECIAL_CHARS);
    $rendimento = intval($_POST['rendimento_porcoes'] ?? 0);
    $preco = floatval($_POST['preco_venda_sugerido'] ?? 0);
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $receita_publica_id = intval($_POST['receita_publica_id'] ?? 0);
    $mostrarCardapio = isset($_POST['mostrar_cardapio']) ? 1 : 0;
    $descricaoPublica = trim((string)($_POST['descricao_publica'] ?? ''));
    $ingredientesTexto = trim((string)($_POST['ingredientes_texto'] ?? ''));
    $modoPreparo = trim((string)($_POST['modo_preparo'] ?? ''));
    $modoCardapio = isset($_POST['modo_cardapio']) ? 1 : 0;

    $deveSalvar = !empty($nome) && $rendimento > 0;
    if ($modoCardapio && $id && !empty($nome)) {
        $deveSalvar = true;
    }

    if ($deveSalvar) {
        if ($id) {
            if ($modoCardapio) {
                $stmt = $pdo->prepare("SELECT nome_receita, rendimento_porcoes, preco_venda_sugerido, imagem_produto FROM receitas WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                $receitaAtual = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($receitaAtual) {
                    $nome = $nome !== '' ? $nome : ($receitaAtual['nome_receita'] ?? '');
                    $rendimento = $rendimento > 0 ? $rendimento : intval($receitaAtual['rendimento_porcoes'] ?? 1);
                    $preco = $preco > 0 ? $preco : floatval($receitaAtual['preco_venda_sugerido'] ?? 0);
                    $mostrarCardapio = 1;
                }
            }

            if ($temImagemReceita) {
                $stmt = $pdo->prepare("SELECT imagem_produto FROM receitas WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                $receitaAtual = $stmt->fetch();
                $imagemProduto = salvar_imagem_produto('imagem_produto', $receitaAtual['imagem_produto'] ?? '');

                $stmt = $pdo->prepare("UPDATE receitas SET nome_receita = ?, rendimento_porcoes = ?, preco_venda_sugerido = ?, imagem_produto = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$nome, $rendimento, $preco, $imagemProduto, $id, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE receitas SET nome_receita = ?, rendimento_porcoes = ?, preco_venda_sugerido = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$nome, $rendimento, $preco, $id, $user_id]);
            }

            if ($receita_publica_id > 0) {
                $stmt = $pdo->prepare("SELECT COUNT(*) AS total_itens FROM receitas_itens WHERE receita_id = ?");
                $stmt->execute([$id]);
                $totalItensReceita = $stmt->fetchColumn();
                if (intval($totalItensReceita) === 0) {
                    importar_ingredientes_receita_publica($pdo, $user_id, $id, $receita_publica_id);
                }
            }

            if ($temIngredientesTexto && $ingredientesTexto !== '') {
                $pdo->prepare("DELETE FROM receitas_itens WHERE receita_id = ?")->execute([$id]);
                $linhas = preg_split('/\r\n|\r|\n/', $ingredientesTexto);
                $insertItem = $pdo->prepare("INSERT INTO receitas_itens (receita_id, insumo_id, quantidade_usada) VALUES (?, ?, ?)");
                foreach ($linhas as $linha) {
                    $ingrediente = extrair_ingrediente_do_texto($linha);
                    if (!$ingrediente) {
                        continue;
                    }
                    $insumoId = criar_ou_recuperar_insumo($pdo, $user_id, $ingrediente['nome'], $ingrediente['unidade']);
                    if ($insumoId) {
                        $insertItem->execute([$id, $insumoId, floatval($ingrediente['quantidade'])]);
                    }
                }
            }

            if ($temMostrarCardapio) {
                $mostrarCardapio = $mostrarCardapio && receita_tem_todos_ingredientes_com_preco($pdo, $id);
            }

            if ($temMostrarCardapio || $temDescricaoPublica || $temIngredientesTexto || $temModoPreparo) {
                $campos = [];
                $valores = [];

                if ($temMostrarCardapio) {
                    $campos[] = 'mostrar_cardapio = ?';
                    $valores[] = $mostrarCardapio;
                }
                if ($temDescricaoPublica) {
                    $campos[] = 'descricao_publica = ?';
                    $valores[] = $descricaoPublica;
                }
                if ($temIngredientesTexto) {
                    $campos[] = 'ingredientes_texto = ?';
                    $valores[] = $ingredientesTexto;
                }
                if ($temModoPreparo) {
                    $campos[] = 'modo_preparo = ?';
                    $valores[] = $modoPreparo;
                }

                $valores[] = $id;
                $valores[] = $user_id;
                $stmt = $pdo->prepare("UPDATE receitas SET " . implode(', ', $campos) . " WHERE id = ? AND user_id = ?");
                $stmt->execute($valores);
            }
        } else {
            if ($temImagemReceita) {
                $imagemProduto = salvar_imagem_produto('imagem_produto');
                $colunas = ['user_id', 'nome_receita', 'rendimento_porcoes', 'preco_venda_sugerido', 'imagem_produto'];
                $placeholders = ['?', '?', '?', '?', '?'];
                $valores = [$user_id, $nome, $rendimento, $preco, $imagemProduto];
            } else {
                $colunas = ['user_id', 'nome_receita', 'rendimento_porcoes', 'preco_venda_sugerido'];
                $placeholders = ['?', '?', '?', '?'];
                $valores = [$user_id, $nome, $rendimento, $preco];
            }

            if ($temMostrarCardapio) {
                $colunas[] = 'mostrar_cardapio';
                $placeholders[] = '?';
                $valores[] = $mostrarCardapio;
            }
            if ($temDescricaoPublica) {
                $colunas[] = 'descricao_publica';
                $placeholders[] = '?';
                $valores[] = $descricaoPublica;
            }
            if ($temIngredientesTexto) {
                $colunas[] = 'ingredientes_texto';
                $placeholders[] = '?';
                $valores[] = $ingredientesTexto;
            }
            if ($temModoPreparo) {
                $colunas[] = 'modo_preparo';
                $placeholders[] = '?';
                $valores[] = $modoPreparo;
            }

            $stmt = $pdo->prepare("INSERT INTO receitas (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $placeholders) . ")");
            $stmt->execute($valores);
            $receitaId = intval($pdo->lastInsertId());

            if ($receita_publica_id > 0) {
                importar_ingredientes_receita_publica($pdo, $user_id, $receitaId, $receita_publica_id);
            }

            if ($temIngredientesTexto && $ingredientesTexto !== '') {
                $pdo->prepare("DELETE FROM receitas_itens WHERE receita_id = ?")->execute([$receitaId]);
                $linhas = preg_split('/\r\n|\r|\n/', $ingredientesTexto);
                $insertItem = $pdo->prepare("INSERT INTO receitas_itens (receita_id, insumo_id, quantidade_usada) VALUES (?, ?, ?)");
                foreach ($linhas as $linha) {
                    $ingrediente = extrair_ingrediente_do_texto($linha);
                    if (!$ingrediente) {
                        continue;
                    }
                    $insumoId = criar_ou_recuperar_insumo($pdo, $user_id, $ingrediente['nome'], $ingrediente['unidade']);
                    if ($insumoId) {
                        $insertItem->execute([$receitaId, $insumoId, floatval($ingrediente['quantidade'])]);
                    }
                }
            }

            if ($temMostrarCardapio) {
                $mostrarCardapio = $mostrarCardapio && receita_tem_todos_ingredientes_com_preco($pdo, $receitaId);
                $stmt = $pdo->prepare("UPDATE receitas SET mostrar_cardapio = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$mostrarCardapio, $receitaId, $user_id]);
            }
        }
    }

    header('Location: receitas.php');
    exit;
}
