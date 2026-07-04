<?php
require_once 'config.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT r.*, IFNULL(SUM(i.quantidade_usada * e.preco_unitario), 0) AS custo_total, COUNT(i.id) AS total_itens
     FROM receitas r
     LEFT JOIN receitas_itens i ON i.receita_id = r.id
     LEFT JOIN estoque e ON e.id = i.insumo_id
     WHERE r.user_id = ?
     GROUP BY r.id
     ORDER BY r.nome_receita ASC"
);
$stmt->execute([$user_id]);
$receitas = $stmt->fetchAll();
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
    <title>Doce Controle - Receitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
    <style>
        .pink-shock { color: #ff007f !important; }
        .pink-shock-bg { background-color: #ff007f !important; color: #fff !important; }
        .bg-warning,
        .navbar.bg-warning,
        .modal-header.bg-warning,
        .table-warning,
        .border-warning {
            background-color: #ff007f !important;
            border-color: #ff007f !important;
            color: #ffffff !important;
        }
        .navbar .navbar-brand,
        .navbar .btn,
        .modal-header.bg-warning .modal-title {
            color: #ffffff !important;
        }
        .navbar .btn {
            border-color: #ffffff !important;
            background-color: transparent !important;
            color: #ffffff !important;
        }
        .navbar .btn:hover {
            background-color: #ffffff !important;
            color: #ff007f !important;
        }
        .card,
        .modal-content,
        .list-group-item,
        .alert,
        .border {
            border-color: #ff007f !important;
        }
        .card,
        .modal-content,
        .list-group-item,
        .form-control,
        .form-select {
            background-color: #fff7fb !important;
        }
        .form-control,
        .form-select {
            border: 2px solid #ff007f !important;
            color: #ff007f !important;
            font-weight: 600;
        }
        .form-control::placeholder {
            color: rgba(255, 0, 127, 0.7) !important;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #ff007f !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 0, 127, 0.2) !important;
        }
        .card-title,
        .modal-title,
        .form-label,
        .list-group-item strong,
        .border strong {
            color: #ff007f !important;
        }
        .badge,
        .btn-warning {
            background-color: #ff007f !important;
            border-color: #ff007f !important;
            color: #ffffff !important;
        }
        .btn-outline-dark,
        .btn-outline-danger,
        .btn-outline-light {
            border-color: #ff007f !important;
            color: #ff007f !important;
        }
        .btn-outline-dark:hover,
        .btn-outline-danger:hover,
        .btn-outline-light:hover {
            background-color: #ff007f !important;
            color: #ffffff !important;
        }
        .menu-card-rosa {
            border: 1px solid rgba(255, 0, 127, 0.2) !important;
            border-top: 5px solid var(--recipe-color, #ff007f) !important;
            background: #ffffff !important;
            color: #212529 !important;
            position: relative;
        }
        .menu-card-rosa .card-header {
            background-color: color-mix(in srgb, var(--recipe-color, #ff007f) 18%, #ffffff) !important;
            border: none;
        }
        .menu-card-rosa .card-body .card-title,
        .menu-card-rosa .card-body strong {
            color: #ff007f !important;
        }
        .menu-card-rosa .card-body .small {
            color: #495057 !important;
        }
        .menu-card-rosa .badge {
            background-color: #ff007f !important;
            color: #ffffff !important;
        }
        .recipe-chip {
            background: rgba(255, 193, 7, 0.18);
            border: 1px solid rgba(255, 193, 7, 0.35);
            border-radius: 999px;
            color: #212529;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .catalogo-body {
            background:
                linear-gradient(135deg, rgba(255, 0, 127, 0.08), rgba(255, 193, 7, 0.14)),
                #fff;
            position: relative;
            overflow: hidden;
        }
        .catalogo-body::before {
            content: "Doce Controle";
            position: absolute;
            right: 1.5rem;
            bottom: 1rem;
            font-size: clamp(2.5rem, 8vw, 6rem);
            font-weight: 800;
            color: #ff007f !important;
            opacity: 0.08;
            pointer-events: none;
            transform: rotate(-8deg);
            white-space: nowrap;
        }
        .catalogo-body > * {
            position: relative;
            z-index: 1;
        }
        #modalDetalheReceita .modal-header {
            background: #ff007f !important;
            color: #ffffff !important;
            border-bottom: none;
        }
        #modalDetalheReceita .modal-title {
            color: #ffffff !important;
        }
        #modalDetalheReceita .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 1;
        }
        .receita-logo-teste {
            width: 100%;
            min-height: 150px;
            border-radius: 8px;
            border: 1px solid rgba(255, 0, 127, 0.18);
            background:
                radial-gradient(circle at 20% 20%, rgba(255, 0, 127, 0.2), transparent 28%),
                radial-gradient(circle at 80% 30%, rgba(255, 193, 7, 0.28), transparent 30%),
                linear-gradient(135deg, #fff7fb, #fff4cf);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff007f;
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1.25rem;
        }
        .modal-window-actions {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-left: auto;
        }
        .modal-window-actions .btn {
            width: 2rem;
            height: 2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        #modalDetalheReceita .modal-window-actions .btn {
            border-color: rgba(255, 255, 255, 0.75);
            color: #ffffff;
        }
        #modalDetalheReceita .modal-window-actions .btn:hover {
            background: rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-warning mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-journal-check"></i> Doce Controle - Receitas</span>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-dark btn-sm">Dashboard</a>
            <a href="clientes.php" class="btn btn-outline-dark btn-sm">Clientes</a>
            <a href="cardapio.php" class="btn btn-outline-dark btn-sm" target="_blank">Cardapio Publico</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <h1 class="h3 mb-0">Receitas</h1>
                <p class="text-muted mb-0">Fichas técnicas baseadas em custos reais do estoque.</p>
            </div>
            <button class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#modalReceita">
                <i class="bi bi-plus-circle"></i> Nova Receita
            </button>
            <button id="btnMenuReceitas" class="btn btn-warning btn-lg border-dark text-dark" data-bs-toggle="modal" data-bs-target="#modalMenuReceitas">
                <i class="bi bi-list-stars"></i> Menu de Receitas
            </button>
        </div>
    </div>

    <?php if (count($receitas) === 0): ?>
        <div class="alert alert-secondary text-center">Nenhuma receita cadastrada. Crie uma nova ficha técnica para começar.</div>
    <?php endif; ?>
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($receitas as $r): ?>
            <?php $margem = $r['preco_venda_sugerido'] - $r['custo_total']; ?>
            <?php $precoSugeridoCalculado = $r['custo_total'] * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO; ?>
            <div class="col">
                <div class="card shadow-sm h-100 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title fw-bold pink-shock"><?= htmlspecialchars($r['nome_receita']) ?></h5>
                            <div class="d-flex flex-column align-items-end gap-1">
                                <span class="badge bg-secondary">Itens: <?= $r['total_itens'] ?></span>
                                <?php if (intval($r['mostrar_cardapio'] ?? 1) === 1): ?>
                                    <span class="badge bg-success">No cardapio</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Oculta</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="mb-1 text-muted">Rendimento: <?= $r['rendimento_porcoes'] ?> porções</p>
                        <div class="mb-3">
                            <strong class="pink-shock">Custo:</strong> <span class="pink-shock">R$ <?= number_format($r['custo_total'], 2, ',', '.') ?></span><br>
                            <strong class="pink-shock">Preco sugerido:</strong> <span class="pink-shock">R$ <?= number_format($precoSugeridoCalculado, 2, ',', '.') ?></span><br>
                            <strong class="pink-shock">Margem:</strong> <span class="pink-shock">R$ <?= number_format($precoSugeridoCalculado - $r['custo_total'], 2, ',', '.') ?></span>
                        </div>
                        <a href="editar_receita.php?id=<?= $r['id'] ?>" class="btn btn-outline-dark btn-sm">Detalhes</a>
                        <a href="excluir_receita.php?id=<?= $r['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Excluir esta receita?')">Excluir</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalReceita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formReceita" action="salvar_receita.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-journal-plus"></i> Criar Receita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome da Receita</label>
                        <input type="text" id="nomeReceitaInput" name="nome_receita" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Rendimento</label>
                            <input type="number" id="rendimentoReceitaInput" name="rendimento_porcoes" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Preço de Venda</label>
                            <input type="number" id="precoReceitaInput" step="0.01" name="preco_venda_sugerido" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto para o cardapio publico</label>
                        <input type="file" name="imagem_produto" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Opcional. Use JPG, PNG ou WebP ate 3 MB.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descricao publica</label>
                        <textarea name="descricao_publica" class="form-control" rows="3" placeholder="Ex: Bolo fofinho com recheio cremoso, ideal para aniversarios."></textarea>
                        <small class="text-muted">Texto que aparece no cardapio para os clientes.</small>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="mostrarCardapioInput" name="mostrar_cardapio" value="1" checked>
                        <label class="form-check-label fw-bold" for="mostrarCardapioInput">Mostrar no cardapio publico</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4">Salvar Receita</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Menu de Receitas via API -->
<div class="modal fade" id="modalMenuReceitas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
        <div class="modal-content border-warning" style="border-top: 4px solid #ffc107;">
            <div class="modal-header bg-gradient" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cake2"></i> Catálogo de Receitas</h5>
                <div class="modal-window-actions">
                    <button type="button" class="btn btn-outline-dark btn-sm" data-modal-toggle-size="modalMenuReceitas" aria-label="Maximizar">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
            </div>
            <div class="modal-body catalogo-body">
                <div class="mb-4">
                    <input type="text" id="buscaReceitas" class="form-control form-control-lg" placeholder="🔍 Buscar receita (ex: Brigadeiro, Bolo...)">
                    <small class="text-muted d-block mt-1">Digite para filtrar as receitas disponíveis</small>
                </div>
                <div id="menuReceitasList" class="row g-4">
                    <div class="col-12 text-center text-muted py-5">
                        <div class="spinner-border text-warning mb-3" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p>Carregando catálogo de receitas...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar Catálogo</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalheReceita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detalheReceitaTitulo">Receita</h5>
                <div class="modal-window-actions">
                    <button type="button" class="btn btn-outline-light btn-sm" data-modal-toggle-size="modalDetalheReceita" aria-label="Maximizar">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
            </div>
            <div class="modal-body" id="detalheReceitaConteudo"></div>
            <div class="modal-footer bg-light" id="detalheReceitaRodape">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Variável global para armazenar todas as receitas
let todasAsReceitas = [];
let receitasRenderizadas = [];

// Carrega receitas via API quando o modal for aberto
document.getElementById('modalMenuReceitas').addEventListener('show.bs.modal', function () {
    carregarMenuReceitas();
});

// Busca em tempo real
document.getElementById('buscaReceitas').addEventListener('keyup', function () {
    filtrarReceitas(this.value);
});

document.querySelectorAll('[data-modal-toggle-size]').forEach(botao => {
    botao.addEventListener('click', function () {
        alternarTamanhoModal(this.dataset.modalToggleSize, this);
    });
});

document.getElementById('modalReceita').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formReceita').reset();
});

function alternarTamanhoModal(modalId, botao) {
    const modal = document.getElementById(modalId);
    const dialog = modal ? modal.querySelector('.modal-dialog') : null;
    const icone = botao.querySelector('i');

    if (!dialog || !icone) return;

    const maximizado = dialog.classList.toggle('modal-fullscreen');
    dialog.classList.toggle('modal-dialog-centered', !maximizado);
    botao.setAttribute('aria-label', maximizado ? 'Minimizar' : 'Maximizar');
    icone.className = maximizado ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen';
}

async function carregarMenuReceitas() {
    const container = document.getElementById('menuReceitasList');
    container.innerHTML = `
        <div class="col-12 text-center text-muted py-5">
            <div class="spinner-border text-warning mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p>Carregando catálogo...</p>
        </div>
    `;

    // Buscar receitas do usuário e incluir ingredientes no menu
    const url = 'api_receitas.php?acao=listar';

    try {
        // Envia cookies de sessão para o endpoint local (credentials: 'same-origin')
        const resp = await fetch(url, {cache: 'no-store', credentials: 'same-origin'});
        const data = await resp.json();

        if (!data.sucesso) {
            // Se a chamada local falhar por autenticação, tenta o catálogo público
            const publicData = await buscarReceitasPublicas();
            if (publicData && publicData.sucesso && publicData.dados && publicData.dados.length > 0) {
                todasAsReceitas = publicData.dados;
                renderizarReceitas(todasAsReceitas);
                return;
            }
            container.innerHTML = `<div class="col-12 text-center text-danger py-4">⚠️ ${data.mensagem}</div>`;
            return;
        }

        todasAsReceitas = data.dados || [];
        if (todasAsReceitas.length === 0) {
            // fallback para catálogo público quando não houver receitas locais
            const publicData = await buscarReceitasPublicas();
            if (publicData && publicData.sucesso && publicData.dados && publicData.dados.length > 0) {
                todasAsReceitas = publicData.dados;
                renderizarReceitas(todasAsReceitas);
                return;
            }
            container.innerHTML = '<div class="col-12 text-center text-muted py-4">Nenhuma receita disponível no momento.</div>';
            return;
        }

        renderizarReceitas(todasAsReceitas);
    } catch (err) {
        container.innerHTML = `<div class="col-12 text-center text-danger py-4">❌ Erro ao carregar: ${err.message}</div>`;
    }
}

function renderizarReceitas(receitas) {
    const container = document.getElementById('menuReceitasList');
    receitasRenderizadas = receitas;
    
    if (receitas.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-4">📭 Nenhuma receita encontrada com esse termo.</div>';
        return;
    }

    const icones = ['🍫', '🎂', '🍦', '🍮', '🧁', '🍩', '🍪', '🥐', '🍰', '🍫', '🌟', '💎', '✨', '🎁', '👑'];
    const cores = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#F38181', '#AA96DA', '#FCBAD3', '#A8D8EA', '#FFC299', '#FFB3BA'];
    const rosaChoque = '#ff007f';

    let html = '';
    receitas.forEach((r, idx) => {
        const icone = icones[idx % icones.length];
        const cor = cores[idx % cores.length];
        const descricaoHtml = r.descricao
            ? `<p class="small mb-2">${escapeHtml(r.descricao)}</p>`
            : '';
        const ingredientesHtml = '';
        const precoSugerido = Number(r.preco_sugerido_calculado || 0);
        const precoSugeridoBadge = r.custo_status === 'completo' && precoSugerido > 0
            ? `R$ ${formatarMoeda(precoSugerido)}`
            : 'Preco pendente';
        const precoSugeridoHtml = r.custo_status === 'completo' && precoSugerido > 0
            ? `<span class="d-block"><i class="bi bi-tag"></i> Preco sugerido: R$ ${formatarMoeda(precoSugerido)}</span>`
            : '<span class="d-block"><i class="bi bi-exclamation-triangle"></i> Preco sugerido pendente</span>';
        const acaoHtml = `<button type="button" class="btn btn-sm btn-warning fw-bold mt-auto btn-ver-receita" data-receita-index="${idx}">
                <i class="bi bi-eye"></i> Ver receita
           </button>`;

        html += `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 overflow-hidden recipe-card menu-card-rosa" 
                     style="--recipe-color: ${cor}; transition: all 0.3s ease; cursor: pointer;">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center" 
                         style="background-color: ${cor}15 !important; border: none;">
                        <span class="fs-3">${icone}</span>
                        <span class="badge rounded-pill pink-shock-bg">${precoSugeridoBadge}</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title fw-bold mb-2">${escapeHtml(r.nome_receita)}</h6>
                        ${descricaoHtml}
                        ${ingredientesHtml}
                        <div class="small mb-3">
                            <span class="d-block"><i class="bi bi-signpost"></i> ${r.rendimento_porcoes} porções</span>
                            ${precoSugeridoHtml}
                        </div>
                        ${acaoHtml}
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Adicionar efeito hover nas cards
    document.querySelectorAll('.recipe-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    document.querySelectorAll('.btn-ver-receita').forEach(botao => {
        botao.addEventListener('click', function() {
            abrirDetalhesReceita(Number(this.dataset.receitaIndex));
        });
    });
}

function abrirDetalhesReceita(index) {
    const receita = receitasRenderizadas[index];
    if (!receita) return;

    const ingredientes = receita.ingredientes && receita.ingredientes.length
        ? `<ul class="list-group list-group-flush mb-4">${receita.ingredientes.map(i => `
                <li class="list-group-item px-0">
                    <div class="d-flex justify-content-between gap-3">
                        <span>
                            ${escapeHtml(i.quantidade_usada)} ${escapeHtml(i.unidade_medida)} ${escapeHtml(i.item_nome)}
                            ${i.unidade_calculo && i.unidade_calculo !== i.unidade_medida ? `<small class="text-muted d-block">Calculado como ${escapeHtml(i.quantidade_calculo)} ${escapeHtml(i.unidade_calculo)}</small>` : ''}
                        </span>
                        <strong>${Number(i.preco_unitario || 0) > 0 ? `R$ ${formatarMoeda(Number(i.custo_item || 0))}` : 'Falta preco'}</strong>
                    </div>
                </li>
            `).join('')}</ul>`
        : '<p class="text-muted mb-4">Sem ingredientes cadastrados.</p>';

    const preparo = receita.modo_preparo && receita.modo_preparo.length
        ? `<ol class="mb-0">${receita.modo_preparo.map(passo => `<li class="mb-2">${escapeHtml(passo)}</li>`).join('')}</ol>`
        : '<p class="text-muted mb-0">Modo de preparo nao cadastrado para esta receita.</p>';

    const descricao = receita.descricao
        ? `<p class="text-muted">${escapeHtml(receita.descricao)}</p>`
        : '';
    const faltantes = receita.ingredientes_sem_preco || [];
    const precoSugeridoDetalhe = Number(receita.preco_sugerido_calculado || 0);
    const precoSugeridoDetalheTexto = receita.custo_status === 'completo' && precoSugeridoDetalhe > 0
        ? `R$ ${formatarMoeda(precoSugeridoDetalhe)}`
        : 'Pendente';
    const faltantesHtml = faltantes.length
        ? `<div class="alert alert-warning small mt-3 mb-0">
                Faltam preços no estoque: ${faltantes.map(escapeHtml).join(', ')}.
           </div>`
        : '';

    document.getElementById('detalheReceitaTitulo').textContent = receita.nome_receita || 'Receita';
    document.getElementById('detalheReceitaConteudo').innerHTML = `
        ${descricao}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="small text-muted">Rendimento</div>
                    <strong>${escapeHtml(receita.rendimento_porcoes)} porcoes</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="small text-muted">Preco sugerido</div>
                    <strong>${precoSugeridoDetalheTexto}</strong>
                </div>
            </div>
        </div>
        ${faltantesHtml}
        <div class="receita-logo-teste">Ficha Técnica</div>
        <h6 class="fw-bold pink-shock">Ingredientes</h6>
        ${ingredientes}
        <h6 class="fw-bold pink-shock">Modo de preparo</h6>
        ${preparo}
    `;

    document.getElementById('detalheReceitaRodape').innerHTML = receita.origem === 'publica'
        ? '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>'
        : `<a href="editar_receita.php?id=${receita.id}" class="btn btn-warning">Editar ficha</a>
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>`;

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalheReceita')).show();
}

function filtrarReceitas(termo) {
    if (!termo.trim()) {
        renderizarReceitas(todasAsReceitas);
        return;
    }

    const termoLower = termo.toLowerCase();
    const filtradas = todasAsReceitas.filter(r => 
        r.nome_receita.toLowerCase().includes(termoLower) ||
        (r.descricao && r.descricao.toLowerCase().includes(termoLower))
    );

    renderizarReceitas(filtradas);
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return String(unsafe).replace(/[&<>"'`=\/]/g, function (s) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        })[s];
    });
}

// Busca catálogo público (fallback)
function formatarMoeda(valor) {
    return Number(valor || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

async function buscarReceitasPublicas() {
    const publicUrl = 'api_receitas_publicas.php?acao=listar';
    try {
        const resp = await fetch(publicUrl, {cache: 'no-store'});
        return await resp.json();
    } catch (err) {
        return { sucesso: false, mensagem: err.message, dados: [] };
    }
}
</script>
    <script src="assets/pwa.js"></script>
</body>
</html>
