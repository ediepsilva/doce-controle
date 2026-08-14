<?php
require_once 'config.php';

function pedido_publico_redirecionar($userId, $resultado, $codigo = '')
{
    $parametros = [
        'user_id' => $userId,
        'pedido' => $resultado,
    ];

    if ($codigo !== '') {
        $parametros['codigo'] = $codigo;
    }

    header('Location: cardapio.php?' . http_build_query($parametros) . '#cardapio');
    exit;
}

function pedido_publico_gerar_codigo()
{
    return strtoupper(bin2hex(random_bytes(4)));
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

$nome = trim((string)($_POST['nome'] ?? ''));
$whatsapp = preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$enderecoEntrega = trim((string)($_POST['endereco_entrega'] ?? ''));
$nomeRecebedor = trim((string)($_POST['nome_recebedor'] ?? ''));
$dataEntregaInformada = trim((string)($_POST['data_entrega'] ?? ''));
$horarioEntregaInformado = trim((string)($_POST['horario_entrega'] ?? ''));
$observacoes = trim((string)($_POST['observacoes'] ?? ''));
$itensJson = (string)($_POST['itens_json'] ?? '');

if (
    mb_strlen($nome) < 2 ||
    mb_strlen($nome) > 160 ||
    strlen($whatsapp) < 10 ||
    strlen($whatsapp) > 15 ||
    ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) ||
    mb_strlen($enderecoEntrega) < 5 ||
    mb_strlen($enderecoEntrega) > 255 ||
    mb_strlen($nomeRecebedor) < 2 ||
    mb_strlen($nomeRecebedor) > 160 ||
    mb_strlen($observacoes) > 1000
) {
    pedido_publico_redirecionar($userId, 'dados');
}

if ($horarioEntregaInformado === '') {
    $horarioEntregaInformado = '12:00';
}

$dataEntrega = DateTime::createFromFormat('Y-m-d H:i', $dataEntregaInformada . ' ' . $horarioEntregaInformado);
$agora = new DateTime();
if (!$dataEntrega || $dataEntrega <= $agora) {
    pedido_publico_redirecionar($userId, 'data');
}

$itens = json_decode($itensJson, true);
if (!is_array($itens) || count($itens) === 0 || count($itens) > 30) {
    pedido_publico_redirecionar($userId, 'produto');
}

$itensValidados = [];
foreach ($itens as $item) {
    $receitaId = intval($item['receita_id'] ?? 0);
    $quantidade = intval($item['quantidade'] ?? 0);

    if ($receitaId <= 0 || $quantidade < 1 || $quantidade > 100) {
        pedido_publico_redirecionar($userId, 'produto');
    }

    $itensValidados[] = ['receita_id' => $receitaId, 'quantidade' => $quantidade];
}

$receitaIds = array_column($itensValidados, 'receita_id');
$placeholders = implode(',', array_fill(0, count($receitaIds), '?'));

$stmt = $pdo->prepare(
    "SELECT r.id, r.preco_venda_sugerido,
            IFNULL(SUM(ri.quantidade_usada * e.preco_unitario), 0) AS custo_total
     FROM receitas r
     INNER JOIN users u ON u.id = r.user_id
     LEFT JOIN receitas_itens ri ON ri.receita_id = r.id
     LEFT JOIN estoque e ON e.id = ri.insumo_id
     WHERE r.id IN ($placeholders) AND r.user_id = ?
       AND r.mostrar_cardapio = 1
       AND u.status = 'ativo' AND u.plano = 'ativo'
     GROUP BY r.id"
);
$stmt->execute(array_merge($receitaIds, [$userId]));
$receitasEncontradas = [];
foreach ($stmt->fetchAll() as $receita) {
    $receitasEncontradas[intval($receita['id'])] = $receita;
}

if (count($receitasEncontradas) !== count(array_unique($receitaIds))) {
    pedido_publico_redirecionar($userId, 'produto');
}

$itensParaSalvar = [];
foreach ($itensValidados as $item) {
    $receita = $receitasEncontradas[$item['receita_id']];
    $precoUnitario = floatval($receita['preco_venda_sugerido']);
    if ($precoUnitario <= 0) {
        $precoUnitario = floatval($receita['custo_total']) * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO;
    }
    $valorItem = round($precoUnitario * $item['quantidade'], 2);

    $itensParaSalvar[] = [
        'receita_id' => $item['receita_id'],
        'quantidade' => $item['quantidade'],
        'valor_total' => $valorItem,
    ];
}

$temColunasEntrega = doce_coluna_existe($pdo, 'pedidos', 'endereco_entrega')
    && doce_coluna_existe($pdo, 'pedidos', 'nome_recebedor')
    && doce_coluna_existe($pdo, 'pedidos', 'codigo_pedido');
$temColunaOrigem = doce_coluna_existe($pdo, 'pedidos', 'origem');

$codigoPedido = pedido_publico_gerar_codigo();

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

    $colunas = ['user_id', 'cliente_id', 'receita_id', 'quantidade', 'data_entrega', 'status', 'valor_total', 'observacoes'];

    if ($temColunasEntrega) {
        $colunas[] = 'endereco_entrega';
        $colunas[] = 'nome_recebedor';
        $colunas[] = 'codigo_pedido';
    }
    if ($temColunaOrigem) {
        $colunas[] = 'origem';
    }
    $colunas[] = 'criado_em';

    $marcadores = array_fill(0, count($colunas) - 1, '?');
    $marcadores[] = 'NOW()';
    $sql = "INSERT INTO pedidos (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $marcadores) . ")";
    $stmtInsere = $pdo->prepare($sql);

    foreach ($itensParaSalvar as $item) {
        $valores = [
            $userId,
            $clienteId,
            $item['receita_id'],
            $item['quantidade'],
            $dataEntrega->format('Y-m-d H:i:s'),
            'Pendente',
            $item['valor_total'],
            $observacoes !== '' ? $observacoes : null,
        ];

        if ($temColunasEntrega) {
            $valores[] = $enderecoEntrega;
            $valores[] = $nomeRecebedor;
            $valores[] = $codigoPedido;
        }
        if ($temColunaOrigem) {
            $valores[] = 'publico';
        }

        $stmtInsere->execute($valores);
    }

    $pdo->commit();
    $_SESSION['ultimo_pedido_publico'] = time();
    pedido_publico_redirecionar($userId, 'sucesso', $codigoPedido);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    pedido_publico_redirecionar($userId, 'erro');
}
