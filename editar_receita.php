<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: receitas.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$receita_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM receitas WHERE id = ? AND user_id = ?");
$stmt->execute([$receita_id, $user_id]);
$receita = $stmt->fetch();

if (!$receita) {
    header('Location: receitas.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT ri.*, e.item_nome, e.unidade_medida, e.preco_unitario,
            (ri.quantidade_usada * e.preco_unitario) AS custo_item
     FROM receitas_itens ri
     JOIN estoque e ON e.id = ri.insumo_id
     WHERE ri.receita_id = ?"
);
$stmt->execute([$receita_id]);
$itens = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE user_id = ? ORDER BY item_nome ASC");
$stmt->execute([$user_id]);
$insumos = $stmt->fetchAll();

$custoTotal = 0;
foreach ($itens as $item) {
    $custoTotal += $item['custo_item'];
}
$precoSugeridoCalculado = $custoTotal * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO;
$imagemProduto = $receita['imagem_produto'] ?? '';
$imagemPreview = $imagemProduto !== '' ? $imagemProduto : 'assets/delicias-da-mara-logo.jpg';
$mostrarCardapio = intval($receita['mostrar_cardapio'] ?? 1) === 1;
$descricaoPublica = $receita['descricao_publica'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff007f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Doce Controle">
    <link rel="manifest" href="manifest.json">
    <title>Editar Receita - <?= htmlspecialchars($receita['nome_receita']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-warning mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-journal-text"></i> Editar Receita</span>
        <div>
            <a href="receitas.php" class="btn btn-outline-dark btn-sm">Voltar</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row gy-4">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Ficha Técnica</h5>
                    <form action="salvar_receita.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $receita['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Receita</label>
                            <input type="text" name="nome_receita" value="<?= htmlspecialchars($receita['nome_receita']) ?>" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Rendimento</label>
                                <input type="number" name="rendimento_porcoes" value="<?= htmlspecialchars($receita['rendimento_porcoes']) ?>" class="form-control" min="1" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Preço de Venda</label>
                                <input type="number" step="0.01" name="preco_venda_sugerido" value="<?= htmlspecialchars($receita['preco_venda_sugerido']) ?>" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Foto para o cardapio publico</label>
                            <div class="border rounded p-2 mb-2 bg-white">
                                <img src="<?= htmlspecialchars($imagemPreview) ?>" alt="Foto atual de <?= htmlspecialchars($receita['nome_receita']) ?>" class="img-fluid rounded" style="max-height: 220px; width: 100%; object-fit: contain;">
                            </div>
                            <input type="file" name="imagem_produto" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Use JPG, PNG ou WebP ate 3 MB. Se nao escolher outra foto, a atual continua.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descricao publica</label>
                            <textarea name="descricao_publica" class="form-control" rows="4" placeholder="Ex: Bolo fofinho com recheio cremoso, ideal para aniversarios."><?= htmlspecialchars($descricaoPublica) ?></textarea>
                            <small class="text-muted">Texto exibido no cardapio publico para vender melhor o produto.</small>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="mostrarCardapioInput" name="mostrar_cardapio" value="1" <?= $mostrarCardapio ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="mostrarCardapioInput">Mostrar no cardapio publico</label>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-warning w-100">Salvar Receita</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">Ingredientes</h5>
                    <form action="salvar_item_receita.php" method="POST">
                        <input type="hidden" name="receita_id" value="<?= $receita['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Insumo</label>
                            <select name="insumo_id" class="form-select" required>
                                <option value="">Selecione o insumo</option>
                                <?php foreach ($insumos as $insumo): ?>
                                    <option value="<?= $insumo['id'] ?>"><?= htmlspecialchars($insumo['item_nome']) ?> (<?= htmlspecialchars($insumo['unidade_medida']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantidade usada</label>
                            <input type="number" step="0.001" name="quantidade_usada" class="form-control" required>
                        </div>
                        <button class="btn btn-success w-100">Adicionar Ingrediente</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Resumo de Custos</h5>
                    <p class="mb-2">Custo total da receita: <strong>R$ <?= number_format($custoTotal, 2, ',', '.') ?></strong></p>
                    <p class="mb-2">Preco sugerido: <strong>R$ <?= number_format($precoSugeridoCalculado, 2, ',', '.') ?></strong></p>
                    <p class="mb-2">Margem estimada: <strong>R$ <?= number_format($precoSugeridoCalculado - $custoTotal, 2, ',', '.') ?></strong></p>
                    <hr>
                    <h6 class="mb-3">Ingredientes adicionados</h6>
                    <?php if (count($itens) === 0): ?>
                        <div class="alert alert-secondary">Nenhum ingrediente adicionado ainda.</div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($itens as $item): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($item['item_nome']) ?></div>
                                        <small class="text-muted">Quantidade: <?= number_format($item['quantidade_usada'], 3, ',', '.') ?> <?= htmlspecialchars($item['unidade_medida']) ?></small>
                                        <div class="small text-muted">Custo: R$ <?= number_format($item['custo_item'], 2, ',', '.') ?></div>
                                    </div>
                                    <a href="excluir_item_receita.php?id=<?= $item['id'] ?>&receita_id=<?= $receita['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover este item?')">Remover</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/pwa.js"></script>
</body>
</html>
