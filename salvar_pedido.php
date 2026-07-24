<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!doce_validar_csrf()) {
        header('Location: pedidos.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $pedido_id = intval($_POST['id'] ?? 0);
    $cliente_id = intval($_POST['cliente_id']);
    $receita_id = intval($_POST['receita_id']);
    $quantidade = intval($_POST['quantidade']);
    $data_entrega = $_POST['data_entrega'];
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);

    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE id = ? AND user_id = ?");
    $stmt->execute([$cliente_id, $user_id]);
    $clienteValido = $stmt->fetch();

    $stmt = $pdo->prepare(
        "SELECT r.preco_venda_sugerido,
                IFNULL(SUM(ri.quantidade_usada * e.preco_unitario), 0) AS custo_total,
                SUM(CASE WHEN e.preco_unitario IS NULL OR e.preco_unitario <= 0 THEN 1 ELSE 0 END) AS itens_sem_preco,
                COUNT(ri.id) AS total_itens
         FROM receitas r
         LEFT JOIN receitas_itens ri ON ri.receita_id = r.id
         LEFT JOIN estoque e ON e.id = ri.insumo_id
         WHERE r.id = ? AND r.user_id = ?
         GROUP BY r.id"
    );
    $stmt->execute([$receita_id, $user_id]);
    $receita = $stmt->fetch();

    if ($clienteValido && $receita && $quantidade > 0 && !empty($data_entrega)) {
        $receitaCompleta = intval($receita['total_itens']) > 0 && intval($receita['itens_sem_preco']) === 0;
        $precoUnitario = $receitaCompleta
            ? floatval($receita['custo_total']) * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO
            : floatval($receita['preco_venda_sugerido']);
        $valor_total = $precoUnitario * $quantidade;

        if ($pedido_id > 0) {
            $stmt = $pdo->prepare(
                "UPDATE pedidos
                 SET cliente_id = ?, receita_id = ?, quantidade = ?, data_entrega = ?, valor_total = ?, observacoes = ?
                 WHERE id = ? AND user_id = ? AND estoque_baixado = 0"
            );
            $stmt->execute([$cliente_id, $receita_id, $quantidade, $data_entrega, $valor_total, $observacoes, $pedido_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO pedidos (user_id, cliente_id, receita_id, quantidade, data_entrega, status, valor_total, observacoes, criado_em) VALUES (?, ?, ?, ?, ?, 'Pendente', ?, ?, NOW())");
            $stmt->execute([$user_id, $cliente_id, $receita_id, $quantidade, $data_entrega, $valor_total, $observacoes]);
        }
    }
}

header('Location: pedidos.php');
exit;
