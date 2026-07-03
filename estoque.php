<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Erro: Usuário não logado.");
}

$user_id = $_SESSION['user_id'];

define('DOCE_APP_RETURN_CATALOGO', true);
$catalogoReceitas = include __DIR__ . '/api_receitas_publicas.php';
$ingredientesCatalogo = [];

foreach ($catalogoReceitas as $receitaCatalogo) {
    foreach (($receitaCatalogo['ingredientes'] ?? []) as $ingrediente) {
        $nome = trim($ingrediente['item_nome']);
        if ($nome === '') {
            continue;
        }
        $chave = function_exists('mb_strtolower') ? mb_strtolower($nome, 'UTF-8') : strtolower($nome);
        if (!isset($ingredientesCatalogo[$chave])) {
            $ingredientesCatalogo[$chave] = [
                'item_nome' => $nome,
                'unidade_medida' => $ingrediente['unidade_medida'] ?: 'un',
            ];
        }
    }
}

foreach ($ingredientesCatalogo as $ingredienteCatalogo) {
    $stmt = $pdo->prepare("SELECT id FROM estoque WHERE user_id = ? AND LOWER(item_nome) = LOWER(?) LIMIT 1");
    $stmt->execute([$user_id, $ingredienteCatalogo['item_nome']]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare(
            "INSERT INTO estoque (user_id, item_nome, unidade_medida, preco_unitario, quantidade_atual, estoque_minimo)
             VALUES (?, ?, ?, 0, 0, 0)"
        );
        $stmt->execute([$user_id, $ingredienteCatalogo['item_nome'], $ingredienteCatalogo['unidade_medida']]);
    }
}

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE user_id = ? ORDER BY item_nome ASC");
$stmt->execute([$user_id]);
$itens = $stmt->fetchAll();

