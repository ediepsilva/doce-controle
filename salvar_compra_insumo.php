<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $estoque_id = intval($_POST['estoque_id']);
    $preco_compra = floatval($_POST['preco_compra']);
    $quantidade_comprada = floatval($_POST['quantidade_comprada']);
    $data_compra = $_POST['data_compra'];
    $nota = filter_input(INPUT_POST, 'nota', FILTER_SANITIZE_SPECIAL_CHARS);

    $stmt = $pdo->prepare("SELECT id FROM estoque WHERE id = ? AND user_id = ?");
    $stmt->execute([$estoque_id, $user_id]);
    $item = $stmt->fetch();

    if ($item && $preco_compra > 0 && $quantidade_comprada >= 0) {
        $stmt = $pdo->prepare("INSERT INTO historico_precos (estoque_id, user_id, preco_compra, quantidade_comprada, data_compra, nota) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$estoque_id, $user_id, $preco_compra, $quantidade_comprada, $data_compra, $nota]);

        $stmt = $pdo->prepare("UPDATE estoque SET preco_unitario = ?, quantidade_atual = quantidade_atual + ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$preco_compra, $quantidade_comprada, $estoque_id, $user_id]);
    }
}

header('Location: historico_precos.php?estoque_id=' . $estoque_id);
exit;
