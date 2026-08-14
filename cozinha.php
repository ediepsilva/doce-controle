<?php
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$statuses = ['Pendente', 'Em Produção', 'Pronto'];

$stmt = $pdo->prepare(
    "SELECT p.*, c.nome AS cliente_nome, c.whatsapp, r.nome_receita
     FROM pedidos p
     JOIN clientes c ON c.id = p.cliente_id
     JOIN receitas r ON r.id = p.receita_id
     WHERE p.user_id = ? AND p.status <> 'Entregue'
     ORDER BY p.data_entrega ASC, FIELD(p.status, 'Pendente', 'Em Produção', 'Pronto')"
);
$stmt->execute([$user_id]);
$pedidos = $stmt->fetchAll();

$pedidoIds = array_column($pedidos, 'id');
$ingredientesPorPedido = [];

if ($pedidoIds) {
    $placeholders = implode(',', array_fill(0, count($pedidoIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT p.id AS pedido_id, e.item_nome, e.unidade_medida,
                SUM(ri.quantidade_usada * p.quantidade) AS quantidade_total
         FROM pedidos p
         JOIN receitas_itens ri ON ri.receita_id = p.receita_id
         JOIN estoque e ON e.id = ri.insumo_id
         WHERE p.id IN ($placeholders) AND p.user_id = ?
         GROUP BY p.id, e.id, e.item_nome, e.unidade_medida
         ORDER BY e.item_nome ASC"
    );
    $params = $pedidoIds;
    $params[] = $user_id;
    $stmt->execute($params);

    foreach ($stmt->fetchAll() as $ingrediente) {
        $ingredientesPorPedido[$ingrediente['pedido_id']][] = $ingrediente;
    }
}

$board = array_fill_keys($statuses, []);
foreach ($pedidos as $pedido) {
    if (strpos($pedido['status'], 'Produ') !== false) {
        $pedido['status'] = 'Em Produção';
    }
    if (!isset($board[$pedido['status']])) {
        $pedido['status'] = 'Pendente';
    }
    $board[$pedido['status']][] = $pedido;
}

function cozinha_proximo_status($status)
{
    if ($status === 'Pendente') {
        return 'Em Produção';
    }
    if ($status === 'Em Produção') {
        return 'Pronto';
    }
    if ($status === 'Pronto') {
        return 'Entregue';
    }
    return null;
}

function cozinha_badge_prazo($dataEntrega)
{
    $agora = new DateTimeImmutable('now');
    $entrega = new DateTimeImmutable($dataEntrega);

    if ($entrega < $agora) {
        return '<span class="badge bg-danger">Atrasado</span>';
    }

    if ($entrega->format('Y-m-d') === $agora->format('Y-m-d')) {
        return '<span class="badge bg-warning text-dark">Hoje</span>';
    }

    return '<span class="badge bg-light text-dark border">' . $entrega->format('d/m H:i') . '</span>';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff007f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Doce Controle">
    <link rel="manifest" href="manifest.json">
    <title>Doce Controle - Cozinha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
    <style>
        .kitchen-card {
            border-radius: 8px;
            border: 1px solid rgba(33, 37, 41, 0.12);
        }

        .ingredient-list {
            max-height: 190px;
            overflow: auto;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-fire"></i> Doce Controle - Cozinha</span>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="pedidos.php" class="btn btn-outline-light btn-sm">Pedidos</a>
            <a href="estoque.php" class="btn btn-outline-light btn-sm">Estoque</a>
        </div>
    </div>
</nav>

<main class="container">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <h1 class="h3 mb-0">Fila de Produção</h1>
                <p class="text-muted mb-0">Pedidos abertos com ingredientes calculados pela quantidade encomendada.</p>
            </div>
            <span class="badge bg-dark fs-6"><?= count($pedidos) ?> em aberto</span>
        </div>
    </div>

    <?php if (!$pedidos): ?>
        <div class="alert alert-success text-center">
            Nenhum pedido pendente agora. A cozinha está em dia.
        </div>
    <?php endif; ?>

    <div class="row gx-3">
        <?php foreach ($statuses as $status): ?>
            <section class="col-12 col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong><?= htmlspecialchars($status) ?></strong>
                        <span class="badge bg-dark"><?= count($board[$status]) ?></span>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <?php if (!$board[$status]): ?>
                            <div class="text-muted">Nenhum pedido nesta etapa.</div>
                        <?php endif; ?>

                        <?php foreach ($board[$status] as $pedido): ?>
                            <?php $nextStatus = cozinha_proximo_status($pedido['status']); ?>
                            <article class="kitchen-card bg-white p-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h2 class="h6 mb-1">#<?= intval($pedido['id']) ?> - <?= htmlspecialchars($pedido['nome_receita']) ?></h2>
                                        <div class="small text-muted"><?= htmlspecialchars($pedido['cliente_nome']) ?> · <?= htmlspecialchars($pedido['whatsapp']) ?></div>
                                    </div>
                                    <?= cozinha_badge_prazo($pedido['data_entrega']) ?>
                                </div>

                                <div class="small mb-2">
                                    <strong>Quantidade:</strong> <?= intval($pedido['quantidade']) ?><br>
                                    <strong>Entrega:</strong> <?= date('d/m/Y H:i', strtotime($pedido['data_entrega'])) ?>
                                </div>

                                <?php if (!empty($pedido['observacoes'])): ?>
                                    <div class="alert alert-light border small py-2 mb-2">
                                        <?= htmlspecialchars($pedido['observacoes']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="ingredient-list small border rounded p-2 mb-3">
                                    <strong class="d-block mb-2">Ingredientes</strong>
                                    <?php if (!empty($ingredientesPorPedido[$pedido['id']])): ?>
                                        <?php foreach ($ingredientesPorPedido[$pedido['id']] as $ingrediente): ?>
                                            <div class="d-flex justify-content-between gap-3 border-bottom py-1">
                                                <span><?= htmlspecialchars($ingrediente['item_nome']) ?></span>
                                                <strong><?= number_format($ingrediente['quantidade_total'], 3, ',', '.') ?> <?= htmlspecialchars($ingrediente['unidade_medida']) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Receita sem ingredientes cadastrados.</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($nextStatus): ?>
                                    <form action="atualizar_status_pedido.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= intval($pedido['id']) ?>">
                                        <input type="hidden" name="status" value="<?= htmlspecialchars($nextStatus) ?>">
                                        <button type="submit" class="btn btn-dark btn-sm w-100">Mover para <?= htmlspecialchars($nextStatus) ?></button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/pwa.js"></script>
</body>
</html>
