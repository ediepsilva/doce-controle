<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: estoque.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: estoque.php');
    exit;
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
    <title>Editar Insumo - Doce Controle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="bi bi-pencil-square"></i> Editar Insumo</span>
        <a href="estoque.php" class="btn btn-outline-light btn-sm">Voltar ao Estoque</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Atualizar insumo</h4>
                    <form action="salvar_insumo.php" method="POST">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome do Insumo</label>
                            <input type="text" name="item_nome" class="form-control" value="<?= htmlspecialchars($item['item_nome']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Unidade</label>
                                <select name="unidade_medida" class="form-select" required>
                                    <option value="g" <?= $item['unidade_medida'] === 'g' ? 'selected' : '' ?>>Gramas (g)</option>
                                    <option value="kg" <?= $item['unidade_medida'] === 'kg' ? 'selected' : '' ?>>Quilos (kg)</option>
                                    <option value="un" <?= $item['unidade_medida'] === 'un' ? 'selected' : '' ?>>Unidade (un)</option>
                                    <option value="ml" <?= $item['unidade_medida'] === 'ml' ? 'selected' : '' ?>>Mililitros (ml)</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Preço Unitário (R$)</label>
                                <input type="number" step="0.01" name="preco_unitario" class="form-control" value="<?= htmlspecialchars($item['preco_unitario']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Qtd. Atual</label>
                                <input type="number" step="0.001" name="quantidade_atual" class="form-control" value="<?= htmlspecialchars($item['quantidade_atual']) ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Estoque Mínimo</label>
                                <input type="number" step="0.001" name="estoque_minimo" class="form-control" value="<?= htmlspecialchars($item['estoque_minimo']) ?>" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Salvar Alterações</button>
                            <a href="historico_precos.php?estoque_id=<?= $item['id'] ?>" class="btn btn-outline-dark btn-lg">Ver Histórico de Preços</a>
                            <a href="estoque.php" class="btn btn-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/pwa.js"></script>
</body>
</html>
