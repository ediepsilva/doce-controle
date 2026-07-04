<?php
require_once 'config.php';

$receita_id = 0;

if (isset($_GET['id'], $_GET['receita_id'])) {
    $id = intval($_GET['id']);
    $receita_id = intval($_GET['receita_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT id FROM receitas WHERE id = ? AND user_id = ?");
    $stmt->execute([$receita_id, $user_id]);
    $receita = $stmt->fetch();

    if ($receita) {
        $stmt = $pdo->prepare("DELETE FROM receitas_itens WHERE id = ? AND receita_id = ?");
        $stmt->execute([$id, $receita_id]);
    }
}

header('Location: editar_receita.php?id=' . $receita_id);
exit;
