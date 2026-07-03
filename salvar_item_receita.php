<?php
require_once 'config.php';

$receita_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $receita_id = intval($_POST['receita_id']);
    $insumo_id = intval($_POST['insumo_id']);
    $quantidade_usada = floatval($_POST['quantidade_usada']);

    $stmt = $pdo->prepare("SELECT id FROM receitas WHERE id = ? AND user_id = ?");
    $stmt->execute([$receita_id, $user_id]);
    $receita = $stmt->fetch();

    if ($receita && $insumo_id > 0 && $quantidade_usada > 0) {
        $stmt = $pdo->prepare("INSERT INTO receitas_itens (receita_id, insumo_id, quantidade_usada) VALUES (?, ?, ?)");
        $stmt->execute([$receita_id, $insumo_id, $quantidade_usada]);
    }
}

header('Location: editar_receita.php?id=' . $receita_id);
exit;
