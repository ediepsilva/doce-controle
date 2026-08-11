<?php
function doce_carregar_env($arquivo)
{
    if (!is_file($arquivo) || !is_readable($arquivo)) {
        return;
    }

    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        if ($linha === '' || substr($linha, 0, 1) === '#' || strpos($linha, '=') === false) {
            continue;
        }

        [$nome, $valor] = explode('=', $linha, 2);
        $nome = trim($nome);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");

        if ($nome !== '' && getenv($nome) === false && !array_key_exists($nome, $_ENV)) {
            $_ENV[$nome] = $valor;

            if (function_exists('putenv')) {
                @putenv($nome . '=' . $valor);
            }
        }
    }
}

doce_carregar_env(__DIR__ . DIRECTORY_SEPARATOR . '.env');

function doce_env($nome, $padrao = '')
{
    $valor = getenv($nome);

    if ($valor !== false) {
        return $valor;
    }

    return array_key_exists($nome, $_ENV) ? $_ENV[$nome] : $padrao;
}

$host = doce_env('DB_HOST', 'localhost');
$db   = doce_env('DB_NAME', 'doce_controle');
$user = doce_env('DB_USER', 'root');
$pass = doce_env('DB_PASS', '');
$port = doce_env('DB_PORT', '3306');

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
     $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
     // Configuramos o PDO para lançar exceções em caso de erro
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     // Forçamos o retorno dos dados como array associativo (mais fácil de usar)
     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
     $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
} catch (PDOException $e) {
     $mostrarDetalhe = filter_var(doce_env('APP_DEBUG', '0'), FILTER_VALIDATE_BOOLEAN);
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
                     <?php if ($mostrarDetalhe): ?>
                         <div class="alert alert-warning text-start small mb-0">
                             Detalhe tecnico: <?= htmlspecialchars($e->getMessage()) ?>
                         </div>
                     <?php endif; ?>
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
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
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

function doce_ip_cliente()
{
    return $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
}

function doce_arquivo_tentativas_login()
{
    $pasta = __DIR__ . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }
    return $pasta . DIRECTORY_SEPARATOR . '.login_attempts.json';
}

function doce_login_tentativas_recentes($chave)
{
    $arquivo = doce_arquivo_tentativas_login();
    if (!is_file($arquivo)) {
        return [];
    }

    $dados = json_decode((string)@file_get_contents($arquivo), true);
    if (!is_array($dados) || empty($dados[$chave]) || !is_array($dados[$chave])) {
        return [];
    }

    $limite = time() - 900; // janela de 15 minutos
    return array_values(array_filter($dados[$chave], function ($t) use ($limite) {
        return $t > $limite;
    }));
}

function doce_login_bloqueado($chave)
{
    return count(doce_login_tentativas_recentes($chave)) >= 5;
}

function doce_registrar_falha_login($chave)
{
    $fp = @fopen(doce_arquivo_tentativas_login(), 'c+');
    if (!$fp) {
        return;
    }

    flock($fp, LOCK_EX);
    $dados = json_decode((string)stream_get_contents($fp), true);
    if (!is_array($dados)) {
        $dados = [];
    }

    $limite = time() - 900;
    $tentativas = !empty($dados[$chave]) && is_array($dados[$chave])
        ? array_values(array_filter($dados[$chave], function ($t) use ($limite) {
            return $t > $limite;
        }))
        : [];
    $tentativas[] = time();
    $dados[$chave] = $tentativas;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($dados));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function doce_limpar_tentativas_login($chave)
{
    $arquivo = doce_arquivo_tentativas_login();
    if (!is_file($arquivo)) {
        return;
    }

    $fp = @fopen($arquivo, 'c+');
    if (!$fp) {
        return;
    }

    flock($fp, LOCK_EX);
    $dados = json_decode((string)stream_get_contents($fp), true);
    if (is_array($dados) && isset($dados[$chave])) {
        unset($dados[$chave]);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($dados));
        fflush($fp);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
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
    return doce_validar_csrf_token($token);
}

function doce_validar_csrf_token($token)
{
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
        'criado_em' => "ALTER TABLE users ADD COLUMN criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
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

function doce_garantir_fk_user($pdo, $tabela, $colunaFk)
{
    // Bancos antigos (de antes da tabela "users" existir) podem ter
    // "receitas"/"pedidos" referenciando a tabela legada "usuarios" por
    // engano, o que quebra o INSERT para qualquer conta que nao seja a
    // primeira criada. Corrige a foreign key para apontar para "users".
    try {
        $stmt = $pdo->prepare(
            "SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1"
        );
        $stmt->execute([$tabela, $colunaFk]);
        $fk = $stmt->fetch();

        if (!$fk || $fk['REFERENCED_TABLE_NAME'] === 'users') {
            return;
        }

        $pdo->exec("ALTER TABLE `{$tabela}` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
        $pdo->exec("ALTER TABLE `{$tabela}` ADD CONSTRAINT `fk_{$tabela}_user` FOREIGN KEY (`{$colunaFk}`) REFERENCES users(id) ON DELETE CASCADE");
    } catch (Exception $e) {
        // Mantem o app funcionando mesmo quando o banco nao permite ALTER TABLE.
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
        'ingredientes_texto' => "ALTER TABLE receitas ADD COLUMN ingredientes_texto TEXT NULL",
        'modo_preparo' => "ALTER TABLE receitas ADD COLUMN modo_preparo TEXT NULL",
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

function doce_garantir_migracoes($pdo)
{
    // Versao das checagens abaixo: aumente ao adicionar uma nova coluna/tabela
    // as funcoes doce_garantir_* para forcar a checagem novamente uma vez.
    $versaoMigracoes = '2026-08-10-3';
    $arquivoMarcador = __DIR__ . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR . '.schema_ok';

    if (is_file($arquivoMarcador) && trim((string)@file_get_contents($arquivoMarcador)) === $versaoMigracoes) {
        return;
    }

    doce_garantir_coluna_imagem_receita($pdo);
    doce_garantir_colunas_usuario($pdo);
    doce_garantir_colunas_pedidos($pdo);
    doce_garantir_fk_user($pdo, 'receitas', 'user_id');
    doce_garantir_fk_user($pdo, 'pedidos', 'user_id');

    $pasta = dirname($arquivoMarcador);
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }
    @file_put_contents($arquivoMarcador, $versaoMigracoes);
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
$paginasSemLogin = [
    'login.php',
    'cadastro.php',
    'redefinir_senha.php',
    'logout.php',
    'cobranca.php',
    'cardapio.php',
    'salvar_pedido_publico.php',
    'api_receitas.php',
    'api_receitas_publicas.php',
];

$paginasPermitidasInativo = [
    'login.php',
    'logout.php',
    'cobranca.php',
    'cardapio.php',
    'api_receitas_publicas.php',
];

doce_garantir_migracoes($pdo);

if (!in_array($paginaAtual, $paginasSemLogin, true) && !doce_usuario_logado()) {
    header('Location: login.php');
    exit;
}

if (doce_usuario_logado() && !in_array($paginaAtual, $paginasPermitidasInativo, true) && doce_usuario_inativo($pdo, $_SESSION['user_id'])) {
    header('Location: cobranca.php');
    exit;
}
?>
