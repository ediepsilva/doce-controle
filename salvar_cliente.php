<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $whatsapp = filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $user_id = $_SESSION['user_id'];

    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO clientes (user_id, nome, whatsapp, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $nome, $whatsapp, $email]);
    }

    header("Location: clientes.php");
    exit;
}