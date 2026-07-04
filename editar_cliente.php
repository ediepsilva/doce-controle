<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: clientes.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cliente_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$cliente_id, $user_id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: clientes.php');
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
    <title>Editar Cliente - <?= htmlspecialchars($cliente['nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-person-lines-fill"></i> Editar Cliente</span>
        <a href="clientes.php" class="btn btn-outline-light btn-sm">Voltar</a>
    </div>
</nav>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-3">Dados do cliente</h1>
                    <form action="salvar_cliente.php" method="POST" class="d-grid gap-3">
                        <input type="hidden" name="id" value="<?= intval($cliente['id']) ?>">
                        <div>
                            <label class="form-label fw-bold">Nome</label>
                            <input type="text" name="nome" class="form-control form-control-lg" value="<?= htmlspecialchars($cliente['nome']) ?>" required autofocus>
                        </div>
                        <div>
                            <label class="form-label fw-bold">WhatsApp</label>
                            <input type="tel" name="whatsapp" class="form-control form-control-lg" value="<?= htmlspecialchars($cliente['whatsapp']) ?>" required>
                        </div>
                        <div>
                            <label class="form-label fw-bold">E-mail</label>
                            <input type="email" name="email" class="form-control form-control-lg" value="<?= htmlspecialchars($cliente['email']) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Salvar alteracoes
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
