<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $selectImagem = doce_coluna_existe($pdo, 'receitas', 'imagem_produto') ? ', imagem_produto' : '';
    $stmt = $pdo->prepare("SELECT id$selectImagem FROM receitas WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $receita = $stmt->fetch();

    if ($receita) {
        if (!empty($receita['imagem_produto']) && strpos($receita['imagem_produto'], 'uploads/produtos/') === 0) {
            $arquivoImagem = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $receita['imagem_produto']);
            if (is_file($arquivoImagem)) {
                unlink($arquivoImagem);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM receitas_itens WHERE receita_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM receitas WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
}

header('Location: receitas.php');
exit;
