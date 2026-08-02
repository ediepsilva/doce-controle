<?php
require_once 'config.php';

function pedido_publico_redirecionar($userId, $resultado)
{
    $parametros = http_build_query([
        'user_id' => $userId,
        'pedido' => $resultado,
    ]);
    header('Location: cardapio.php?' . $parametros . '#cardapio');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
if ($userId <= 0 || !doce_validar_csrf()) {
    pedido_publico_redirecionar($userId, 'erro');
}

// Campo invisivel: robos costumam preenche-lo, clientes reais nao.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    pedido_publico_redirecionar($userId, 'sucesso');
}

$ultimoEnvio = intval($_SESSION['ultimo_pedido_publico'] ?? 0);
if ($ultimoEnvio > 0 && time() - $ultimoEnvio < 5) {
    pedido_publico_redirecionar($userId, 'aguarde');
}

$receitaId = intval($_POST['receita_id'] ?? 0);
$quantidade = intval($_POST['quantidade'] ?? 0);
$nome = trim((string)($_POST['nome'] ?? ''));
$whatsapp = preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$dataEntregaInformada = trim((string)($_POST['data_entrega'] ?? ''));
$observacoes = trim((string)($_POST['observacoes'] ?? ''));

if (
    $receitaId <= 0 ||
    $quantidade < 1 ||
    $quantidade > 100 ||
    mb_strlen($nome) < 2 ||
    mb_strlen($nome) > 160 ||
    strlen($whatsapp) < 10 ||
    strlen($whatsapp) > 15 ||
    ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) ||
    mb_strlen($observacoes) > 1000
) {
    pedido_publico_redirecionar($userId, 'dados');
}

$dataEntrega = DateTime::createFromFormat('Y-m-d\TH:i', $dataEntregaInformada);
$agora = new DateTime();
if (!$dataEntrega || $dataEntrega <= $agora) {
    pedido_publico_redirecionar($userId, 'data');
}

$stmt = $pdo->prepare(
    "SELECT r.id, r.preco_venda_sugerido,
            IFNULL(SUM(ri.quantidade_usada * e.preco_unitario), 0) AS custo_total
     FROM receitas r
     INNER JOIN users u ON u.id = r.user_id
     LEFT JOIN receitas_itens ri ON ri.receita_id = r.id
     LEFT JOIN estoque e ON e.id = ri.insumo_id
     WHERE r.id = ? AND r.user_id = ?
       AND r.mostrar_cardapio = 1
       AND u.status = 'ativo' AND u.plano = 'ativo'
     GROUP BY r.id"
);
$stmt->execute([$receitaId, $userId]);
$receita = $stmt->fetch();

if (!$receita) {
    pedido_publico_redirecionar($userId, 'produto');
}

$precoUnitario = floatval($receita['preco_venda_sugerido']);
if ($precoUnitario <= 0) {
    $precoUnitario = floatval($receita['custo_total']) * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO;
}
$valorTotal = round($precoUnitario * $quantidade, 2);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT id FROM clientes
         WHERE user_id = ? AND REPLACE(REPLACE(REPLACE(REPLACE(whatsapp, '(', ''), ')', ''), '-', ''), ' ', '') = ?
         LIMIT 1"
    );
    $stmt->execute([$userId, $whatsapp]);
    $clienteId = intval($stmt->fetchColumn());

    if ($clienteId > 0) {
        $stmt = $pdo->prepare(
            "UPDATE clientes SET nome = ?, email = ? WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$nome, $email !== '' ? $email : null, $clienteId, $userId]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO clientes (user_id, nome, whatsapp, email, criado_em)
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$userId, $nome, $whatsapp, $email !== '' ? $email : null]);
        $clienteId = intval($pdo->lastInsertId());
    }

    $stmt = $pdo->prepare(
        "INSERT INTO pedidos
            (user_id, cliente_id, receita_id, quantidade, data_entrega, status, valor_total, observacoes, criado_em)
         VALUES (?, ?, ?, ?, ?, 'Pendente', ?, ?, NOW())"
    );
    $stmt->execute([
        $userId,
        $clienteId,
        $receitaId,
        $quantidade,
        $dataEntrega->format('Y-m-d H:i:s'),
        $valorTotal,
        $observacoes !== '' ? $observacoes : null,
    ]);

    $pdo->commit();
    $_SESSION['ultimo_pedido_publico'] = time();
    pedido_publico_redirecionar($userId, 'sucesso');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    pedido_publico_redirecionar($userId, 'erro');
}
