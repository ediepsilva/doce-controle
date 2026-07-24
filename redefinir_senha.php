<?php
require_once 'config.php';

if (doce_usuario_logado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';
$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_password_reset_user (user_id),
            INDEX idx_password_reset_expires (expires_at),
            CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (Exception $e) {
    $erro = 'A recuperacao de senha esta temporariamente indisponivel.';
}

function doce_url_redefinicao($token)
{
    $base = rtrim((string)doce_env('APP_URL', ''), '/');

    if ($base === '') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        if (!preg_match('/^[a-z0-9.-]+(?::\d+)?$/i', $host)) {
            return '';
        }
        $diretorio = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')));
        $base = ($https ? 'https://' : 'http://') . $host . ($diretorio === '/' ? '' : $diretorio);
    }

    return $base . '/redefinir_senha.php?token=' . rawurlencode($token);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $erro === '') {
    if (!doce_validar_csrf()) {
        $erro = 'A sessao expirou. Atualize a pagina e tente novamente.';
    } elseif ($token === '') {
        $email = trim((string)($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail valido.';
        } else {
            $stmt = $pdo->prepare("SELECT id, nome, email FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                $tokenAberto = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $tokenAberto);
                $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL")
                    ->execute([intval($usuario['id'])]);
                $pdo->prepare(
                    "INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))"
                )->execute([intval($usuario['id']), $tokenHash]);

                $link = doce_url_redefinicao($tokenAberto);
                if ($link !== '') {
                    $assunto = 'Redefinicao de senha - Doce Controle';
                    $mensagem = "Ola, {$usuario['nome']}.\n\nUse este link para criar uma nova senha:\n{$link}\n\nO link expira em 30 minutos.";
                    $headers = "Content-Type: text/plain; charset=UTF-8\r\n";
                    @mail($usuario['email'], $assunto, $mensagem, $headers);
                }
            }

            $sucesso = 'Se o e-mail estiver cadastrado, enviaremos um link valido por 30 minutos.';
        }
    } else {
        $senha = (string)($_POST['senha'] ?? '');
        $confirmarSenha = (string)($_POST['confirmar_senha'] ?? '');

        if (strlen($senha) < 8) {
            $erro = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif ($senha !== $confirmarSenha) {
            $erro = 'As senhas nao conferem.';
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare(
                    "SELECT id, user_id
                     FROM password_reset_tokens
                     WHERE token_hash = ? AND used_at IS NULL AND expires_at >= NOW()
                     LIMIT 1 FOR UPDATE"
                );
                $stmt->execute([hash('sha256', $token)]);
                $registro = $stmt->fetch();

                if (!$registro) {
                    throw new RuntimeException('Token invalido');
                }

                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                    ->execute([doce_hash_senha($senha), intval($registro['user_id'])]);
                $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?")
                    ->execute([intval($registro['id'])]);
                $pdo->commit();
                $sucesso = 'Senha redefinida com sucesso. Agora voce pode entrar.';
                $token = '';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erro = 'Este link e invalido ou expirou. Solicite outro.';
            }
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
    <title>Doce Controle - Redefinir Senha</title>
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
                    <i class="bi bi-key display-5 pink-shock"></i>
                    <h1 class="h3 fw-bold mt-3 mb-1">Redefinir senha</h1>
                    <p class="text-muted mb-0"><?= $token !== '' ? 'Crie uma nova senha para sua conta.' : 'Receba um link seguro no e-mail cadastrado.' ?></p>
                </div>

                <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

                <?php if ($sucesso === '' || $token !== ''): ?>
                    <form method="POST" class="d-grid gap-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                        <?php if ($token !== ''): ?>
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            <div>
                                <label class="form-label fw-bold">Nova senha</label>
                                <input type="password" name="senha" class="form-control form-control-lg" minlength="8" required>
                            </div>
                            <div>
                                <label class="form-label fw-bold">Confirmar nova senha</label>
                                <input type="password" name="confirmar_senha" class="form-control form-control-lg" minlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-danger btn-lg">Salvar nova senha</button>
                        <?php else: ?>
                            <div>
                                <label class="form-label fw-bold">E-mail cadastrado</label>
                                <input type="email" name="email" class="form-control form-control-lg" required autofocus>
                            </div>
                            <button type="submit" class="btn btn-danger btn-lg">Enviar link seguro</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="login.php" class="fw-bold pink-shock">Voltar para o login</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
