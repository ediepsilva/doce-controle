<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && doce_validar_csrf()) {
    $user_id = $_SESSION['user_id'];
    $pedido_id = intval($_POST['id'] ?? 0);

    if ($pedido_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ? AND user_id = ? AND estoque_baixado = 0");
        $stmt->execute([$pedido_id, $user_id]);
    }
}

header('Location: pedidos.php');
exit;
