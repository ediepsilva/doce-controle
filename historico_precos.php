<?php
require_once 'config.php';

if (!isset($_GET['estoque_id'])) {
    header('Location: estoque.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$estoque_id = intval($_GET['estoque_id']);

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE id = ? AND user_id = ?");
$stmt->execute([$estoque_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: estoque.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM historico_precos WHERE estoque_id = ? AND user_id = ? ORDER BY data_compra DESC, criado_em DESC");
$stmt->execute([$estoque_id, $user_id]);
$historico = $stmt->fetchAll();
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
    <title>Histórico de Preços - <?= htmlspecialchars($item['item_nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="bi bi-clock-history"></i> Histórico de Preços</span>
        <div class="d-flex gap-2">
            <a href="estoque.php" class="btn btn-outline-light btn-sm">Voltar ao Estoque</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3"><?= htmlspecialchars($item['item_nome']) ?></h4>
                    <p class="mb-1"><strong>Unidade:</strong> <?= htmlspecialchars($item['unidade_medida']) ?></p>
                    <p class="mb-1"><strong>Estoque atual:</strong> <?= number_format($item['quantidade_atual'], 2, ',', '.') ?> <?= htmlspecialchars($item['unidade_medida']) ?></p>
                    <p class="mb-1"><strong>Último preço:</strong> R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></p>
                    <p class="mb-1"><strong>Estoque mínimo:</strong> <?= number_format($item['estoque_minimo'], 2, ',', '.') ?> <?= htmlspecialchars($item['unidade_medida']) ?></p>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">Registrar nova compra</h5>
                    <form action="salvar_compra_insumo.php" method="POST">
                        <input type="hidden" name="estoque_id" value="<?= $item['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Valor pago na compra</label>
                            <input type="number" step="0.01" name="preco_compra" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantidade comprada</label>
                            <input type="number" step="0.001" name="quantidade_comprada" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data da compra</label>
                            <input type="datetime-local" name="data_compra" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observações</label>
                            <textarea name="nota" class="form-control" rows="2"></textarea>
                        </div>
                        <button class="btn btn-success w-100">Salvar compra</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Histórico de preços</h5>
                    <?php if (empty($historico)): ?>
                        <div class="alert alert-secondary">Ainda não há registros de compra para este insumo.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Data</th>
                                        <th>Preço</th>
                                        <th>Quant.</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historico as $linha): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($linha['data_compra'])) ?></td>
                                            <td>R$ <?= number_format($linha['preco_compra'], 2, ',', '.') ?></td>
                                            <td><?= number_format($linha['quantidade_comprada'] ?? 0, 3, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($linha['nota']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/pwa.js"></script>
</body>
</html>
