<?php
require_once 'config.php';

$usuarioAtual = doce_usuario_atual($pdo);
if (!$usuarioAtual) {
    header('Location: login.php');
    exit;
}

$logoAtual = trim((string)($usuarioAtual['logo_marca'] ?? ''));
$logoPreview = $logoAtual !== '' && is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logoAtual))
    ? $logoAtual
    : 'assets/delicias-da-mara-logo.jpg';
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
    <title>Doce Controle - Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
    <style>
        .brand-preview {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #ff007f;
            background: #fff;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-shop-window"></i> Perfil da Confeitaria</span>
        <a href="index.php" class="btn btn-outline-light btn-sm">Dashboard</a>
    </div>
</nav>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mb-4">
                        <img src="<?= htmlspecialchars($logoPreview) ?>" alt="Logo atual" class="brand-preview">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Dados que aparecem no cardápio</h1>
                            <p class="text-muted mb-0">Atualize a identidade usada no link público dos seus produtos.</p>
                        </div>
                    </div>

                    <?php if (isset($_GET['sucesso'])): ?>
                        <div class="alert alert-success">Perfil atualizado com sucesso.</div>
                    <?php endif; ?>

                    <form action="salvar_perfil.php" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                        <div>
                            <label class="form-label fw-bold">Nome da confeitaria</label>
                            <input type="text" name="nome" class="form-control form-control-lg" value="<?= htmlspecialchars($usuarioAtual['nome'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label class="form-label fw-bold">WhatsApp para pedidos</label>
                            <input type="tel" name="whatsapp" class="form-control form-control-lg" value="<?= htmlspecialchars($usuarioAtual['whatsapp'] ?? '') ?>" placeholder="(00) 00000-0000">
                        </div>
                        <div>
                            <label class="form-label fw-bold">Logo ou foto da marca</label>
                            <input type="file" name="logo_marca" class="form-control form-control-lg" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Use JPG, PNG ou WebP com até 3 MB.</div>
                        </div>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Salvar perfil
                            </button>
                            <a href="cardapio.php?user_id=<?= urlencode((string)$usuarioAtual['id']) ?>" target="_blank" class="btn btn-outline-success btn-lg">
                                <i class="bi bi-box-arrow-up-right"></i> Ver cardápio
                            </a>
                        </div>
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
