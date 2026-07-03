<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM pedidos WHERE cliente_id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);

    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

header('Location: clientes.php');
exit;
