<?php
require_once 'config.php';

if (doce_usuario_logado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } elseif (!doce_tabela_existe($pdo, 'users') || !doce_coluna_existe($pdo, 'users', 'password_hash')) {
        $erro = 'Tabela de usuarios nao encontrada. Execute o arquivo schema.sql no banco.';
    } else {
        $stmt = $pdo->prepare("SELECT id, nome, email, password_hash, status, plano FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && doce_verificar_senha($senha, $usuario['password_hash'])) {
            $_SESSION['user_id'] = intval($usuario['id']);
            $_SESSION['user_nome'] = $usuario['nome'];
            header('Location: index.php');
            exit;
        }

        $erro = 'E-mail ou senha invalidos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff007f">
    <title>Doce Controle - Entrar</title>
    <link rel="manifest" href="manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="card shadow-sm border-danger w-100" style="max-width: 460px;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shop-window display-5 pink-shock"></i>
                    <h1 class="h3 fw-bold mt-3 mb-1">Doce Controle</h1>
                    <p class="text-muted mb-0">Entre para gerenciar sua confeitaria.</p>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST" class="d-grid gap-3">
                    <div>
                        <label class="form-label fw-bold">E-mail</label>
                        <input type="email" name="email" class="form-control form-control-lg" value="<?= htmlspecialchars($email) ?>" required autofocus>
                    </div>
                    <div>
                        <label class="form-label fw-bold">Senha</label>
                        <input type="password" name="senha" class="form-control form-control-lg" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </form>

                <div class="text-center mt-4">
                    <span class="text-muted">Ainda nao tem conta?</span>
                    <a href="cadastro.php" class="fw-bold pink-shock">Criar cadastro</a>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/pwa.js"></script>
</body>
</html>
