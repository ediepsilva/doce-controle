<?php
require_once 'config.php';

if (doce_usuario_logado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string)($_POST['nome'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    $confirmarSenha = (string)($_POST['confirmar_senha'] ?? '');

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha nome, e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail valido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas nao conferem.';
    } elseif (!doce_tabela_existe($pdo, 'users') || !doce_coluna_existe($pdo, 'users', 'password_hash')) {
        $erro = 'Tabela de usuarios nao encontrada. Execute o arquivo schema.sql no banco.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = 'Ja existe uma conta com este e-mail.';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO users (nome, email, password_hash, status, plano, criado_em)
                 VALUES (?, ?, ?, 'ativo', 'ativo', NOW())"
            );
            $stmt->execute([$nome, $email, doce_hash_senha($senha)]);

            $_SESSION['user_id'] = intval($pdo->lastInsertId());
            $_SESSION['user_nome'] = $nome;
            header('Location: index.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff007f">
    <title>Doce Controle - Cadastro</title>
    <link rel="manifest" href="manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="card shadow-sm border-danger w-100" style="max-width: 520px;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus display-5 pink-shock"></i>
                    <h1 class="h3 fw-bold mt-3 mb-1">Criar Conta</h1>
                    <p class="text-muted mb-0">Cadastre seu acesso ao Doce Controle.</p>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST" class="d-grid gap-3">
                    <div>
                        <label class="form-label fw-bold">Nome</label>
                        <input type="text" name="nome" class="form-control form-control-lg" value="<?= htmlspecialchars($nome) ?>" required autofocus>
                    </div>
                    <div>
                        <label class="form-label fw-bold">E-mail</label>
                        <input type="email" name="email" class="form-control form-control-lg" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Senha</label>
                            <input type="password" name="senha" class="form-control form-control-lg" minlength="6" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Confirmar senha</label>
                            <input type="password" name="confirmar_senha" class="form-control form-control-lg" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-check-circle"></i> Criar conta
                    </button>
                </form>

                <div class="text-center mt-4">
                    <span class="text-muted">Ja tem conta?</span>
                    <a href="login.php" class="fw-bold pink-shock">Entrar</a>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/pwa.js"></script>
</body>
</html>
