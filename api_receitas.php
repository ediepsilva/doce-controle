<?php
require_once 'config.php';
$header = 'Content-Type: application/json; charset=utf-8';
header($header);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuario nao autenticado.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$acao = $_REQUEST['acao'] ?? '';

$jsonBody = null;
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $jsonBody = json_decode($json, true);
}

function obterValor($nome)
{
    global $jsonBody;

    if ($jsonBody && array_key_exists($nome, $jsonBody)) {
        return $jsonBody[$nome];
    }
    if (isset($_REQUEST[$nome])) {
        return $_REQUEST[$nome];
    }
    return null;
}

$response = ['sucesso' => false, 'mensagem' => 'Ação inválida.', 'dados' => null];

switch ($acao) {
    case 'listar':
        $stmt = $pdo->prepare(
            "SELECT r.id, r.nome_receita, r.rendimento_porcoes, r.preco_venda_sugerido,
                    IFNULL(SUM(ri.quantidade_usada * e.preco_unitario), 0) AS custo_real
             FROM receitas r
             LEFT JOIN receitas_itens ri ON ri.receita_id = r.id
             LEFT JOIN estoque e ON e.id = ri.insumo_id
             WHERE r.user_id = ?
             GROUP BY r.id
             ORDER BY r.nome_receita ASC"
        );
        $stmt->execute([$user_id]);
        $receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($receitas)) {
            $ids = array_column($receitas, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare(
                "SELECT ri.receita_id, ri.quantidade_usada, e.item_nome, e.unidade_medida, e.preco_unitario,
                        (ri.quantidade_usada * e.preco_unitario) AS custo_item
                 FROM receitas_itens ri
                 JOIN estoque e ON e.id = ri.insumo_id
                 WHERE ri.receita_id IN ($placeholders)
                 ORDER BY e.item_nome ASC"
            );
            $stmt->execute($ids);
            $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $ingredientesPorReceita = [];
            foreach ($ingredientes as $ing) {
                $ingredientesPorReceita[$ing['receita_id']][] = $ing;
            }

            foreach ($receitas as &$receita) {
                $receita['ingredientes'] = $ingredientesPorReceita[$receita['id']] ?? [];
                $faltantes = [];
                foreach ($receita['ingredientes'] as $ingrediente) {
                    if (floatval($ingrediente['preco_unitario'] ?? 0) <= 0) {
                        $faltantes[] = $ingrediente['item_nome'];
                    }
                }
                $receita['origem'] = 'local';
                $receita['custo_real'] = round(floatval($receita['custo_real']), 2);
                $receita['custo_status'] = count($faltantes) > 0 ? 'parcial' : 'completo';
                $receita['ingredientes_sem_preco'] = array_values(array_unique($faltantes));
                $receita['preco_sugerido_calculado'] = round($receita['custo_real'] * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO, 2);
                $receita['multiplicador_preco'] = DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO;
            }
            unset($receita);
        }

        $response['sucesso'] = true;
        $response['mensagem'] = 'Lista de receitas carregada.';
        $response['dados'] = $receitas;
        break;

    case 'buscar':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $response['mensagem'] = 'ID da receita inválido.';
            break;
        }

        $stmt = $pdo->prepare('SELECT * FROM receitas WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $user_id]);
        $receita = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$receita) {
            $response['mensagem'] = 'Receita não encontrada.';
            break;
        }

        $stmt = $pdo->prepare(
            'SELECT ri.id, ri.insumo_id, ri.quantidade_usada, e.item_nome, e.unidade_medida
             FROM receitas_itens ri
             JOIN estoque e ON e.id = ri.insumo_id
             WHERE ri.receita_id = ?'
        );
        $stmt->execute([$id]);
        $receita['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['sucesso'] = true;
        $response['mensagem'] = 'Receita encontrada.';
        $response['dados'] = $receita;
        break;

    case 'criar':
        $csrfToken = (string)(obterValor('csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !doce_validar_csrf_token($csrfToken)) {
            http_response_code(403);
            $response['mensagem'] = 'Token de seguranca invalido.';
            break;
        }
        $nome = trim(obterValor('nome_receita') ?? '');
        $rendimento = intval(obterValor('rendimento_porcoes') ?? 0);
        $preco = floatval(obterValor('preco_venda_sugerido') ?? 0);

        if ($nome === '' || $rendimento <= 0 || $preco <= 0) {
            $response['mensagem'] = 'Preencha nome, rendimento e preço corretamente.';
            break;
        }

        $stmt = $pdo->prepare('INSERT INTO receitas (user_id, nome_receita, rendimento_porcoes, preco_venda_sugerido) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, $nome, $rendimento, $preco]);

        $response['sucesso'] = true;
        $response['mensagem'] = 'Receita cadastrada com sucesso.';
        $response['dados'] = ['id' => $pdo->lastInsertId()];
        break;

    case 'editar':
        $csrfToken = (string)(obterValor('csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !doce_validar_csrf_token($csrfToken)) {
            http_response_code(403);
            $response['mensagem'] = 'Token de seguranca invalido.';
            break;
        }
        $id = intval(obterValor('id') ?? 0);
        $nome = trim(obterValor('nome_receita') ?? '');
        $rendimento = intval(obterValor('rendimento_porcoes') ?? 0);
        $preco = floatval(obterValor('preco_venda_sugerido') ?? 0);

        $erros = [];
        if ($id <= 0) {
            $erros[] = 'ID da receita inválido.';
        }
        if ($nome === '') {
            $erros[] = 'Nome da receita é obrigatório.';
        }
        if ($rendimento <= 0) {
            $erros[] = 'Rendimento deve ser maior que zero.';
        }
        if ($preco <= 0) {
            $erros[] = 'Preço de venda deve ser maior que zero.';
        }
        if (count($erros) > 0) {
            $response['mensagem'] = implode(' ', $erros);
            break;
        }

        $stmt = $pdo->prepare('UPDATE receitas SET nome_receita = ?, rendimento_porcoes = ?, preco_venda_sugerido = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$nome, $rendimento, $preco, $id, $user_id]);

        $response['sucesso'] = true;
        $response['mensagem'] = 'Receita atualizada com sucesso.';
        break;

    default:
        $response['mensagem'] = 'Ação desconhecida. Use acao=listar, buscar, criar ou editar.';
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
