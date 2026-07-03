<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: estoque.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$itens = $_POST['itens'] ?? [];

foreach ($itens as $id => $dados) {
    $id = intval($id);
    $quantidade = floatval(str_replace(',', '.', $dados['quantidade_atual'] ?? 0));
    $valorPago = floatval(str_replace(',', '.', $dados['valor_pago'] ?? 0));

    if ($id <= 0) {
        continue;
    }

    $stmt = $pdo->prepare("SELECT preco_unitario FROM estoque WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $precoAtual = floatval($stmt->fetchColumn());
    $precoUnitario = $quantidade > 0 && $valorPago > 0 ? $valorPago / $quantidade : $precoAtual;

    $stmt = $pdo->prepare(
        "UPDATE estoque
         SET quantidade_atual = ?, preco_unitario = ?
         WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$quantidade, $precoUnitario, $id, $user_id]);
}

header('Location: estoque.php?sucesso=1');
exit;
?>
