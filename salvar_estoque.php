<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_nome = $_POST['item_nome'];
    $unidade = $_POST['unidade_medida'];
    $preco = $_POST['preco_unitario'];
    $qtd = $_POST['quantidade_atual'];
    $minimo = $_POST['estoque_minimo'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO estoque (user_id, item_nome, unidade_medida, preco_unitario, quantidade_atual, estoque_minimo) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $item_nome, $unidade, $preco, $qtd, $minimo]);
    
    header("Location: estoque.php");
    exit;
}