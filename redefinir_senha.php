<?php
require_once 'config.php';

if (doce_usuario_logado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';
$identificador = '';

function somente_digitos($valor)
{
    return preg_replace('/\D+/', '', (string)$valor);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim((string)($_POST['identificador'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    $confirmarSenha = (string)($_POST['confirmar_senha'] ?? '');

    if ($identificador === '' || $senha === '') {
        $erro = 'Informe o e-mail ou celular e a nova senha.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas nao conferem.';
    } elseif (!doce_tabela_existe($pdo, 'users') || !doce_coluna_existe($pdo, 'users', 'password_hash')) {
        $erro = 'Tabela de usuarios nao encontrada. Execute o arquivo schema.sql no banco.';
    } else {
        $usuario = null;

        if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare("SELECT id, nome FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$identificador]);
            $usuario = $stmt->fetch();
        } elseif (doce_coluna_existe($pdo, 'users', 'whatsapp')) {
            $digitos = somente_digitos($identificador);
            $stmt = $pdo->query("SELECT id, nome, whatsapp FROM users");
            foreach ($stmt->fetchAll() as $linha) {
                if ($digitos !== '' && somente_digitos($linha['whatsapp'] ?? '') === $digitos) {
                    $usuario = $linha;
                    break;
                }
            }
        }

        if (!$usuario) {
            $erro = 'Nao encontramos uma conta com esse e-mail ou celular.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([doce_hash_senha($senha), intval($usuario['id'])]);
            $sucesso = 'Senha redefinida com sucesso. Agora voce ja pode entrar.';
            $identificador = '';
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
                    <h1 class="h3 fw-bold mt-3 mb-1">Redefinir Senha</h1>
                    <p class="text-muted mb-0">Use o e-mail ou celular cadastrado para criar uma nova senha.</p>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>
                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <form method="POST" class="d-grid gap-3">
                    <div>
                        <label class="form-label fw-bold">E-mail ou celular</label>
                        <input type="text" name="identificador" class="form-control form-control-lg" value="<?= htmlspecialchars($identificador) ?>" placeholder="email@exemplo.com ou (00) 00000-0000" required autofocus>
                    </div>
                    <div>
                        <label class="form-label fw-bold">Nova senha</label>
                        <div class="input-group input-group-lg">
                            <input type="password" name="senha" id="senha" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="senha" aria-label="Mostrar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-bold">Confirmar nova senha</label>
                        <div class="input-group input-group-lg">
                            <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmar_senha" aria-label="Mostrar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-check-circle"></i> Salvar nova senha
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="login.php" class="fw-bold pink-shock">Voltar para o login</a>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.querySelectorAll('.toggle-password').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.target);
                const icon = button.querySelector('i');
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        });
    </script>
    <script src="assets/pwa.js"></script>
</body>
</html>
