<?php
require_once 'config.php';
$user_id = $_SESSION['user_id'];
$filterCliente = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

$statuses = ['Pendente', 'Em Produção', 'Pronto', 'Entregue'];

$where = 'p.user_id = ?';
$params = [$user_id];

if ($filterCliente) {
    $where .= ' AND p.cliente_id = ?';
    $params[] = $filterCliente;
}

$stmt = $pdo->prepare(
    "SELECT p.*, c.nome AS cliente_nome, r.nome_receita
     FROM pedidos p
     JOIN clientes c ON c.id = p.cliente_id
     JOIN receitas r ON r.id = p.receita_id
     WHERE {$where}
     ORDER BY FIELD(p.status, 'Pendente', 'Em Produção', 'Pronto', 'Entregue'), p.data_entrega ASC"
);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

$clienteFiltrado = null;
if ($filterCliente) {
    $stmt = $pdo->prepare('SELECT nome FROM clientes WHERE id = ? AND user_id = ?');
    $stmt->execute([$filterCliente, $user_id]);
    $clienteFiltrado = $stmt->fetchColumn();
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

$agora = new DateTimeImmutable('now');
$hoje = $agora->format('Y-m-d');
$pedidosAbertos = 0;
$entregasHoje = 0;
$pedidosAtrasados = 0;
$faturamentoAberto = 0;
$proximaEntrega = null;

foreach ($pedidos as $pedido) {
    if ($pedido['status'] === 'Entregue') {
        continue;
    }

    $pedidosAbertos++;
    $faturamentoAberto += floatval($pedido['valor_total']);

    $dataEntrega = new DateTimeImmutable($pedido['data_entrega']);
    if ($dataEntrega->format('Y-m-d') === $hoje) {
        $entregasHoje++;
    }

    if ($dataEntrega < $agora) {
        $pedidosAtrasados++;
    }

    if ($dataEntrega >= $agora && (!$proximaEntrega || $dataEntrega < $proximaEntrega['data'])) {
        $proximaEntrega = [
            'data' => $dataEntrega,
            'pedido' => $pedido,
        ];
    }
}

function pedido_badge_prazo($pedido)
{
    if ($pedido['status'] === 'Entregue') {
        return '<span class="badge bg-success">Entregue</span>';
    }

    $agora = new DateTimeImmutable('now');
    $dataEntrega = new DateTimeImmutable($pedido['data_entrega']);
    $dias = intval($agora->diff($dataEntrega)->format('%r%a'));

    if ($dataEntrega < $agora) {
        return '<span class="badge bg-danger">Atrasado</span>';
    }

    if ($dataEntrega->format('Y-m-d') === $agora->format('Y-m-d')) {
        return '<span class="badge bg-warning text-dark">Entrega hoje</span>';
    }

    if ($dias === 1) {
        return '<span class="badge bg-info text-dark">Amanha</span>';
    }

    return '<span class="badge bg-light text-dark border">Em ' . $dias . ' dias</span>';
}

$stmt = $pdo->prepare("SELECT id, nome FROM clientes WHERE user_id = ? ORDER BY nome ASC");
$stmt->execute([$user_id]);
$clientes = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT id, nome_receita FROM receitas WHERE user_id = ? ORDER BY nome_receita ASC");
$stmt->execute([$user_id]);
$receitas = $stmt->fetchAll();
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
    <title>Doce Controle - Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
    <style>
        .summary-card {
            border: 1px solid rgba(220, 53, 69, 0.18);
            border-radius: 8px;
            background: #fff;
        }

        .summary-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-kanban-fill"></i> Doce Controle - Pedidos</span>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="cozinha.php" class="btn btn-outline-light btn-sm">Cozinha</a>
            <a href="receitas.php" class="btn btn-outline-light btn-sm">Receitas</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <h1 class="h3 mb-0">Painel de Pedidos</h1>
                <p class="text-muted mb-0">Acompanhe prazos, valores em aberto e avance cada encomenda pelo fluxo.</p>
                <?php if ($clienteFiltrado): ?>
                    <div class="mt-2">
                        <span class="badge bg-secondary">Filtrando histÃ³rico de: <?= htmlspecialchars($clienteFiltrado) ?></span>
                        <a href="pedidos.php" class="btn btn-sm btn-outline-secondary ms-2">Limpar filtro</a>
                    </div>
                <?php endif; ?>
            </div>
            <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#modalPedido">
                <i class="bi bi-plus-circle"></i> Nova Encomenda
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="summary-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="summary-icon"><i class="bi bi-hourglass-split"></i></span>
                    <div>
                        <div class="text-muted small">Pedidos abertos</div>
                        <div class="h4 mb-0"><?= $pedidosAbertos ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="summary-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="summary-icon"><i class="bi bi-calendar-check"></i></span>
                    <div>
                        <div class="text-muted small">Entregas hoje</div>
                        <div class="h4 mb-0"><?= $entregasHoje ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="summary-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="summary-icon"><i class="bi bi-exclamation-triangle"></i></span>
                    <div>
                        <div class="text-muted small">Atrasados</div>
                        <div class="h4 mb-0"><?= $pedidosAtrasados ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="summary-card shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="summary-icon"><i class="bi bi-cash-coin"></i></span>
                    <div>
                        <div class="text-muted small">Valor em aberto</div>
                        <div class="h4 mb-0">R$ <?= number_format($faturamentoAberto, 2, ',', '.') ?></div>
                    </div>
                </div>
                <?php if ($proximaEntrega): ?>
                    <div class="small text-muted mt-2">
                        Proxima: #<?= $proximaEntrega['pedido']['id'] ?> em <?= $proximaEntrega['data']->format('d/m H:i') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row gx-3">
        <?php foreach ($statuses as $status): ?>
            <div class="col-12 col-md-6 col-xl-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <strong><?= $status ?></strong>
                        <span class="badge bg-light text-dark ms-2"><?= count($board[$status]) ?></span>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <?php if (empty($board[$status])): ?>
                            <div class="text-muted">Nenhum pedido nesta fase.</div>
                        <?php endif; ?>
                        <?php foreach ($board[$status] as $pedido): ?>
                            <?php
                                $nextStatus = null;
                                if ($pedido['status'] === 'Pendente') $nextStatus = 'Em Produção';
                                if ($pedido['status'] === 'Em Produção') $nextStatus = 'Pronto';
                                if ($pedido['status'] === 'Pronto') $nextStatus = 'Entregue';
                            ?>
                            <div class="card border-secondary shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <h6 class="card-title mb-0">#<?= $pedido['id'] ?> - <?= htmlspecialchars($pedido['cliente_nome']) ?></h6>
                                        <?= pedido_badge_prazo($pedido) ?>
                                    </div>
                                    <p class="mb-1"><strong>Receita:</strong> <?= htmlspecialchars($pedido['nome_receita']) ?></p>
                                    <p class="mb-1"><strong>Quantidade:</strong> <?= $pedido['quantidade'] ?></p>
                                    <p class="mb-1"><strong>Entrega:</strong> <?= date('d/m/Y H:i', strtotime($pedido['data_entrega'])) ?></p>
                                    <p class="mb-1"><strong>Total:</strong> R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></p>
                                    <?php if (!empty($pedido['observacoes'])): ?>
                                        <p class="text-muted small">Observações: <?= htmlspecialchars($pedido['observacoes']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-grid gap-2">
                                        <?php if ($nextStatus): ?>
                                            <form action="atualizar_status_pedido.php" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= intval($pedido['id']) ?>">
                                                <input type="hidden" name="status" value="<?= htmlspecialchars($nextStatus) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Mover para <?= htmlspecialchars($nextStatus) ?></button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-success w-100">Entregue</span>
                                        <?php endif; ?>
                                        <div class="btn-group" role="group">
                                            <a href="editar_pedido.php?id=<?= intval($pedido['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <form action="excluir_pedido.php" method="POST" class="d-inline" onsubmit="return confirm('Excluir este pedido?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= intval($pedido['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" <?= intval($pedido['estoque_baixado'] ?? 0) === 1 ? 'disabled' : '' ?>>
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="salvar_pedido.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-cart-plus"></i> Nova Encomenda</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cliente</label>
                        <select name="cliente_id" class="form-select" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Receita</label>
                        <select name="receita_id" class="form-select" required>
                            <option value="">Selecione a receita</option>
                            <?php foreach ($receitas as $receita): ?>
                                <option value="<?= $receita['id'] ?>"><?= htmlspecialchars($receita['nome_receita']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Quantidade</label>
                            <input type="number" name="quantidade" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Data de entrega</label>
                            <input type="datetime-local" name="data_entrega" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4">Salvar Pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/pwa.js"></script>
</body>
</html>
