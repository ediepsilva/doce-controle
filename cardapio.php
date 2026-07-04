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
$filtroCardapio = $temMostrarCardapio ? 'AND r.mostrar_cardapio = 1' : '';

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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff007f">
    <title><?= htmlspecialchars($nomeMarca) ?> - Cardapio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --rosa: #ff007f;
            --rosa-escuro: #bd005e;
            --amarelo: #ffd166;
            --verde: #22a06b;
            --texto: #24151d;
            --fundo: #fff7fb;
        }

        body {
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(255, 0, 127, 0.11), rgba(255, 209, 102, 0.22)),
                #ffffff;
            color: var(--texto);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid rgba(255, 0, 127, 0.16);
            backdrop-filter: blur(12px);
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--rosa);
        }

        .hero {
            min-height: 88vh;
            display: grid;
            align-items: center;
            padding: 6rem 0 3rem;
            background:
                linear-gradient(90deg, rgba(255, 247, 251, 0.96), rgba(255, 247, 251, 0.68), rgba(255, 247, 251, 0.2)),
                url("<?= htmlspecialchars($imagemMarca) ?>");
            background-repeat: no-repeat;
            background-position: right 8% center;
            background-size: min(48vw, 520px) auto;
        }

        .hero h1 {
            color: var(--rosa);
            font-size: clamp(2.5rem, 7vw, 5.4rem);
            font-weight: 900;
            line-height: 0.95;
            letter-spacing: 0;
            max-width: 760px;
        }

        .hero p {
            max-width: 600px;
            font-size: 1.12rem;
        }

        .btn-pink {
            background: var(--rosa);
            border-color: var(--rosa);
            color: #fff;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 700;
        }

        .btn-pink:hover {
            background: var(--rosa-escuro);
            border-color: var(--rosa-escuro);
            color: #fff;
        }

        .btn-outline-pink {
            border-color: var(--rosa);
            color: var(--rosa);
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 700;
        }

        .btn-outline-pink:hover {
            background: var(--rosa);
            color: #fff;
        }

        .section-title {
            color: var(--rosa);
            font-weight: 900;
        }

        .catalog-band {
            background: #fff;
            padding: 4rem 0;
        }

        .filter-bar {
            background: var(--fundo);
            border: 1px solid rgba(255, 0, 127, 0.16);
            border-radius: 8px;
            padding: 1rem;
        }

        .form-control {
            min-height: 48px;
            border: 2px solid rgba(255, 0, 127, 0.32);
        }

        .form-control:focus {
            border-color: var(--rosa);
            box-shadow: 0 0 0 0.25rem rgba(255, 0, 127, 0.16);
        }

        .product-card {
            border: 1px solid rgba(255, 0, 127, 0.16);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 12px 30px rgba(36, 21, 29, 0.08);
        }

        .product-media {
            min-height: 170px;
            background:
                linear-gradient(135deg, rgba(255, 0, 127, 0.1), rgba(255, 209, 102, 0.32)),
                url("<?= htmlspecialchars($imagemMarca) ?>");
            background-position: center;
            background-size: contain;
            background-repeat: no-repeat;
            border-bottom: 1px solid rgba(255, 0, 127, 0.12);
        }

        .price-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(34, 160, 107, 0.12);
            color: #116442;
            padding: 0.35rem 0.7rem;
            font-weight: 800;
        }

        .empty-state {
            border: 1px dashed rgba(255, 0, 127, 0.4);
            border-radius: 8px;
            background: var(--fundo);
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
            box-shadow: 0 12px 28px rgba(37, 211, 102, 0.34);
            text-decoration: none;
            font-size: 1.55rem;
        }

        .floating-whatsapp:hover {
            color: #fff;
            transform: translateY(-2px);
        }

        @media (max-width: 767px) {
            .hero {
                min-height: auto;
                padding: 5.5rem 0 2.5rem;
                background:
                    linear-gradient(rgba(255, 247, 251, 0.92), rgba(255, 247, 251, 0.92)),
                    url("<?= htmlspecialchars($imagemMarca) ?>");
                background-repeat: no-repeat;
                background-position: center 5.2rem;
                background-size: 72vw auto;
            }

            .hero-copy {
                padding-top: 48vw;
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
            <div class="hero-copy">
                <p class="fw-bold text-uppercase mb-2" style="color: var(--verde);">Doces artesanais sob encomenda</p>
                <h1><?= htmlspecialchars($nomeMarca) ?></h1>
                <p class="text-muted mt-3 mb-4">
                    Bolos, docinhos e sobremesas feitos para aniversarios, eventos e aquele momento em que um doce bonito muda o dia.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <a href="#cardapio" class="btn btn-pink btn-lg">
                        <i class="bi bi-bag-heart"></i> Escolher um doce
                    </a>
                    <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, 'uma encomenda personalizada')) ?>" target="_blank" rel="noopener" class="btn btn-outline-pink btn-lg">
                        <i class="bi bi-whatsapp"></i> Pedir pelo WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main id="cardapio" class="catalog-band">
        <div class="container">
            <div class="row align-items-end g-3 mb-4">
                <div class="col-12 col-lg-7">
                    <h2 class="section-title mb-2">Cardapio de doces</h2>
                    <p class="text-muted mb-0">Escolha um produto e envie o pedido direto pelo WhatsApp.</p>
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
                                <div class="product-media" role="img" aria-label="<?= htmlspecialchars($nomeProduto) ?>" style="background-image: linear-gradient(135deg, rgba(255, 0, 127, 0.08), rgba(255, 209, 102, 0.18)), url('<?= htmlspecialchars($imagemProduto) ?>'); background-size: cover;"></div>
                                <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <h3 class="h5 fw-bold mb-0"><?= htmlspecialchars($nomeProduto) ?></h3>
                                        <span class="price-pill">R$ <?= number_format($preco, 2, ',', '.') ?></span>
                                    </div>
                                    <?php if ($descricaoProduto !== ''): ?>
                                        <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($descricaoProduto)) ?></p>
                                    <?php else: ?>
                                        <p class="text-muted mb-3">
                                            Produto artesanal com rendimento aproximado de <?= intval($produto['rendimento_porcoes']) ?> porcoes.
                                        </p>
                                    <?php endif; ?>
                                    <div class="small text-muted mb-3">
                                        <i class="bi bi-stars"></i>
                                        Ideal para encomendas, festas e presentes.
                                    </div>
                                    <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, $mensagemProduto)) ?>" target="_blank" rel="noopener" class="btn btn-pink mt-auto w-100">
                                        <i class="bi bi-whatsapp"></i> Quero pedir
                                    </a>
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

    <a href="<?= htmlspecialchars(cardapio_link_whatsapp($whatsapp, 'uma encomenda')) ?>" target="_blank" rel="noopener" class="floating-whatsapp" aria-label="Falar no WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <script>
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
    </script>
</body>
</html>
