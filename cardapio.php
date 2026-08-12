<?php
require_once 'config.php';

$cardapioUserId = intval($_GET['user_id'] ?? ($_SESSION['user_id'] ?? 1));
if ($cardapioUserId <= 0) {
    $cardapioUserId = 1;
}

$temWhatsappUsuario = doce_coluna_existe($pdo, 'users', 'whatsapp');
$temLogoUsuario = doce_coluna_existe($pdo, 'users', 'logo_marca');
$campoWhatsappUsuario = $temWhatsappUsuario ? 'whatsapp' : "'' AS whatsapp";
$campoLogoUsuario = $temLogoUsuario ? 'logo_marca' : "'' AS logo_marca";
$camposUsuario = "id, nome, $campoWhatsappUsuario, $campoLogoUsuario";
$stmtUsuario = $pdo->prepare("SELECT $camposUsuario FROM users WHERE id = ? LIMIT 1");
$stmtUsuario->execute([$cardapioUserId]);
$usuarioCardapio = $stmtUsuario->fetch();

if (!$usuarioCardapio) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cardapio nao encontrado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <main class="container min-vh-100 d-flex align-items-center justify-content-center">
            <div class="card shadow-sm" style="max-width: 560px;">
                <div class="card-body p-4 text-center">
                    <h1 class="h4 fw-bold">Cardapio nao encontrado</h1>
                    <p class="text-muted mb-0">Confira se o link esta correto ou solicite um novo link para a confeitaria.</p>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$user_id = intval($usuarioCardapio['id']);
$nomeMarca = trim((string)($usuarioCardapio['nome'] ?? '')) ?: 'Doce Controle';
$whatsapp = trim((string)($usuarioCardapio['whatsapp'] ?? ''));
$logoMarca = trim((string)($usuarioCardapio['logo_marca'] ?? ''));
$imagemMarca = $logoMarca !== '' && is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logoMarca))
    ? $logoMarca
    : 'assets/delicias-da-mara-logo.jpg';
$temImagemReceita = doce_coluna_existe($pdo, 'receitas', 'imagem_produto');
$temMostrarCardapio = doce_coluna_existe($pdo, 'receitas', 'mostrar_cardapio');
$temDescricaoPublica = doce_coluna_existe($pdo, 'receitas', 'descricao_publica');
$campoImagem = $temImagemReceita ? 'r.imagem_produto,' : "'' AS imagem_produto,";
$campoDescricao = $temDescricaoPublica ? 'r.descricao_publica,' : "'' AS descricao_publica,";
$filtroCardapio = '';

$stmt = $pdo->prepare(
    "SELECT r.id, r.nome_receita, r.rendimento_porcoes, r.preco_venda_sugerido,
            $campoImagem
            $campoDescricao
            IFNULL(SUM(i.quantidade_usada * e.preco_unitario), 0) AS custo_total,
            COUNT(i.id) AS total_itens
     FROM receitas r
     LEFT JOIN receitas_itens i ON i.receita_id = r.id
     LEFT JOIN estoque e ON e.id = i.insumo_id
     WHERE r.user_id = ?
     $filtroCardapio
     GROUP BY r.id
     ORDER BY r.nome_receita ASC"
);
$stmt->execute([$user_id]);
$produtos = $stmt->fetchAll();

function cardapio_preco_produto($produto)
{
    $precoVenda = floatval($produto['preco_venda_sugerido'] ?? 0);
    if ($precoVenda > 0) {
        return $precoVenda;
    }

    return floatval($produto['custo_total'] ?? 0) * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO;
}

function cardapio_link_whatsapp($telefone, $produto)
{
    $mensagem = "Ola! Vim pelo cardapio e quero pedir: " . $produto;
    $numero = preg_replace('/\D+/', '', $telefone);

    if ($numero !== '') {
        if (strpos($numero, '55') !== 0) {
            $numero = '55' . $numero;
        }

        return 'https://wa.me/' . $numero . '?text=' . rawurlencode($mensagem);
    }

    return 'https://api.whatsapp.com/send?text=' . rawurlencode($mensagem);
}

function cardapio_imagem_produto($produto)
{
    global $imagemMarca;

    $imagem = trim((string)($produto['imagem_produto'] ?? ''));
    if ($imagem !== '' && is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagem))) {
        return $imagem;
    }

    return $imagemMarca;
}

