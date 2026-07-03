<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM historico_precos WHERE estoque_id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);

    $stmt = $pdo->prepare("DELETE FROM estoque WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

header("Location: estoque.php");
exit;