function formatar_numero_input($valor)
{
    $valor = number_format(floatval($valor), 3, '.', '');
    return rtrim(rtrim($valor, '0'), '.');
}
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
    <title>Doce Controle - Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
    <style>
        .card { border-width: 2px; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-lg { padding: 15px 25px; }
        .kitchen-link { font-size: 1rem; }
        .stock-table th { white-space: nowrap; }
        .stock-table td { vertical-align: middle; }
        .stock-table input { min-width: 110px; }
        .unit-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            background: #e9f7ef;
            color: #198754;
            font-weight: 700;
        }
        @media (max-width: 767px) {
            .card { min-height: 320px; }
            .display-5 { font-size: 3rem; }
            .card-title { font-size: 1.5rem; }
            .btn-sm { padding: 0.85rem 1rem; font-size: 1rem; }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-box-seam"></i> Doce Controle - Estoque</span>
        <a href="index.php" class="btn btn-outline-light btn-sm">Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col d-flex flex-column flex-md-row gap-2">
            <button class="btn btn-success btn-lg shadow-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalInsumo">
                <i class="bi bi-plus-square"></i> Novo Insumo
            </button>
            <a href="clientes.php" class="btn btn-outline-secondary btn-lg shadow-sm flex-fill d-flex align-items-center justify-content-center">
                <i class="bi bi-people me-2"></i> Ver Clientes
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-success mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
                <div>
                    <h1 class="h4 mb-1">Tabela de ingredientes das receitas</h1>
                    <p class="text-muted mb-0">Preencha a quantidade comprada e o valor pago. O sistema calcula o custo por g, ml ou unidade.</p>
                </div>
                <button form="formEstoqueTabela" class="btn btn-success px-4">
                    <i class="bi bi-save"></i> Salvar tabela
                </button>
            </div>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success">Estoque atualizado com sucesso.</div>
            <?php endif; ?>

            <form id="formEstoqueTabela" action="salvar_estoque_tabela.php" method="POST">
                <div class="table-responsive">
                    <table class="table table-hover align-middle stock-table">
                        <thead class="table-success">
                            <tr>
                                <th>Ingrediente</th>
                                <th>Unidade base</th>
                                <th>Quantidade comprada</th>
                                <th>Valor pago (R$)</th>
                                <th>Custo unitario</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $i): ?>
                                <?php
                                    $quantidade = floatval($i['quantidade_atual']);
                                    $precoUnitario = floatval($i['preco_unitario']);
                                    $valorPago = $quantidade > 0 ? $quantidade * $precoUnitario : 0;
                                    $temPreco = $precoUnitario > 0;
                                    $estoqueNegativo = $quantidade < 0;
                                ?>
                                <tr class="<?= $estoqueNegativo ? 'table-danger' : '' ?>">
                                    <td class="fw-bold"><?= htmlspecialchars($i['item_nome']) ?></td>
                                    <td><span class="unit-badge"><?= htmlspecialchars($i['unidade_medida']) ?></span></td>
                                    <td>
                                        <input type="number" step="0.001" class="form-control quantidade-input" name="itens[<?= $i['id'] ?>][quantidade_atual]" value="<?= htmlspecialchars(formatar_numero_input($i['quantidade_atual'])) ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control valor-input" name="itens[<?= $i['id'] ?>][valor_pago]" value="<?= number_format($valorPago, 2, '.', '') ?>">
                                    </td>
                                    <td class="fw-bold custo-unitario">
                                        R$ <?= number_format($precoUnitario, 4, ',', '.') ?> / <?= htmlspecialchars($i['unidade_medida']) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $estoqueNegativo ? 'bg-danger' : ($temPreco ? 'bg-success' : 'bg-warning text-dark') ?>">
                                            <?= $estoqueNegativo ? 'Estoque negativo' : ($temPreco ? 'Com preco' : 'Falta preco') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <div class="row d-none">
        <?php if (empty($itens)): ?>
            <div class="col-12 text-center mt-5 text-muted">
                <i class="bi bi-cart-x display-1"></i>
                <p class="mt-3">Nenhum item no estoque. Comece cadastrando um novo insumo!</p>
            </div>
        <?php endif; ?>

        <?php foreach ($itens as $i): 
            $alerta = ($i['quantidade_atual'] <= $i['estoque_minimo']);
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100 <?= $alerta ? 'border-danger' : 'border-success' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title fw-bold text-uppercase"><?= htmlspecialchars($i['item_nome']) ?></h5>
                        <span class="badge <?= $alerta ? 'bg-danger' : 'bg-success' ?>">
                            <?= $alerta ? 'REPOR!' : 'ESTOQUE OK' ?>
                        </span>
                    </div>

                    <h2 class="display-5 mt-3 <?= $alerta ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($i['quantidade_atual'], 2, ',', '.') ?> 
                        <small class="fs-6 text-muted"><?= $i['unidade_medida'] ?></small>
                    </h2>
                    
                    <p class="text-muted mb-0 small">Mínimo: <?= $i['estoque_minimo'] ?> <?= $i['unidade_medida'] ?></p>
                    <p class="text-dark fw-bold">Preço: R$ <?= number_format($i['preco_unitario'], 2, ',', '.') ?></p>
                </div>
                
                <div class="card-footer bg-white border-0 pt-0 pb-3 d-flex flex-column flex-sm-row gap-2">
                    <a href="historico_precos.php?estoque_id=<?= $i['id'] ?>" class="btn btn-outline-dark btn-sm flex-fill">
                        <i class="bi bi-clock-history"></i> Histórico
                    </a>
                    <a href="editar_insumo.php?id=<?= $i['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="excluir_insumo.php?id=<?= $i['id'] ?>" class="btn btn-outline-danger btn-sm flex-fill" onclick="return confirm('Tem certeza que deseja apagar?')">
                        <i class="bi bi-trash"></i> Excluir
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalInsumo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="salvar_insumo.php" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Cadastrar Ingrediente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome do Insumo</label>
                        <input type="text" name="item_nome" class="form-control" placeholder="Ex: Leite Condensado" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Unidade</label>
                            <select name="unidade_medida" class="form-select">
                                <option value="g">Gramas (g)</option>
                                <option value="kg">Quilos (kg)</option>
                                <option value="un">Unidade (un)</option>
                                <option value="ml">Mililitros (ml)</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Preço Unitário (R$)</label>
                            <input type="number" step="0.01" name="preco_unitario" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Qtd. Atual</label>
                            <input type="number" step="0.001" name="quantidade_atual" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Estoque Mínimo</label>
                            <input type="number" step="0.001" name="estoque_minimo" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">Salvar no Estoque</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.stock-table tbody tr').forEach(row => {
    const quantidade = row.querySelector('.quantidade-input');
    const valor = row.querySelector('.valor-input');
    const custo = row.querySelector('.custo-unitario');
    const unidade = row.querySelector('.unit-badge')?.textContent.trim() || '';

    function atualizarCusto() {
        const qtd = Number(quantidade.value || 0);
        const val = Number(valor.value || 0);
        const unitario = qtd > 0 && val > 0 ? val / qtd : 0;
        custo.textContent = `R$ ${unitario.toFixed(4).replace('.', ',')} / ${unidade}`;
    }

    quantidade.addEventListener('input', atualizarCusto);
    valor.addEventListener('input', atualizarCusto);
});
</script>

    <script src="assets/pwa.js"></script>
</body>
</html>
