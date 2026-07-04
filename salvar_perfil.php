<?php
require_once 'config.php';

function salvar_logo_marca($campo, $logoAtual = '')
{
    if (empty($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $logoAtual;
    }

    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK || $_FILES[$campo]['size'] > 3 * 1024 * 1024) {
        return $logoAtual;
    }

    $tmp = $_FILES[$campo]['tmp_name'];
    $info = @getimagesize($tmp);
    if (!$info) {
        return $logoAtual;
    }

    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = $info['mime'] ?? '';
    if (!isset($extensoes[$mime])) {
        return $logoAtual;
    }

    $pasta = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'marcas';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeArquivo = 'marca_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extensoes[$mime];
    $destino = $pasta . DIRECTORY_SEPARATOR . $nomeArquivo;

    if (!move_uploaded_file($tmp, $destino)) {
        return $logoAtual;
    }

    if ($logoAtual && strpos($logoAtual, 'uploads/marcas/') === 0) {
        $arquivoAntigo = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logoAtual);
        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }

    return 'uploads/marcas/' . $nomeArquivo;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !doce_validar_csrf()) {
    header('Location: perfil.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$nome = trim((string)($_POST['nome'] ?? ''));
$whatsapp = trim((string)($_POST['whatsapp'] ?? ''));

if ($nome !== '') {
    $stmt = $pdo->prepare("SELECT logo_marca FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $logoAtual = (string)$stmt->fetchColumn();
    $logoMarca = salvar_logo_marca('logo_marca', $logoAtual);

    $stmt = $pdo->prepare("UPDATE users SET nome = ?, whatsapp = ?, logo_marca = ? WHERE id = ?");
    $stmt->execute([$nome, $whatsapp, $logoMarca, $user_id]);
    $_SESSION['user_nome'] = $nome;
}

header('Location: perfil.php?sucesso=1');
exit;
