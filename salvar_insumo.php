<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id          = $_SESSION['user_id'];
    $item_nome        = filter_input(INPUT_POST, 'item_nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $unidade_medida   = $_POST['unidade_medida'];
    $preco_unitario   = floatval($_POST['preco_unitario']);
    $quantidade_atual = floatval($_POST['quantidade_atual']);
    $estoque_minimo   = floatval($_POST['estoque_minimo']);
    $id               = isset($_POST['id']) ? intval($_POST['id']) : null;

    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT preco_unitario FROM estoque WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $item = $stmt->fetch();

            if ($item && floatval($item['preco_unitario']) !== $preco_unitario) {
                $hist = $pdo->prepare("INSERT INTO historico_precos (estoque_id, user_id, preco_compra, quantidade_comprada, nota, data_compra) VALUES (?, ?, ?, ?, ?, NOW())");
                $hist->execute([$id, $user_id, $preco_unitario, null, 'Atualização de preço via edição de insumo']);
            }

            $sql = "UPDATE estoque SET item_nome = ?, unidade_medida = ?, preco_unitario = ?, quantidade_atual = ?, estoque_minimo = ? WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$item_nome, $unidade_medida, $preco_unitario, $quantidade_atual, $estoque_minimo, $id, $user_id]);
        } else {
            $sql = "INSERT INTO estoque (user_id, item_nome, unidade_medida, preco_unitario, quantidade_atual, estoque_minimo) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $item_nome, $unidade_medida, $preco_unitario, $quantidade_atual, $estoque_minimo]);
            $insertId = $pdo->lastInsertId();

            $hist = $pdo->prepare("INSERT INTO historico_precos (estoque_id, user_id, preco_compra, quantidade_comprada, nota, data_compra) VALUES (?, ?, ?, ?, ?, NOW())");
            $hist->execute([$insertId, $user_id, $preco_unitario, $quantidade_atual, 'Compra inicial registrada no cadastro de insumo']);
        }

        header("Location: estoque.php?sucesso=1");
        exit;
    } catch (Exception $e) {
        die("Erro ao salvar insumo: " . $e->getMessage());
    }
}