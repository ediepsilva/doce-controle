<?php
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$pedido_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT *
     FROM pedidos
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);
$stmt->execute([$pedido_id, $user_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, nome FROM clientes WHERE user_id = ? ORDER BY nome ASC");
$stmt->execute([$user_id]);
$clientes = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT id, nome_receita FROM receitas WHERE user_id = ? ORDER BY nome_receita ASC");
$stmt->execute([$user_id]);
$receitas = $stmt->fetchAll();

$podeEditar = intval($pedido['estoque_baixado'] ?? 0) === 0;
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
    <title>Editar Pedido #<?= intval($pedido['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-pencil-square"></i> Editar Pedido #<?= intval($pedido['id']) ?></span>
        <a href="pedidos.php" class="btn btn-outline-light btn-sm">Voltar</a>
    </div>
</nav>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if (!$podeEditar): ?>
                        <div class="alert alert-warning">
                            Este pedido já baixou estoque. Para evitar divergência, edite apenas pedidos ainda pendentes.
                        </div>
                    <?php endif; ?>

                    <form action="salvar_pedido.php" method="POST" class="d-grid gap-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= intval($pedido['id']) ?>">
                        <div>
                            <label class="form-label fw-bold">Cliente</label>
                            <select name="cliente_id" class="form-select form-select-lg" required <?= $podeEditar ? '' : 'disabled' ?>>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= intval($cliente['id']) ?>" <?= intval($cliente['id']) === intval($pedido['cliente_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Receita</label>
                            <select name="receita_id" class="form-select form-select-lg" required <?= $podeEditar ? '' : 'disabled' ?>>
                                <?php foreach ($receitas as $receita): ?>
                                    <option value="<?= intval($receita['id']) ?>" <?= intval($receita['id']) === intval($pedido['receita_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($receita['nome_receita']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Quantidade</label>
                                <input type="number" name="quantidade" class="form-control form-control-lg" min="1" value="<?= intval($pedido['quantidade']) ?>" required <?= $podeEditar ? '' : 'disabled' ?>>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Data de entrega</label>
                                <input type="datetime-local" name="data_entrega" class="form-control form-control-lg" value="<?= date('Y-m-d\TH:i', strtotime($pedido['data_entrega'])) ?>" required <?= $podeEditar ? '' : 'disabled' ?>>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3" <?= $podeEditar ? '' : 'disabled' ?>><?= htmlspecialchars($pedido['observacoes'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-lg" <?= $podeEditar ? '' : 'disabled' ?>>
                            <i class="bi bi-check-circle"></i> Salvar pedido
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/pwa.js"></script>
</body>
</html>
