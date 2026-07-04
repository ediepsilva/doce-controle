<?php
require_once 'config.php';

function garantir_coluna_estoque_baixado($pdo)
{
    if (!doce_coluna_existe($pdo, 'pedidos', 'estoque_baixado')) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN estoque_baixado TINYINT(1) NOT NULL DEFAULT 0");
    }
}

function baixar_estoque_pedido($pdo, $pedido)
{
    if (intval($pedido['estoque_baixado'] ?? 0) === 1) {
        return;
    }

    $stmt = $pdo->prepare(
        "SELECT ri.insumo_id, SUM(ri.quantidade_usada * ?) AS quantidade_total
         FROM receitas_itens ri
         WHERE ri.receita_id = ?
         GROUP BY ri.insumo_id"
    );
    $stmt->execute([intval($pedido['quantidade']), intval($pedido['receita_id'])]);
    $itens = $stmt->fetchAll();

    $update = $pdo->prepare(
        "UPDATE estoque
         SET quantidade_atual = quantidade_atual - ?
         WHERE id = ? AND user_id = ?"
    );

    foreach ($itens as $item) {
        $update->execute([
            floatval($item['quantidade_total']),
            intval($item['insumo_id']),
            intval($pedido['user_id']),
        ]);
    }

    $stmt = $pdo->prepare("UPDATE pedidos SET estoque_baixado = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([intval($pedido['id']), intval($pedido['user_id'])]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && doce_validar_csrf() && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];
    $valid = ['Pendente', 'Em Produção', 'Pronto', 'Entregue'];

    if (in_array($status, $valid, true)) {
        garantir_coluna_estoque_baixado($pdo);

        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$id, $user_id]);
        $pedido = $stmt->fetch();

        if ($pedido) {
            try {
                $pdo->beginTransaction();
                if ($status !== 'Pendente') {
                    baixar_estoque_pedido($pdo, $pedido);
                }
                $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$status, $id, $user_id]);
                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }
        }
    }
}

header('Location: pedidos.php');
exit;
