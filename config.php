<?php
$host = 'localhost';
$db   = 'doce_controle';
$user = 'root';
$pass = ''; // No XAMPP o padrão é vazio

if (!defined('DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO')) {
    define('DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO', 3);
}

function doce_hash_senha($senha)
{
    return password_hash($senha, PASSWORD_DEFAULT);
}

function doce_verificar_senha($senha, $hash)
{
    return password_verify($senha, $hash);
}

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
     // Configuramos o PDO para lançar exceções em caso de erro
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     // Forçamos o retorno dos dados como array associativo (mais fácil de usar)
     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
     $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
} catch (PDOException $e) {
     http_response_code(503);
     ?>
     <!DOCTYPE html>
     <html lang="pt-br">
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Doce Controle - Banco indisponivel</title>
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
         <link rel="stylesheet" href="assets/watermark.css">
     </head>
     <body class="bg-light">
         <main class="container min-vh-100 d-flex align-items-center justify-content-center">
             <div class="card shadow-sm" style="max-width: 620px;">
                 <div class="card-body p-4 p-md-5 text-center">
                     <h1 class="h3 fw-bold pink-shock">Banco de dados indisponivel</h1>
                     <p class="text-muted mb-3">
                         O MySQL do XAMPP nao esta respondendo agora. Abra o painel do XAMPP, clique em
                         <strong>Start</strong> no MySQL e atualize esta pagina.
                     </p>
                     <div class="alert alert-warning text-start small mb-0">
                         Detalhe tecnico: <?= htmlspecialchars($e->getMessage()) ?>
                     </div>
                 </div>
             </div>
         </main>
     </body>
     </html>
     <?php
     exit;
}

// Verifica se a sessão já não foi iniciada antes de chamar o session_start
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);
    session_start();
}

function doce_tabela_existe($pdo, $tabela)
{
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?"
        );
        $stmt->execute([$tabela]);
        return intval($stmt->fetchColumn()) > 0;
    } catch (Exception $e) {
        return false;
    }
}

function doce_coluna_existe($pdo, $tabela, $coluna)
{
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?"
        );
        $stmt->execute([$tabela, $coluna]);
        return intval($stmt->fetchColumn()) > 0;
    } catch (Exception $e) {
        return false;
    }
}

function doce_usuario_logado()
{
    return !empty($_SESSION['user_id']);
}

function doce_usuario_atual($pdo)
{
    if (!doce_usuario_logado() || !doce_tabela_existe($pdo, 'users')) {
        return null;
    }

    doce_garantir_colunas_usuario($pdo);

    $stmt = $pdo->prepare("SELECT id, nome, email, whatsapp, logo_marca, status, plano FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

function doce_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function doce_validar_csrf()
{
    $token = (string)($_POST['csrf_token'] ?? '');
    return $token !== '' && hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token);
}

function doce_garantir_colunas_usuario($pdo)
{
    if (!doce_tabela_existe($pdo, 'users')) {
        return;
    }

    $colunas = [
        'whatsapp' => "ALTER TABLE users ADD COLUMN whatsapp VARCHAR(40) NULL",
        'logo_marca' => "ALTER TABLE users ADD COLUMN logo_marca VARCHAR(255) NULL",
    ];

    foreach ($colunas as $coluna => $sql) {
        if (doce_coluna_existe($pdo, 'users', $coluna)) {
            continue;
        }

        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            // Mantem o app funcionando mesmo quando o banco nao permite ALTER TABLE.
        }
    }
}

function doce_garantir_colunas_pedidos($pdo)
{
    if (!doce_tabela_existe($pdo, 'pedidos') || doce_coluna_existe($pdo, 'pedidos', 'estoque_baixado')) {
        return;
    }

    try {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN estoque_baixado TINYINT(1) NOT NULL DEFAULT 0");
    } catch (Exception $e) {
        // Coluna auxiliar; se nao puder criar agora, o banco atualizado pelo SQL continua sendo o caminho oficial.
    }
}

function doce_garantir_coluna_imagem_receita($pdo)
{
    if (!doce_tabela_existe($pdo, 'receitas')) {
        return;
    }

    $colunas = [
        'imagem_produto' => "ALTER TABLE receitas ADD COLUMN imagem_produto VARCHAR(255) NULL",
        'mostrar_cardapio' => "ALTER TABLE receitas ADD COLUMN mostrar_cardapio TINYINT(1) NOT NULL DEFAULT 1",
        'descricao_publica' => "ALTER TABLE receitas ADD COLUMN descricao_publica TEXT NULL",
    ];

    foreach ($colunas as $coluna => $sql) {
        if (doce_coluna_existe($pdo, 'receitas', $coluna)) {
            continue;
        }

        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            // As colunas sao opcionais para manter o sistema funcionando mesmo sem permissao de ALTER TABLE.
        }
    }
}

function doce_usuario_inativo($pdo, $user_id)
{
    if (!doce_tabela_existe($pdo, 'users')) {
        return false;
    }

    $colunas = [];
    foreach (['status', 'plano'] as $coluna) {
        if (doce_coluna_existe($pdo, 'users', $coluna)) {
            $colunas[] = $coluna;
        }
    }

    if (!$colunas) {
        return false;
    }

    $select = implode(', ', $colunas);
    $stmt = $pdo->prepare("SELECT $select FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        return false;
    }

    foreach ($colunas as $coluna) {
        if (strtolower(trim((string)$usuario[$coluna])) === 'inativo') {
            return true;
        }
    }

    return false;
}

$paginaAtual = basename($_SERVER['SCRIPT_NAME'] ?? '');
$paginasLiberadas = [
    'login.php',
    'cadastro.php',
    'redefinir_senha.php',
    'logout.php',
    'cobranca.php',
    'cardapio.php',
    'api_receitas.php',
    'api_receitas_publicas.php',
    'testar_conexao.php',
];

doce_garantir_coluna_imagem_receita($pdo);
doce_garantir_colunas_usuario($pdo);
doce_garantir_colunas_pedidos($pdo);

if (!in_array($paginaAtual, $paginasLiberadas, true) && !doce_usuario_logado()) {
    header('Location: login.php');
    exit;
}

if (doce_usuario_logado() && !in_array($paginaAtual, $paginasLiberadas, true) && doce_usuario_inativo($pdo, $_SESSION['user_id'])) {
    header('Location: cobranca.php');
    exit;
}
?>
