<?php
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$usuarioAtual = doce_usuario_atual($pdo);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM estoque WHERE user_id = ?");
$stmt->execute([$user_id]);
$estoqueCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE user_id = ?");
$stmt->execute([$user_id]);
$clientesCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM receitas WHERE user_id = ?");
$stmt->execute([$user_id]);
$receitasCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(status <> 'Entregue') FROM pedidos WHERE user_id = ?");
$stmt->execute([$user_id]);
$pedidosAbertos = $stmt->fetchColumn();
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
    <title>Doce Controle - Painel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-shop-window"></i> Doce Controle</span>
        <div class="d-flex align-items-center gap-2">
            <?php if ($usuarioAtual): ?>
                <span class="text-white small d-none d-md-inline">Olá, <?= htmlspecialchars($usuarioAtual['nome']) ?></span>
            <?php endif; ?>
            <a href="perfil.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-person-gear"></i> Perfil
            </a>
            <a href="cardapio.php?user_id=<?= urlencode((string)$user_id) ?>" class="btn btn-outline-light btn-sm" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Ver Cardápio
            </a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="fw-bold">Painel de Controle</h1>
            <p class="text-muted">Acesso rápido às áreas de estoque, clientes, receitas e encomendas.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title">Estoque</h5>
                            <p class="card-text text-muted mb-0">Itens cadastrados</p>
                        </div>
                        <span class="badge bg-success fs-6"><?= $estoqueCount ?></span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="estoque.php" class="btn btn-success w-100">Ver Estoque</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title">Clientes</h5>
                            <p class="card-text text-muted mb-0">Clientes cadastrados</p>
                        </div>
                        <span class="badge bg-primary fs-6"><?= $clientesCount ?></span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="clientes.php" class="btn btn-primary w-100">Ver Clientes</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title">Receitas</h5>
                            <p class="card-text text-muted mb-0">Fichas técnicas</p>
                        </div>
                        <span class="badge bg-warning text-dark fs-6"><?= $receitasCount ?></span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="receitas.php" class="btn btn-warning w-100">Ver Receitas</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title">Encomendas</h5>
                            <p class="card-text text-muted mb-0">Pedidos não entregues</p>
                        </div>
                        <span class="badge bg-danger fs-6"><?= $pedidosAbertos ?></span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="pedidos.php" class="btn btn-danger w-100">Ver Pedidos</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title">Cozinha</h5>
                            <p class="card-text text-muted mb-0">Fila de produção</p>
                        </div>
                        <span class="badge bg-dark fs-6"><i class="bi bi-fire"></i></span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="cozinha.php" class="btn btn-dark w-100">Abrir Cozinha</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <strong>Bem-vinda!</strong> Use este painel para navegar rapidamente entre estoque, clientes, receitas e pedidos.
                O cardápio público já pode ser divulgado para seus clientes:
                <a href="cardapio.php?user_id=<?= urlencode((string)$user_id) ?>" target="_blank" class="alert-link">abrir link público</a>.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/pwa.js"></script>
</body>
</html>
