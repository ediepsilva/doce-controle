<?php
require_once 'config.php';
$temImagemReceita = doce_coluna_existe($pdo, 'receitas', 'imagem_produto');
$temMostrarCardapio = doce_coluna_existe($pdo, 'receitas', 'mostrar_cardapio');
$temDescricaoPublica = doce_coluna_existe($pdo, 'receitas', 'descricao_publica');

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
    $rendimento = intval($_POST['rendimento_porcoes']);
    $preco = floatval($_POST['preco_venda_sugerido']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $receita_publica_id = intval($_POST['receita_publica_id'] ?? 0);
    $mostrarCardapio = isset($_POST['mostrar_cardapio']) ? 1 : 0;
    $descricaoPublica = trim((string)($_POST['descricao_publica'] ?? ''));

    if (!empty($nome) && $rendimento > 0) {
        if ($id) {
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

            if ($temMostrarCardapio || $temDescricaoPublica) {
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

            $stmt = $pdo->prepare("INSERT INTO receitas (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $placeholders) . ")");
            $stmt->execute($valores);
            importar_ingredientes_receita_publica($pdo, $user_id, intval($pdo->lastInsertId()), $receita_publica_id);
        }
    }

    header('Location: receitas.php');
    exit;
}
