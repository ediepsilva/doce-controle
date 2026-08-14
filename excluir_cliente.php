<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && doce_validar_csrf()) {
    $id = intval($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE cliente_id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
}

header('Location: clientes.php');
exit;