$pedidoResumo = null;
$resultadoPedidoGet = (string)($_GET['pedido'] ?? '');
$codigoPedidoGet = trim((string)($_GET['codigo'] ?? ''));
if ($resultadoPedidoGet === 'sucesso' && $codigoPedidoGet !== '' && doce_coluna_existe($pdo, 'pedidos', 'codigo_pedido')) {
    $stmtResumo = $pdo->prepare(
        "SELECT p.quantidade, p.valor_total, p.data_entrega, p.nome_recebedor, p.endereco_entrega, r.nome_receita
         FROM pedidos p
         INNER JOIN receitas r ON r.id = p.receita_id
         WHERE p.codigo_pedido = ? AND p.user_id = ?
         ORDER BY p.id ASC"
    );
    $stmtResumo->execute([$codigoPedidoGet, $user_id]);
    $itensPedidoResumo = $stmtResumo->fetchAll();

    if ($itensPedidoResumo) {
        $totalPedidoResumo = 0;
        foreach ($itensPedidoResumo as $itemPedidoResumo) {
            $totalPedidoResumo += floatval($itemPedidoResumo['valor_total']);
        }

        $pedidoResumo = [
            'codigo' => $codigoPedidoGet,
            'itens' => $itensPedidoResumo,
            'total' => $totalPedidoResumo,
            'data_entrega' => $itensPedidoResumo[0]['data_entrega'],
            'nome_recebedor' => $itensPedidoResumo[0]['nome_recebedor'],
            'endereco_entrega' => $itensPedidoResumo[0]['endereco_entrega'],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#A04255">
    <title><?= htmlspecialchars($nomeMarca) ?> - Cardapio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --rosa: #A04255;
            --rosa-escuro: #7E3040;
            --dourado: #D4AF37;
            --verde: #8A9A86;
            --texto: #2C2523;
            --texto-muted: #766B63;
            --fundo: #F3ECE1;
            --creme: #FAF7F2;
            --branco: #FFFFFF;
            --borda-suave: rgba(44, 37, 35, 0.10);
            --sombra-suave: 0 20px 50px rgba(44, 37, 35, 0.09);
            --sombra-card: 0 14px 32px rgba(44, 37, 35, 0.07);
        }

        body {
            min-height: 100vh;
            background: var(--creme);
            color: var(--texto);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        h1, h2, h3, .font-serif {
            font-family: 'Playfair Display', serif;
        }

        .navbar {
            background: rgba(250, 247, 242, 0.92);
            border-bottom: 1px solid var(--borda-suave);
            backdrop-filter: blur(12px);
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--dourado);
        }

        .navbar-brand span {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .hero {
            padding: 8.5rem 0 4rem;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 3rem;
            align-items: center;
        }

        .hero-eyebrow {
            color: var(--dourado);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .hero h1 {
            color: var(--texto);
            font-size: clamp(2.2rem, 4.2vw, 3.4rem);
            font-weight: 700;
            line-height: 1.15;
            max-width: 620px;
        }

        .hero p.lead-copy {
            max-width: 520px;
            font-size: 1.08rem;
            color: var(--texto-muted);
        }

        .hero-visual {
            position: relative;
        }

        .hero-visual-frame {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--sombra-suave);
            aspect-ratio: 4 / 5;
        }

        .hero-visual-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-badge-float {
            position: absolute;
            left: -1.25rem;
            bottom: -1.25rem;
            background: var(--branco);
            border-radius: 14px;
            box-shadow: var(--sombra-card);
            padding: 0.85rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            max-width: 230px;
        }

        .hero-badge-float .icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(212, 175, 55, 0.16);
            color: var(--dourado);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .hero-badge-float strong {
            display: block;
            font-size: 0.86rem;
            color: var(--texto);
        }

        .hero-badge-float span {
            display: block;
            font-size: 0.76rem;
            color: var(--texto-muted);
        }

        .btn-pink {
            background: var(--rosa);
            border-color: var(--rosa);
            color: #fff;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-weight: 600;
            border-radius: 999px;
            padding-inline: 1.5rem;
        }

        .btn-pink:hover {
            background: var(--rosa-escuro);
            border-color: var(--rosa-escuro);
            color: #fff;
        }

        .btn-outline-pink {
            border: 1.5px solid var(--rosa);
            color: var(--rosa);
            background: transparent;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-weight: 600;
            border-radius: 999px;
            padding-inline: 1.5rem;
        }

        .btn-outline-pink:hover {
            background: var(--rosa);
            color: #fff;
        }

        .section-title {
            color: var(--texto);
            font-weight: 700;
        }

        .trust-bar {
            background: var(--branco);
            border-top: 1px solid var(--borda-suave);
            border-bottom: 1px solid var(--borda-suave);
            padding: 1.35rem 0;
        }

        .trust-bar-inner {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2.25rem 2.75rem;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--texto);
        }

        .trust-item i {
            color: var(--dourado);
            font-size: 1.2rem;
        }

        .catalog-band {
            background: var(--creme);
            padding: 4.5rem 0;
        }

        .filter-bar {
            background: var(--branco);
            border: 1px solid var(--borda-suave);
            border-radius: 14px;
            padding: 1rem;
        }

        .form-control {
            min-height: 48px;
            border: 1.5px solid var(--borda-suave);
        }

        .form-control:focus {
            border-color: var(--rosa);
            box-shadow: 0 0 0 0.2rem rgba(160, 66, 85, 0.14);
        }

        .product-card {
            border: 1px solid var(--borda-suave);
            border-radius: 20px;
            background: var(--branco);
            overflow: hidden;
            height: 100%;
            box-shadow: var(--sombra-card);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--sombra-suave);
        }

        .product-media {
            aspect-ratio: 4 / 3;
            background: var(--fundo);
            overflow: hidden;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-category {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--verde);
            margin-bottom: 0.35rem;
        }

        .product-card h3 {
            font-weight: 700;
            font-size: 1.2rem;
        }

        .product-desc {
            font-size: 0.92rem;
            color: var(--texto-muted);
        }

        .price-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--rosa);
            font-weight: 700;
            font-size: 0.98rem;
            white-space: nowrap;
        }

        .success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(138, 154, 134, 0.2);
            color: #4C5F49;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .empty-state {
            border: 1px dashed rgba(160, 66, 85, 0.3);
            border-radius: 16px;
            background: var(--branco);
        }

        .floating-whatsapp {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 10;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #25d366;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--sombra-card);
            text-decoration: none;
            font-size: 1.55rem;
            opacity: 0;
            transform: scale(0.85);
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .floating-whatsapp:hover {
            color: #fff;
            transform: translateY(-2px);
        }

        .floating-cart {
            position: fixed;
            right: 1rem;
            bottom: 4.6rem;
            z-index: 10;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--rosa);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--sombra-card);
            border: none;
            font-size: 1.4rem;
            opacity: 0;
            transform: scale(0.85);
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .floating-cart.is-visible,
        .floating-whatsapp.is-visible {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
        }

        .floating-cart .cart-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--dourado);
            color: var(--texto);
            font-size: 0.72rem;
            font-weight: 800;
            min-width: 22px;
            height: 22px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        .cart-item-row {
            border-bottom: 1px solid var(--borda-suave);
            padding: 0.75rem 0;
        }

        .qty-control {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .qty-control input {
            width: 54px;
            text-align: center;
        }

        .success-card {
            border: 1px solid rgba(138, 154, 134, 0.4);
            border-radius: 16px;
            background: rgba(138, 154, 134, 0.09);
        }

        @media (max-width: 991px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-visual {
                order: -1;
                max-width: 360px;
                margin: 0 auto 2rem;
            }

            .hero p.lead-copy {
                margin-inline: auto;
            }

            .hero-badge-float {
                left: 0.75rem;
                bottom: -1rem;
            }
        }

        @media (max-width: 767px) {
            .hero {
                padding: 6.5rem 0 2.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar fixed-top">
        <div class="container d-flex justify-content-between align-items-center gap-3">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-decoration-none" href="#topo">
                <img src="<?= htmlspecialchars($imagemMarca) ?>" class="brand-logo" alt="Logo <?= htmlspecialchars($nomeMarca) ?>">
                <span class="text-dark"><?= htmlspecialchars($nomeMarca) ?></span>
            </a>
            <a href="#cardapio" class="btn btn-outline-pink btn-sm">
                <i class="bi bi-grid-3x3-gap"></i> Ver doces
            </a>
        </div>
    </nav>

    <header id="topo" class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-copy">
                    <p class="hero-eyebrow mb-3">Confeitaria Artesanal &amp; Fine Pastry</p>
                    <h1 class="mb-3">Momentos inesqueciveis pedem doces inesqueciveis.</h1>
                    <p class="lead-copy mb-4">
                        Bolos esculpidos, doces finos e sobremesas feitas a mao com ingredientes selecionados, pensados para o seu momento especial.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-lg-start">
                        <a href="#cardapio" class="btn btn-pink btn-lg">
                            <i class="bi bi-bag-heart"></i> Explorar o Cardapio
                        </a>
                        <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, 'uma encomenda personalizada')) ?>" target="_blank" rel="noopener" class="btn btn-outline-pink btn-lg">
                            <i class="bi bi-whatsapp"></i> Encomenda Personalizada
                        </a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-visual-frame">
                        <img src="<?= htmlspecialchars($imagemMarca) ?>" alt="<?= htmlspecialchars($nomeMarca) ?>" loading="eager">
                    </div>
                    <div class="hero-badge-float">
                        <span class="icon-circle"><i class="bi bi-award"></i></span>
                        <div>
                            <strong>Feito a mao</strong>
                            <span>com ingredientes selecionados</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="trust-bar">
        <div class="container">
            <div class="trust-bar-inner">
                <div class="trust-item"><i class="bi bi-flower1"></i> Ingredientes Selecionados</div>
                <div class="trust-item"><i class="bi bi-palette2"></i> Producao 100% Artesanal</div>
                <div class="trust-item"><i class="bi bi-box-seam"></i> Entrega Pontual e Segura</div>
            </div>
        </div>
    </section>

    <main id="cardapio" class="catalog-band">
        <div class="container">
            <?php
                $resultadoPedido = (string)($_GET['pedido'] ?? '');
                $mensagensPedido = [
                    'dados' => ['warning', 'Confira seus dados e tente enviar o pedido novamente.'],
                    'data' => ['warning', 'Escolha uma data e horario de entrega futuros.'],
                    'produto' => ['warning', 'Um dos produtos do carrinho nao esta mais disponivel no cardapio.'],
                    'aguarde' => ['warning', 'Aguarde alguns segundos antes de enviar outro pedido.'],
                    'erro' => ['danger', 'Nao foi possivel enviar o pedido agora. Tente novamente.'],
                ];
            ?>
            <?php if ($resultadoPedido === 'sucesso' && $pedidoResumo): ?>
                <div class="success-card p-4 p-md-5 mb-4" id="resumoPedidoSucesso">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="success-icon"><i class="bi bi-check-circle-fill"></i></span>
                        <div>
                            <h2 class="h4 fw-bold mb-1" style="color:#4C5F49;">Pedido realizado com sucesso!</h2>
                            <p class="text-muted mb-0">A confeitaria recebeu sua encomenda <strong>#<?= htmlspecialchars($pedidoResumo['codigo']) ?></strong>. Confira o resumo abaixo.</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <strong class="d-block mb-2">Itens do pedido</strong>
                            <?php foreach ($pedidoResumo['itens'] as $itemResumo): ?>
                                <div class="d-flex justify-content-between border-bottom py-1">
                                    <span><?= intval($itemResumo['quantidade']) ?>x <?= htmlspecialchars($itemResumo['nome_receita']) ?></span>
                                    <strong>R$ <?= number_format($itemResumo['valor_total'], 2, ',', '.') ?></strong>
                                </div>
                            <?php endforeach; ?>
                            <div class="d-flex justify-content-between pt-2">
                                <strong>Total</strong>
                                <strong>R$ <?= number_format($pedidoResumo['total'], 2, ',', '.') ?></strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-5">
                            <strong class="d-block mb-2">Entrega</strong>
                            <p class="mb-1 small"><i class="bi bi-calendar-event"></i> <?= date('d/m/Y \à\s H:i', strtotime($pedidoResumo['data_entrega'])) ?></p>
                            <p class="mb-1 small"><i class="bi bi-person"></i> <?= htmlspecialchars($pedidoResumo['nome_recebedor'] ?? '') ?></p>
                            <p class="mb-0 small"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($pedidoResumo['endereco_entrega'] ?? '') ?></p>
                        </div>
                    </div>
                    <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, 'confirmar os detalhes do pedido #' . $pedidoResumo['codigo'])) ?>" target="_blank" rel="noopener" class="btn btn-outline-pink">
                        <i class="bi bi-whatsapp"></i> Acompanhar pelo WhatsApp
                    </a>
                </div>
            <?php elseif (isset($mensagensPedido[$resultadoPedido])): ?>
                <div class="alert alert-<?= $mensagensPedido[$resultadoPedido][0] ?> shadow-sm mb-4" role="alert">
                    <?= htmlspecialchars($mensagensPedido[$resultadoPedido][1]) ?>
                </div>
            <?php endif; ?>
            <div class="row align-items-end g-3 mb-4">
                <div class="col-12 col-lg-7">
                    <h2 class="section-title mb-2">Cardapio de doces</h2>
                    <p class="text-muted mb-0">Escolha um produto e faça sua encomenda sem precisar criar senha.</p>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="filter-bar">
                        <label for="buscaProduto" class="form-label fw-bold mb-1">Buscar produto</label>
                        <input type="search" id="buscaProduto" class="form-control" placeholder="Ex: bolo, brigadeiro, brownie">
                    </div>
                </div>
            </div>

            <?php if (count($produtos) === 0): ?>
                <div class="empty-state p-4 p-md-5 text-center">
                    <h3 class="h5 fw-bold mb-2">Cardapio em preparacao</h3>
                    <p class="text-muted mb-0">Em breve os doces disponiveis aparecerao por aqui.</p>
                </div>
            <?php else: ?>
                <div class="row g-4" id="listaProdutos">
                    <?php foreach ($produtos as $produto): ?>
                        <?php
                            $preco = cardapio_preco_produto($produto);
                            $nomeProduto = $produto['nome_receita'];
                            $mensagemProduto = $nomeProduto . ' - R$ ' . number_format($preco, 2, ',', '.');
                            $imagemProduto = cardapio_imagem_produto($produto);
                            $descricaoProduto = trim((string)($produto['descricao_publica'] ?? ''));
                        ?>
                        <div class="col-12 col-md-6 col-xl-4 produto-item" data-nome="<?= htmlspecialchars(strtolower($nomeProduto)) ?>">
                            <article class="product-card d-flex flex-column">
                                <div class="product-media">
                                    <img src="<?= htmlspecialchars($imagemProduto) ?>" alt="<?= htmlspecialchars($nomeProduto) ?>" loading="lazy">
                                </div>
                                <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                                    <p class="product-category"><i class="bi bi-stars"></i> Doce Artesanal</p>
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <h3 class="mb-0 font-serif"><?= htmlspecialchars($nomeProduto) ?></h3>
                                        <span class="price-pill">R$ <?= number_format($preco, 2, ',', '.') ?></span>
                                    </div>
                                    <?php if ($descricaoProduto !== ''): ?>
                                        <p class="product-desc mb-3"><?= nl2br(htmlspecialchars($descricaoProduto)) ?></p>
                                    <?php else: ?>
                                        <p class="product-desc mb-3">
                                            Produto artesanal com rendimento aproximado de <?= intval($produto['rendimento_porcoes']) ?> porcoes.
                                        </p>
                                    <?php endif; ?>
                                    <div class="d-flex gap-2 mt-auto">
                                        <input type="number" class="form-control" style="max-width: 78px;" min="1" max="100" value="1" id="qtd-<?= intval($produto['id']) ?>" aria-label="Quantidade">
                                        <button
                                            type="button"
                                            class="btn btn-pink flex-grow-1 js-adicionar-carrinho"
                                            data-receita-id="<?= intval($produto['id']) ?>"
                                            data-produto="<?= htmlspecialchars($nomeProduto) ?>"
                                            data-preco="<?= htmlspecialchars(number_format($preco, 2, ',', '.')) ?>"
                                        >
                                            <i class="bi bi-bag-plus"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="semResultados" class="empty-state p-4 text-center d-none mt-4">
                    <h3 class="h5 fw-bold mb-2">Nenhum doce encontrado</h3>
                    <p class="text-muted mb-0">Tente buscar por outro nome ou chame no WhatsApp para uma encomenda personalizada.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCarrinho" aria-labelledby="tituloCarrinho">
        <div class="offcanvas-header border-bottom">
            <h2 class="offcanvas-title h5 fw-bold" id="tituloCarrinho"><i class="bi bi-bag-heart"></i> Meu carrinho</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div id="carrinhoLista" class="flex-grow-1"></div>
            <div id="carrinhoVazio" class="empty-state p-4 text-center d-none">
                <p class="text-muted mb-0">Seu carrinho esta vazio. Escolha um doce no cardapio.</p>
            </div>
            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between fw-bold h5 mb-3">
                    <span>Total</span>
                    <span id="carrinhoTotal">R$ 0,00</span>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-pink btn-lg" id="btnFinalizarSite" data-bs-toggle="modal" data-bs-target="#modalCheckout">
                        <i class="bi bi-bag-check"></i> Finalizar pedido no site
                    </button>
                    <a href="#" target="_blank" rel="noopener" class="btn btn-outline-pink btn-lg" id="btnFinalizarWhatsapp" data-numero="<?= htmlspecialchars(preg_replace('/\D+/', '', $whatsapp)) ?>">
                        <i class="bi bi-whatsapp"></i> Pedir pelo WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCheckout" tabindex="-1" aria-labelledby="tituloCheckout" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="salvar_pedido_publico.php" method="POST" id="formCheckout">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold mb-1" id="tituloCheckout">Finalizar pedido</h2>
                            <div class="text-muted small" id="resumoCarrinhoCheckout"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(doce_csrf_token()) ?>">
                        <input type="hidden" name="user_id" value="<?= intval($user_id) ?>">
                        <input type="hidden" name="itens_json" id="checkoutItensJson" value="">
                        <div class="position-absolute opacity-0" style="pointer-events:none;" aria-hidden="true">
                            <label for="pedidoWebsite">Website</label>
                            <input type="text" name="website" id="pedidoWebsite" tabindex="-1" autocomplete="off">
                        </div>

                        <h3 class="h6 fw-bold text-uppercase" style="color: var(--rosa);">Dados de identificacao</h3>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-7">
                                <label for="pedidoNome" class="form-label fw-bold">Nome completo</label>
                                <input type="text" class="form-control" name="nome" id="pedidoNome" maxlength="160" autocomplete="name" required>
                            </div>
                            <div class="col-12 col-md-5">
                                <label for="pedidoWhatsapp" class="form-label fw-bold">WhatsApp</label>
                                <input type="tel" class="form-control" name="whatsapp" id="pedidoWhatsapp" maxlength="20" placeholder="(00) 00000-0000" autocomplete="tel" required>
                            </div>
                            <div class="col-12">
                                <label for="pedidoEmail" class="form-label fw-bold">E-mail <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="email" class="form-control" name="email" id="pedidoEmail" maxlength="160" autocomplete="email">
                            </div>
                        </div>

                        <h3 class="h6 fw-bold text-uppercase" style="color: var(--rosa);">Dados de entrega</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="pedidoEndereco" class="form-label fw-bold">Endereco de entrega completo</label>
                                <input type="text" class="form-control" name="endereco_entrega" id="pedidoEndereco" maxlength="255" placeholder="Rua, numero, bairro, cidade" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="pedidoRecebedor" class="form-label fw-bold">Nome de quem vai receber</label>
                                <input type="text" class="form-control" name="nome_recebedor" id="pedidoRecebedor" maxlength="160" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="pedidoDataEntrega" class="form-label fw-bold">Data de entrega</label>
                                <input type="date" class="form-control" name="data_entrega" id="pedidoDataEntrega" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="pedidoHorarioEntrega" class="form-label fw-bold">Horario <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="time" class="form-control" name="horario_entrega" id="pedidoHorarioEntrega">
                            </div>
                            <div class="col-12">
                                <label for="pedidoObservacoes" class="form-label fw-bold">Observacoes <span class="text-muted fw-normal">(opcional)</span></label>
                                <textarea class="form-control" name="observacoes" id="pedidoObservacoes" rows="3" maxlength="1000" placeholder="Tema, cor, sabor ou outro detalhe importante"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-pink">
                            <i class="bi bi-send-check"></i> Enviar pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="py-4" style="background: var(--fundo);">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <strong><?= htmlspecialchars($nomeMarca) ?></strong>
                <div class="text-muted small">Doces artesanais feitos com carinho.</div>
            </div>
            <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, 'uma encomenda')) ?>" target="_blank" rel="noopener" class="btn btn-outline-pink">
                <i class="bi bi-whatsapp"></i> Falar no WhatsApp
            </a>
        </div>
    </footer>

    <button type="button" class="floating-cart" id="btnAbrirCarrinho" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCarrinho" aria-controls="offcanvasCarrinho" aria-label="Abrir carrinho">
        <i class="bi bi-bag-heart"></i>
        <span class="cart-badge d-none" id="carrinhoBadge">0</span>
    </button>

    <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, 'uma encomenda')) ?>" target="_blank" rel="noopener" class="floating-whatsapp" aria-label="Falar no WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const botoesFluantes = document.querySelectorAll('.floating-cart, .floating-whatsapp');
        function atualizarBotoesFluantes() {
            const mostrar = window.scrollY > 220;
            botoesFluantes.forEach(el => el.classList.toggle('is-visible', mostrar));
        }
        window.addEventListener('scroll', atualizarBotoesFluantes, { passive: true });
        atualizarBotoesFluantes();

        const buscaProduto = document.getElementById('buscaProduto');
        const produtos = Array.from(document.querySelectorAll('.produto-item'));
        const semResultados = document.getElementById('semResultados');

        if (buscaProduto) {
            buscaProduto.addEventListener('input', () => {
                const termo = buscaProduto.value.trim().toLowerCase();
                let visiveis = 0;

                produtos.forEach(produto => {
                    const encontrou = produto.dataset.nome.includes(termo);
                    produto.classList.toggle('d-none', !encontrou);
                    if (encontrou) {
                        visiveis++;
                    }
                });

                if (semResultados) {
                    semResultados.classList.toggle('d-none', visiveis > 0);
                }
            });
        }

        const CARRINHO_CHAVE = 'doce_carrinho_<?= intval($user_id) ?>';
        const pedidoFoiSucesso = <?= $pedidoResumo ? 'true' : 'false' ?>;

        function escapeHtml(texto) {
            const div = document.createElement('div');
            div.textContent = texto;
            return div.innerHTML;
        }

        function parsePrecoBr(precoStr) {
            return parseFloat(String(precoStr).replace(/\./g, '').replace(',', '.')) || 0;
        }

        function formatarMoeda(valor) {
            return 'R$ ' + valor.toFixed(2).replace('.', ',');
        }

        function carregarCarrinho() {
            try {
                const dados = JSON.parse(localStorage.getItem(CARRINHO_CHAVE) || '[]');
                return Array.isArray(dados) ? dados : [];
            } catch (e) {
                return [];
            }
        }

        function salvarCarrinho(itens) {
            localStorage.setItem(CARRINHO_CHAVE, JSON.stringify(itens));
        }

        let carrinho = pedidoFoiSucesso ? [] : carregarCarrinho();
        if (pedidoFoiSucesso) {
            salvarCarrinho([]);
        }

        function calcularTotalCarrinho() {
            return carrinho.reduce((total, item) => total + (item.preco * item.quantidade), 0);
        }

        function renderizarCarrinho() {
            const lista = document.getElementById('carrinhoLista');
            const vazio = document.getElementById('carrinhoVazio');
            const totalEl = document.getElementById('carrinhoTotal');
            const badge = document.getElementById('carrinhoBadge');
            const btnSite = document.getElementById('btnFinalizarSite');
            const btnWhats = document.getElementById('btnFinalizarWhatsapp');

            if (!lista) {
                return;
            }

            lista.innerHTML = '';

            if (carrinho.length === 0) {
                vazio.classList.remove('d-none');
                if (btnSite) btnSite.disabled = true;
                if (btnWhats) btnWhats.classList.add('disabled');
            } else {
                vazio.classList.add('d-none');
                if (btnSite) btnSite.disabled = false;
                if (btnWhats) btnWhats.classList.remove('disabled');

                carrinho.forEach(item => {
                    const linha = document.createElement('div');
                    linha.className = 'cart-item-row d-flex justify-content-between align-items-center';
                    linha.dataset.cartId = item.id;
                    linha.innerHTML = `
                        <div class="flex-grow-1 me-2">
                            <div class="fw-bold">${escapeHtml(item.nome)}</div>
                            <div class="text-muted small">${formatarMoeda(item.preco)} cada</div>
                        </div>
                        <div class="qty-control">
                            <button type="button" class="btn btn-sm btn-outline-secondary js-cart-dec" data-id="${item.id}">-</button>
                            <input type="number" min="1" max="100" value="${item.quantidade}" class="form-control form-control-sm js-cart-qty" data-id="${item.id}">
                            <button type="button" class="btn btn-sm btn-outline-secondary js-cart-inc" data-id="${item.id}">+</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger js-cart-remove" data-id="${item.id}" aria-label="Remover"><i class="bi bi-trash"></i></button>
                    `;
                    lista.appendChild(linha);
                });
            }

            const total = calcularTotalCarrinho();
            const totalItens = carrinho.reduce((soma, item) => soma + item.quantidade, 0);

            totalEl.textContent = formatarMoeda(total);

            if (badge) {
                badge.textContent = totalItens;
                badge.classList.toggle('d-none', totalItens === 0);
            }

            const resumoCheckout = document.getElementById('resumoCarrinhoCheckout');
            if (resumoCheckout) {
                resumoCheckout.textContent = totalItens > 0
                    ? `${totalItens} item(ns) - ${formatarMoeda(total)}`
                    : '';
            }

            if (btnWhats) {
                const numero = btnWhats.dataset.numero || '';
                const resumoTexto = carrinho.map(item => `${item.quantidade}x ${item.nome}`).join(', ');
                const mensagem = 'Ola! Vim pelo cardapio e quero pedir: ' + resumoTexto
                    + (resumoTexto ? ` (Total: ${formatarMoeda(total)})` : '');
                const base = numero
                    ? `https://wa.me/${numero.indexOf('55') === 0 ? numero : '55' + numero}`
                    : 'https://api.whatsapp.com/send';
                btnWhats.href = base + '?text=' + encodeURIComponent(mensagem);
            }
        }

        function adicionarAoCarrinho(id, nome, preco, quantidade) {
            const existente = carrinho.find(item => item.id === id);
            if (existente) {
                existente.quantidade = Math.min(100, existente.quantidade + quantidade);
            } else {
                carrinho.push({ id, nome, preco, quantidade: Math.max(1, Math.min(100, quantidade)) });
            }
            salvarCarrinho(carrinho);
            renderizarCarrinho();
        }

        function removerDoCarrinho(id) {
            carrinho = carrinho.filter(item => item.id !== id);
            salvarCarrinho(carrinho);
            renderizarCarrinho();
        }

        function alterarQuantidade(id, quantidade) {
            const item = carrinho.find(i => i.id === id);
            if (!item) return;
            item.quantidade = Math.max(1, Math.min(100, quantidade));
            salvarCarrinho(carrinho);
            renderizarCarrinho();
        }

        document.querySelectorAll('.js-adicionar-carrinho').forEach(button => {
            button.addEventListener('click', () => {
                const id = parseInt(button.dataset.receitaId, 10);
                const nome = button.dataset.produto;
                const preco = parsePrecoBr(button.dataset.preco);
                const inputQtd = document.getElementById('qtd-' + id);
                const quantidade = inputQtd ? Math.max(1, Math.min(100, parseInt(inputQtd.value, 10) || 1)) : 1;

                adicionarAoCarrinho(id, nome, preco, quantidade);

                if (inputQtd) {
                    inputQtd.value = 1;
                }
            });
        });

        const carrinhoLista = document.getElementById('carrinhoLista');
        if (carrinhoLista) {
            carrinhoLista.addEventListener('click', (evento) => {
                const decBtn = evento.target.closest('.js-cart-dec');
                const incBtn = evento.target.closest('.js-cart-inc');
                const removeBtn = evento.target.closest('.js-cart-remove');

                if (decBtn) {
                    const id = parseInt(decBtn.dataset.id, 10);
                    const item = carrinho.find(i => i.id === id);
                    if (item) alterarQuantidade(id, item.quantidade - 1);
                } else if (incBtn) {
                    const id = parseInt(incBtn.dataset.id, 10);
                    const item = carrinho.find(i => i.id === id);
                    if (item) alterarQuantidade(id, item.quantidade + 1);
                } else if (removeBtn) {
                    removerDoCarrinho(parseInt(removeBtn.dataset.id, 10));
                }
            });

            carrinhoLista.addEventListener('change', (evento) => {
                if (evento.target.classList.contains('js-cart-qty')) {
                    const id = parseInt(evento.target.dataset.id, 10);
                    alterarQuantidade(id, parseInt(evento.target.value, 10) || 1);
                }
            });
        }

        const formCheckout = document.getElementById('formCheckout');
        if (formCheckout) {
            formCheckout.addEventListener('submit', (evento) => {
                if (carrinho.length === 0) {
                    evento.preventDefault();
                    return;
                }
                const itensJson = carrinho.map(item => ({ receita_id: item.id, quantidade: item.quantidade }));
                document.getElementById('checkoutItensJson').value = JSON.stringify(itensJson);
            });
        }

        renderizarCarrinho();
    </script>
</body>
</html>
