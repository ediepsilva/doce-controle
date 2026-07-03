<?php
// API Pública de Receitas de Doces em Português
if (!defined('DOCE_APP_RETURN_CATALOGO')) {
    header('Content-Type: application/json; charset=utf-8');
}
require_once 'config.php';

$receitas_publicas = [
    ['id' => 1, 'nome_receita' => 'Brigadeiro Tradicional', 'descricao' => 'Brigadeiro clássico de chocolate', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 35.00],
    ['id' => 2, 'nome_receita' => 'Beijinho de Coco', 'descricao' => 'Docinhos de coco com calda', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 30.00],
    ['id' => 3, 'nome_receita' => 'Bolo de Chocolate', 'descricao' => 'Bolo úmido com ganache', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 45.00],
    ['id' => 4, 'nome_receita' => 'Pavê de Chocolate', 'descricao' => 'Pavê gelado com biscoito', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 50.00],
    ['id' => 5, 'nome_receita' => 'Olho de Sogra', 'descricao' => 'Docinhos com goiaba e chocolate', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 32.00],
    ['id' => 6, 'nome_receita' => 'Torta de Sorvete', 'descricao' => 'Torta gelada com sorvete', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 60.00],
    ['id' => 7, 'nome_receita' => 'Cupcake de Baunilha', 'descricao' => 'Cupcakes com buttercream', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 40.00],
    ['id' => 8, 'nome_receita' => 'Brownie de Chocolate', 'descricao' => 'Brownie fudge crocante', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 42.00],
    ['id' => 9, 'nome_receita' => 'Mousse de Frutas Vermelhas', 'descricao' => 'Mousse leve e arejada', 'rendimento_porcoes' => 6, 'preco_venda_sugerido' => 38.00],
    ['id' => 10, 'nome_receita' => 'Romeu e Julieta', 'descricao' => 'Goiaba com queijo branco', 'rendimento_porcoes' => 15, 'preco_venda_sugerido' => 35.00],
    ['id' => 11, 'nome_receita' => 'Churros de Chocolate', 'descricao' => 'Churros crocantes com chocolate', 'rendimento_porcoes' => 15, 'preco_venda_sugerido' => 36.00],
    ['id' => 12, 'nome_receita' => 'Torta de Morango', 'descricao' => 'Torta de biscoito com morangos frescos', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 55.00],
    ['id' => 13, 'nome_receita' => 'Açúcar Cristal', 'descricao' => 'Docinhos de açúcar cristal', 'rendimento_porcoes' => 30, 'preco_venda_sugerido' => 28.00],
    ['id' => 14, 'nome_receita' => 'Merengue', 'descricao' => 'Suspiro de merengue crocante', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 25.00],
    ['id' => 15, 'nome_receita' => 'Pudim de Leite Condensado', 'descricao' => 'Pudim cremoso com calda de caramelo', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 40.00],
    ['id' => 16, 'nome_receita' => 'Brigadeiro Branco', 'descricao' => 'Brigadeiro claro com sabor suave', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 34.00],
    ['id' => 17, 'nome_receita' => 'Brigadeiro de Ninho', 'descricao' => 'Docinho cremoso de leite em po', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 38.00],
    ['id' => 18, 'nome_receita' => 'Brigadeiro de Cafe', 'descricao' => 'Brigadeiro com toque de cafe', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 36.00],
    ['id' => 19, 'nome_receita' => 'Cajuzinho', 'descricao' => 'Docinho tradicional de amendoim', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 30.00],
    ['id' => 20, 'nome_receita' => 'Bicho de Pe', 'descricao' => 'Docinho rosa sabor morango', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 32.00],
    ['id' => 21, 'nome_receita' => 'Casadinho', 'descricao' => 'Docinho metade branco e metade chocolate', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 36.00],
    ['id' => 22, 'nome_receita' => 'Camafeu de Nozes', 'descricao' => 'Doce fino com nozes e fondant', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 55.00],
    ['id' => 23, 'nome_receita' => 'Trufa de Chocolate', 'descricao' => 'Trufa recheada com ganache', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 48.00],
    ['id' => 24, 'nome_receita' => 'Bolo de Cenoura', 'descricao' => 'Bolo caseiro com cobertura de chocolate', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 42.00],
    ['id' => 25, 'nome_receita' => 'Bolo de Fuba', 'descricao' => 'Bolo simples de fuba para cafe', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 32.00],
    ['id' => 26, 'nome_receita' => 'Bolo de Laranja', 'descricao' => 'Bolo citrico e macio', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 36.00],
    ['id' => 27, 'nome_receita' => 'Bolo Red Velvet', 'descricao' => 'Bolo vermelho com recheio cremoso', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 80.00],
    ['id' => 28, 'nome_receita' => 'Bolo de Coco Gelado', 'descricao' => 'Bolo molhadinho embrulhado', 'rendimento_porcoes' => 16, 'preco_venda_sugerido' => 55.00],
    ['id' => 29, 'nome_receita' => 'Bolo de Banana', 'descricao' => 'Bolo com banana e canela', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 38.00],
    ['id' => 30, 'nome_receita' => 'Bolo de Pote Chocolate', 'descricao' => 'Bolo de pote com creme de chocolate', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 50.00],
    ['id' => 31, 'nome_receita' => 'Torta de Limao', 'descricao' => 'Torta citrica com merengue', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 52.00],
    ['id' => 32, 'nome_receita' => 'Torta Holandesa', 'descricao' => 'Torta gelada com creme e chocolate', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 70.00],
    ['id' => 33, 'nome_receita' => 'Torta Banoffee', 'descricao' => 'Torta de banana com doce de leite', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 68.00],
    ['id' => 34, 'nome_receita' => 'Cheesecake de Morango', 'descricao' => 'Cheesecake cremoso com calda', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 75.00],
    ['id' => 35, 'nome_receita' => 'Palha Italiana', 'descricao' => 'Brigadeiro com biscoito em quadrados', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 40.00],
    ['id' => 36, 'nome_receita' => 'Pudim de Chocolate', 'descricao' => 'Pudim cremoso sabor chocolate', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 45.00],
    ['id' => 37, 'nome_receita' => 'Pudim de Coco', 'descricao' => 'Pudim com coco ralado', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 44.00],
    ['id' => 38, 'nome_receita' => 'Quindim', 'descricao' => 'Doce amarelo de gemas e coco', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 48.00],
    ['id' => 39, 'nome_receita' => 'Ambrosia', 'descricao' => 'Doce tradicional de leite e ovos', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 40.00],
    ['id' => 40, 'nome_receita' => 'Canjica Doce', 'descricao' => 'Sobremesa cremosa de milho branco', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 35.00],
    ['id' => 41, 'nome_receita' => 'Arroz Doce', 'descricao' => 'Arroz doce cremoso com canela', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 32.00],
    ['id' => 42, 'nome_receita' => 'Curau de Milho', 'descricao' => 'Creme doce de milho verde', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 34.00],
    ['id' => 43, 'nome_receita' => 'Mousse de Maracuja', 'descricao' => 'Mousse azedinha e cremosa', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 36.00],
    ['id' => 44, 'nome_receita' => 'Mousse de Chocolate', 'descricao' => 'Mousse intensa de chocolate', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 42.00],
    ['id' => 45, 'nome_receita' => 'Gelatina Colorida', 'descricao' => 'Sobremesa colorida com creme', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 30.00],
    ['id' => 46, 'nome_receita' => 'Manjar de Coco', 'descricao' => 'Manjar branco com calda', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 38.00],
    ['id' => 47, 'nome_receita' => 'Panna Cotta de Baunilha', 'descricao' => 'Creme gelado de baunilha', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 48.00],
    ['id' => 48, 'nome_receita' => 'Cupcake de Chocolate', 'descricao' => 'Cupcake de chocolate com cobertura', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 42.00],
    ['id' => 49, 'nome_receita' => 'Cupcake de Morango', 'descricao' => 'Cupcake com creme de morango', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 42.00],
    ['id' => 50, 'nome_receita' => 'Mini Brownie Recheado', 'descricao' => 'Brownie pequeno com recheio cremoso', 'rendimento_porcoes' => 16, 'preco_venda_sugerido' => 55.00],
];

// Ingredientes do catalogo publico. Essas receitas nao ficam no banco do usuario,
// entao os detalhes precisam vir junto da API publica.
$ingredientes_publicos = [
    1 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'colheres', 'item_nome' => 'Chocolate em po'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Granulado'],
    ],
    2 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Coco ralado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '25', 'unidade_medida' => 'un', 'item_nome' => 'Cravos para decorar'],
    ],
    3 => [
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Chocolate em po'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Fermento'],
    ],
    4 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'pacote', 'item_nome' => 'Biscoito maisena'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'latas', 'item_nome' => 'Creme de leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Chocolate meio amargo'],
    ],
    5 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Coco ralado'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Goiabada'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Manteiga'],
    ],
    6 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'pote', 'item_nome' => 'Sorvete de creme'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'pacote', 'item_nome' => 'Biscoito triturado'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Chocolate'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Creme de leite'],
    ],
    7 => [
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Essencia de baunilha'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Buttercream'],
    ],
    8 => [
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Chocolate meio amargo'],
        ['quantidade_usada' => '150', 'unidade_medida' => 'g', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Farinha de trigo'],
    ],
    9 => [
        ['quantidade_usada' => '300', 'unidade_medida' => 'g', 'item_nome' => 'Frutas vermelhas'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Creme de leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'pacote', 'item_nome' => 'Gelatina sem sabor'],
    ],
    10 => [
        ['quantidade_usada' => '300', 'unidade_medida' => 'g', 'item_nome' => 'Goiabada'],
        ['quantidade_usada' => '300', 'unidade_medida' => 'g', 'item_nome' => 'Queijo branco'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'fio', 'item_nome' => 'Mel ou calda'],
    ],
    11 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Agua'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'colheres', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Chocolate para recheio'],
    ],
    12 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'pacote', 'item_nome' => 'Biscoito triturado'],
        ['quantidade_usada' => '300', 'unidade_medida' => 'g', 'item_nome' => 'Morangos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Creme de leite'],
    ],
    13 => [
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Acucar cristal'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Manteiga'],
    ],
    14 => [
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Claras'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '5', 'unidade_medida' => 'gotas', 'item_nome' => 'Limao'],
    ],
    15 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'latas', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar para calda'],
    ],
];

$preparos_publicos = [
    1 => ['Misture leite condensado, chocolate em po e manteiga em fogo baixo.', 'Mexa sem parar ate soltar do fundo da panela.', 'Deixe esfriar, enrole e passe no granulado.'],
    2 => ['Misture leite condensado, coco ralado e manteiga em fogo baixo.', 'Mexa ate desgrudar do fundo da panela.', 'Deixe esfriar, enrole e decore com coco ou cravo.'],
    3 => ['Misture os ingredientes secos em uma tigela.', 'Adicione ovos, leite e misture ate formar massa lisa.', 'Junte o fermento por ultimo e asse em forma untada.', 'Finalize com ganache se desejar.'],
    4 => ['Prepare o creme com leite condensado e creme de leite.', 'Monte camadas de biscoito e creme em uma travessa.', 'Cubra com chocolate derretido e leve para gelar.'],
    5 => ['Prepare uma massa de beijinho com leite condensado, coco e manteiga.', 'Corte a goiabada em pequenos pedacos.', 'Modele os docinhos envolvendo a goiabada.'],
    6 => ['Forre a forma com biscoito triturado.', 'Espalhe o sorvete e nivele bem.', 'Cubra com calda de chocolate e leve ao freezer ate firmar.'],
    7 => ['Prepare a massa misturando farinha, ovos, acucar e baunilha.', 'Distribua em forminhas e asse ate dourar.', 'Espere esfriar e cubra com buttercream.'],
    8 => ['Derreta chocolate com manteiga.', 'Misture ovos, acucar e farinha.', 'Leve ao forno ate formar casquinha por cima e centro macio.'],
    9 => ['Bata frutas vermelhas, leite condensado e creme de leite.', 'Hidrate a gelatina e incorpore ao creme.', 'Distribua em tacas e leve para gelar.'],
    10 => ['Corte goiabada e queijo em porcoes iguais.', 'Monte as camadas ou sirva lado a lado.', 'Finalize com mel ou calda se quiser.'],
    11 => ['Ferva agua com manteiga e adicione a farinha.', 'Mexa ate formar uma massa firme.', 'Modele, frite e recheie com chocolate.'],
    12 => ['Prepare a base com biscoito triturado.', 'Misture leite condensado e creme de leite para o creme.', 'Monte com morangos e leve para gelar.'],
    13 => ['Misture acucar cristal, leite e manteiga em fogo baixo.', 'Mexa ate engrossar.', 'Modele os docinhos depois de frio.'],
    14 => ['Bata claras com acucar ate formar picos firmes.', 'Pingue pequenas porcoes em assadeira.', 'Asse em temperatura baixa ate secar.'],
    15 => ['Bata leite condensado, leite e ovos.', 'Caramelize a forma com acucar.', 'Asse em banho-maria ate firmar e leve para gelar.'],
];

$ingredientes_modelo = [
    'docinho' => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Sabor principal'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Confeito para finalizar'],
    ],
    'bolo' => [
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher', 'item_nome' => 'Fermento'],
    ],
    'torta' => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'pacote', 'item_nome' => 'Biscoito triturado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Creme de leite'],
        ['quantidade_usada' => '300', 'unidade_medida' => 'g', 'item_nome' => 'Recheio principal'],
    ],
    'sobremesa' => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Creme de leite'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Sabor principal'],
    ],
    'pudim' => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'latas', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar para calda'],
    ],
    'cupcake' => [
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Cobertura cremosa'],
    ],
    'brownie' => [
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Chocolate'],
        ['quantidade_usada' => '150', 'unidade_medida' => 'g', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Farinha de trigo'],
    ],
];

$preparos_modelo = [
    'docinho' => ['Misture os ingredientes em fogo baixo.', 'Mexa ate soltar do fundo da panela.', 'Deixe esfriar, modele e finalize.'],
    'bolo' => ['Misture os ingredientes secos.', 'Adicione ovos, leite e o sabor principal.', 'Coloque fermento por ultimo e asse em forma untada.'],
    'torta' => ['Prepare a base com biscoito triturado.', 'Misture o creme e o recheio principal.', 'Monte a torta e leve para gelar.'],
    'sobremesa' => ['Prepare o creme base.', 'Incorpore o sabor principal.', 'Distribua em tacas ou travessa e leve para gelar.'],
    'pudim' => ['Bata os ingredientes do pudim.', 'Caramelize a forma.', 'Asse em banho-maria, esfrie e desenforme.'],
    'cupcake' => ['Prepare a massa e distribua em forminhas.', 'Asse ate dourar.', 'Espere esfriar e finalize com cobertura.'],
    'brownie' => ['Derreta chocolate com manteiga.', 'Misture ovos, acucar e farinha.', 'Asse ate formar casquinha por cima.'],
];

$categorias_por_id = [
    1 => 'docinho', 2 => 'docinho', 3 => 'bolo', 4 => 'sobremesa', 5 => 'docinho',
    6 => 'torta', 7 => 'cupcake', 8 => 'brownie', 9 => 'sobremesa', 10 => 'sobremesa',
    11 => 'sobremesa', 12 => 'torta', 13 => 'docinho', 14 => 'sobremesa', 15 => 'pudim',
    16 => 'docinho', 17 => 'docinho', 18 => 'docinho', 19 => 'docinho', 20 => 'docinho', 21 => 'docinho', 22 => 'docinho', 23 => 'docinho',
    24 => 'bolo', 25 => 'bolo', 26 => 'bolo', 27 => 'bolo', 28 => 'bolo', 29 => 'bolo', 30 => 'bolo',
    31 => 'torta', 32 => 'torta', 33 => 'torta', 34 => 'torta',
    35 => 'docinho',
    36 => 'pudim', 37 => 'pudim',
    38 => 'sobremesa', 39 => 'sobremesa', 40 => 'sobremesa', 41 => 'sobremesa', 42 => 'sobremesa', 43 => 'sobremesa', 44 => 'sobremesa', 45 => 'sobremesa', 46 => 'sobremesa', 47 => 'sobremesa',
    48 => 'cupcake', 49 => 'cupcake',
    50 => 'brownie',
];

$receitas_publicas = [
    ['id' => 1, 'nome_receita' => 'Brigadeiro Tradicional', 'descricao' => 'Docinho classico de chocolate para enrolar', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 35.00],
    ['id' => 2, 'nome_receita' => 'Beijinho de Coco', 'descricao' => 'Docinho de coco com acabamento em coco ralado', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 34.00],
    ['id' => 3, 'nome_receita' => 'Brigadeiro de Ninho', 'descricao' => 'Brigadeiro branco com leite em po', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 40.00],
    ['id' => 4, 'nome_receita' => 'Bicho de Pe', 'descricao' => 'Docinho rosa sabor morango', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 36.00],
    ['id' => 5, 'nome_receita' => 'Cajuzinho', 'descricao' => 'Docinho de amendoim tradicional', 'rendimento_porcoes' => 25, 'preco_venda_sugerido' => 34.00],
    ['id' => 6, 'nome_receita' => 'Palha Italiana', 'descricao' => 'Brigadeiro com biscoito em quadradinhos', 'rendimento_porcoes' => 20, 'preco_venda_sugerido' => 42.00],
    ['id' => 7, 'nome_receita' => 'Bolo de Cenoura', 'descricao' => 'Bolo de cenoura com cobertura de chocolate', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 45.00],
    ['id' => 8, 'nome_receita' => 'Brownie de Chocolate', 'descricao' => 'Brownie intenso com casquinha e centro macio', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 48.00],
    ['id' => 9, 'nome_receita' => 'Pudim de Leite Condensado', 'descricao' => 'Pudim cremoso com calda de caramelo', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 45.00],
    ['id' => 10, 'nome_receita' => 'Mousse de Maracuja', 'descricao' => 'Mousse cremosa com sabor azedinho', 'rendimento_porcoes' => 8, 'preco_venda_sugerido' => 38.00],
    ['id' => 11, 'nome_receita' => 'Torta de Limao', 'descricao' => 'Torta com base crocante, creme de limao e merengue', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 58.00],
    ['id' => 12, 'nome_receita' => 'Torta de Morango', 'descricao' => 'Torta cremosa com morangos frescos', 'rendimento_porcoes' => 10, 'preco_venda_sugerido' => 65.00],
    ['id' => 13, 'nome_receita' => 'Bolo de Chocolate', 'descricao' => 'Bolo de chocolate simples e macio', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 50.00],
    ['id' => 14, 'nome_receita' => 'Suspiro Merengue', 'descricao' => 'Suspiro leve e crocante', 'rendimento_porcoes' => 30, 'preco_venda_sugerido' => 30.00],
    ['id' => 15, 'nome_receita' => 'Cupcake de Baunilha', 'descricao' => 'Cupcake de baunilha com cobertura cremosa', 'rendimento_porcoes' => 12, 'preco_venda_sugerido' => 44.00],
];

$ingredientes_publicos = [
    1 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Chocolate em po 50%'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '120', 'unidade_medida' => 'g', 'item_nome' => 'Granulado'],
    ],
    2 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '80', 'unidade_medida' => 'g', 'item_nome' => 'Coco ralado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '80', 'unidade_medida' => 'g', 'item_nome' => 'Coco ralado para finalizar'],
    ],
    3 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '4', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Leite em po'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '80', 'unidade_medida' => 'g', 'item_nome' => 'Leite em po para finalizar'],
    ],
    4 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Gelatina sabor morango'],
        ['quantidade_usada' => '80', 'unidade_medida' => 'g', 'item_nome' => 'Acucar cristal para finalizar'],
    ],
    5 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '150', 'unidade_medida' => 'g', 'item_nome' => 'Amendoim torrado e moido'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Chocolate em po'],
    ],
    6 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Chocolate em po'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '150', 'unidade_medida' => 'g', 'item_nome' => 'Biscoito maisena picado'],
    ],
    7 => [
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Cenouras medias picadas'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Oleo'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Fermento'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'receita', 'item_nome' => 'Cobertura de chocolate'],
    ],
    8 => [
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Chocolate meio amargo'],
        ['quantidade_usada' => '150', 'unidade_medida' => 'g', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '3/4', 'unidade_medida' => 'xicara', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Chocolate em po'],
    ],
    9 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'medidas da lata', 'item_nome' => 'Leite integral'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar para calda'],
    ],
    10 => [
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'caixa', 'item_nome' => 'Creme de leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Suco concentrado de maracuja'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'un', 'item_nome' => 'Polpa de maracuja para decorar'],
    ],
    11 => [
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Biscoito maisena triturado'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Manteiga derretida'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'caixa', 'item_nome' => 'Creme de leite'],
        ['quantidade_usada' => '1/2', 'unidade_medida' => 'xicara', 'item_nome' => 'Suco de limao'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'un', 'item_nome' => 'Claras para merengue'],
    ],
    12 => [
        ['quantidade_usada' => '200', 'unidade_medida' => 'g', 'item_nome' => 'Biscoito triturado'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Manteiga derretida'],
        ['quantidade_usada' => '500', 'unidade_medida' => 'ml', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'lata', 'item_nome' => 'Leite condensado'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'colheres sopa', 'item_nome' => 'Amido de milho'],
        ['quantidade_usada' => '300', 'unidade_medida' => 'g', 'item_nome' => 'Morangos'],
    ],
    13 => [
        ['quantidade_usada' => '2', 'unidade_medida' => 'xicaras', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Chocolate em po'],
        ['quantidade_usada' => '1 e 1/2', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '1/2', 'unidade_medida' => 'xicara', 'item_nome' => 'Oleo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher sopa', 'item_nome' => 'Fermento'],
    ],
    14 => [
        ['quantidade_usada' => '3', 'unidade_medida' => 'un', 'item_nome' => 'Claras'],
        ['quantidade_usada' => '1 e 1/2', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '5', 'unidade_medida' => 'gotas', 'item_nome' => 'Limao'],
    ],
    15 => [
        ['quantidade_usada' => '1 e 1/2', 'unidade_medida' => 'xicara', 'item_nome' => 'Farinha de trigo'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'xicara', 'item_nome' => 'Acucar'],
        ['quantidade_usada' => '2', 'unidade_medida' => 'un', 'item_nome' => 'Ovos'],
        ['quantidade_usada' => '1/2', 'unidade_medida' => 'xicara', 'item_nome' => 'Leite'],
        ['quantidade_usada' => '100', 'unidade_medida' => 'g', 'item_nome' => 'Manteiga'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher cha', 'item_nome' => 'Baunilha'],
        ['quantidade_usada' => '1', 'unidade_medida' => 'colher cha', 'item_nome' => 'Fermento'],
    ],
];

$preparos_publicos = [
    1 => ['Misture leite condensado, chocolate e manteiga em uma panela.', 'Cozinhe em fogo baixo, mexendo sempre, ate desgrudar do fundo.', 'Transfira para um prato untado e deixe esfriar.', 'Enrole com as maos untadas e passe no granulado.'],
    2 => ['Misture leite condensado, coco ralado e manteiga.', 'Cozinhe em fogo baixo ate soltar do fundo.', 'Deixe esfriar completamente.', 'Enrole e passe no coco ralado.'],
    3 => ['Misture leite condensado, leite em po e manteiga.', 'Cozinhe em fogo baixo ate ponto de enrolar.', 'Esfrie, enrole e finalize no leite em po.'],
    4 => ['Misture leite condensado, manteiga e gelatina de morango.', 'Cozinhe em fogo baixo ate desgrudar da panela.', 'Deixe esfriar, modele e passe no acucar cristal.'],
    5 => ['Misture leite condensado, manteiga, amendoim e chocolate.', 'Cozinhe ate engrossar e desgrudar do fundo.', 'Esfrie, modele em formato de cajuzinho e finalize como preferir.'],
    6 => ['Prepare um brigadeiro com leite condensado, chocolate e manteiga.', 'Quando chegar ao ponto, desligue o fogo e misture o biscoito picado.', 'Espalhe em forma untada ou forrada com papel manteiga.', 'Depois de firme, corte em quadrados.'],
    7 => ['Bata cenoura, ovos e oleo no liquidificador.', 'Misture com acucar e farinha em uma tigela.', 'Adicione fermento por ultimo.', 'Asse em forma untada a 180 graus ate firmar.', 'Cubra com calda de chocolate.'],
    8 => ['Derreta chocolate e manteiga juntos.', 'Misture ovos e acucar sem bater demais.', 'Adicione chocolate derretido, farinha e chocolate em po.', 'Asse em forma forrada a 180 graus ate formar casquinha e centro ainda umido.'],
    9 => ['Derreta o acucar e caramelize a forma.', 'Bata leite condensado, leite e ovos no liquidificador.', 'Despeje na forma caramelizada.', 'Asse em banho-maria ate firmar.', 'Leve para gelar antes de desenformar.'],
    10 => ['Bata leite condensado, creme de leite e suco de maracuja.', 'Distribua em tacas ou travessa.', 'Leve para gelar por pelo menos 3 horas.', 'Finalize com polpa de maracuja.'],
    11 => ['Misture biscoito triturado com manteiga e forre a forma.', 'Misture leite condensado, creme de leite e suco de limao.', 'Coloque o creme sobre a base.', 'Prepare merengue com claras e acucar e cubra a torta.', 'Leve para gelar antes de servir.'],
    12 => ['Misture biscoito e manteiga para formar a base.', 'Cozinhe leite, leite condensado e amido ate virar creme.', 'Coloque o creme sobre a base ja fria.', 'Cubra com morangos e leve para gelar.'],
    13 => ['Misture farinha, chocolate e acucar.', 'Adicione ovos, leite e oleo e mexa ate ficar uniforme.', 'Acrescente fermento por ultimo.', 'Asse a 180 graus em forma untada.', 'Finalize com cobertura se desejar.'],
    14 => ['Bata claras ate espumar.', 'Adicione acucar aos poucos e bata ate formar picos firmes.', 'Acrescente gotas de limao.', 'Modele em assadeira forrada e asse em forno baixo ate secar.'],
    15 => ['Bata manteiga e acucar ate formar creme.', 'Adicione ovos, baunilha, leite e farinha aos poucos.', 'Misture o fermento por ultimo.', 'Distribua em forminhas e asse a 180 graus.', 'Finalize com cobertura depois de frio.'],
];

$categorias_por_id = [
    1 => 'docinho', 2 => 'docinho', 3 => 'docinho', 4 => 'docinho', 5 => 'docinho', 6 => 'docinho',
    7 => 'bolo', 8 => 'brownie', 9 => 'pudim', 10 => 'sobremesa', 11 => 'torta', 12 => 'torta',
    13 => 'bolo', 14 => 'sobremesa', 15 => 'cupcake',
];

function quantidade_decimal($valor)
{
    $valor = trim(str_replace(',', '.', (string)$valor));
    if (strpos($valor, ' e ') !== false) {
        $partes = explode(' e ', $valor);
        return quantidade_decimal($partes[0]) + quantidade_decimal($partes[1]);
    }
    if (strpos($valor, '/') !== false) {
        [$num, $den] = array_map('floatval', explode('/', $valor, 2));
        return $den > 0 ? $num / $den : 0;
    }
    return floatval($valor);
}

function ingrediente_base($quantidade, $unidade, $nome)
{
    return [
        'quantidade_usada' => round($quantidade, 3),
        'unidade_medida' => $unidade,
        'item_nome' => $nome,
    ];
}

function chave_ingrediente_custo($nome)
{
    $nome = trim((string)$nome);
    $nome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
    $nome = strtolower($nome);
    $nome = preg_replace('/[0-9]+(g|ml|%)?/', '', $nome);
    $nome = str_replace([' derretida', ' picado', ' picada', ' picados', ' picadas', ' triturado', ' triturada', ' para finalizar', ' para decorar', ' para calda', ' sabor'], '', $nome);
    $nome = trim(preg_replace('/\s+/', ' ', $nome));

    if (strpos($nome, 'clara') !== false || $nome === 'ovo' || $nome === 'ovos') {
        return 'ovos';
    }
    if (strpos($nome, 'leite condensado') !== false) {
        return 'leite condensado';
    }
    if (strpos($nome, 'creme de leite') !== false) {
        return 'creme de leite';
    }
    if (strpos($nome, 'acucar') !== false) {
        return 'acucar';
    }
    if (strpos($nome, 'chocolate em po') !== false) {
        return 'chocolate em po';
    }
    if (strpos($nome, 'coco ralado') !== false) {
        return 'coco ralado';
    }
    if (strpos($nome, 'leite em po') !== false) {
        return 'leite em po';
    }
    if (strpos($nome, 'manteiga') !== false) {
        return 'manteiga';
    }

    return $nome;
}

function normalizar_ingrediente_catalogo($ingrediente)
{
    $qtd = quantidade_decimal($ingrediente['quantidade_usada']);
    $unidade = strtolower(trim($ingrediente['unidade_medida']));
    $nomeOriginal = trim($ingrediente['item_nome']);
    $nome = strtolower($nomeOriginal);

    if (strpos($nome, 'leite condensado') !== false && in_array($unidade, ['lata', 'latas'], true)) {
        return ingrediente_base($qtd, 'un', 'Leite condensado 395g');
    }
    if (strpos($nome, 'creme de leite') !== false && in_array($unidade, ['caixa', 'lata', 'latas'], true)) {
        return ingrediente_base($qtd, 'un', 'Creme de leite 200g');
    }
    if (in_array($unidade, ['un', 'unidade', 'unidades'], true)) {
        if (strpos($nome, 'clara') !== false) {
            return ingrediente_base($qtd, 'un', 'Ovos');
        }
        return ingrediente_base($qtd, 'un', $nomeOriginal);
    }
    if ($unidade === 'gotas') {
        return ingrediente_base($qtd * 0.05, 'ml', $nomeOriginal);
    }
    if (strpos($unidade, 'medidas da lata') !== false) {
        return ingrediente_base($qtd * 395, 'ml', $nomeOriginal);
    }
    if (strpos($unidade, 'xicara') !== false) {
        if (strpos($nome, 'farinha') !== false) {
            return ingrediente_base($qtd * 120, 'g', 'Farinha de trigo');
        }
        if (strpos($nome, 'acucar') !== false) {
            return ingrediente_base($qtd * 200, 'g', $nomeOriginal);
        }
        if (strpos($nome, 'chocolate') !== false) {
            return ingrediente_base($qtd * 90, 'g', $nomeOriginal);
        }
        if (strpos($nome, 'oleo') !== false || strpos($nome, 'suco') !== false || strpos($nome, 'leite') !== false) {
            return ingrediente_base($qtd * 240, 'ml', $nomeOriginal);
        }
    }
    if (strpos($unidade, 'colher sopa') !== false || strpos($unidade, 'colheres sopa') !== false || $unidade === 'colher' || $unidade === 'colheres') {
        if (strpos($nome, 'manteiga') !== false || strpos($nome, 'amido') !== false) {
            return ingrediente_base($qtd * 15, 'g', str_replace(' derretida', '', $nomeOriginal));
        }
        return ingrediente_base($qtd * 10, 'g', $nomeOriginal);
    }
    if (strpos($unidade, 'colher cha') !== false) {
        return ingrediente_base($qtd * 5, 'g', $nomeOriginal);
    }
    if ($unidade === 'receita') {
        return ingrediente_base($qtd, 'un', $nomeOriginal);
    }
    if (in_array($unidade, ['g', 'kg', 'ml', 'l'], true)) {
        if ($unidade === 'kg') {
            return ingrediente_base($qtd * 1000, 'g', $nomeOriginal);
        }
        if ($unidade === 'l') {
            return ingrediente_base($qtd * 1000, 'ml', $nomeOriginal);
        }
        return ingrediente_base($qtd, $unidade, str_replace(' derretida', '', $nomeOriginal));
    }

    return ingrediente_base($qtd, $unidade ?: 'un', $nomeOriginal);
}

function quantidade_na_unidade_do_estoque($quantidade, $unidadeIngrediente, $unidadeEstoque)
{
    $unidadeIngrediente = strtolower(trim((string)$unidadeIngrediente));
    $unidadeEstoque = strtolower(trim((string)$unidadeEstoque));

    if ($unidadeIngrediente === $unidadeEstoque || $unidadeEstoque === '') {
        return floatval($quantidade);
    }

    if ($unidadeIngrediente === 'g' && $unidadeEstoque === 'kg') {
        return floatval($quantidade) / 1000;
    }
    if ($unidadeIngrediente === 'kg' && $unidadeEstoque === 'g') {
        return floatval($quantidade) * 1000;
    }
    if ($unidadeIngrediente === 'ml' && $unidadeEstoque === 'l') {
        return floatval($quantidade) / 1000;
    }
    if ($unidadeIngrediente === 'l' && $unidadeEstoque === 'ml') {
        return floatval($quantidade) * 1000;
    }

    return floatval($quantidade);
}

foreach ($receitas_publicas as &$receita_publica) {
    $categoria = $categorias_por_id[$receita_publica['id']] ?? 'sobremesa';
    $receita_publica['origem'] = 'publica';
    $receita_publica['categoria'] = $categoria;
    $receita_publica['ingredientes'] = $ingredientes_publicos[$receita_publica['id']] ?? [];
    $receita_publica['modo_preparo'] = $preparos_publicos[$receita_publica['id']] ?? [];
    if (empty($receita_publica['ingredientes'])) {
        $receita_publica['ingredientes'] = $ingredientes_modelo[$categoria] ?? $ingredientes_modelo['sobremesa'];
    }
    if (empty($receita_publica['modo_preparo'])) {
        $receita_publica['modo_preparo'] = $preparos_modelo[$categoria] ?? $preparos_modelo['sobremesa'];
    }
    $receita_publica['ingredientes'] = array_map('normalizar_ingrediente_catalogo', $receita_publica['ingredientes']);
}
unset($receita_publica);

$estoquePorNome = [];
try {
    $user_id_api = $_SESSION['user_id'] ?? 1;
    $stmtEstoque = $pdo->prepare("SELECT item_nome, unidade_medida, preco_unitario FROM estoque WHERE user_id = ?");
    $stmtEstoque->execute([$user_id_api]);
    foreach ($stmtEstoque->fetchAll(PDO::FETCH_ASSOC) as $itemEstoque) {
        $chaveEstoque = chave_ingrediente_custo($itemEstoque['item_nome']);
        if (!isset($estoquePorNome[$chaveEstoque]) || floatval($itemEstoque['preco_unitario']) > floatval($estoquePorNome[$chaveEstoque]['preco_unitario'])) {
            $estoquePorNome[$chaveEstoque] = $itemEstoque;
        }
    }
} catch (Exception $e) {
    $estoquePorNome = [];
}

foreach ($receitas_publicas as &$receita_publica) {
    $custoTotal = 0;
    $faltantes = [];

    foreach ($receita_publica['ingredientes'] as &$ingrediente) {
        $chaveIngrediente = chave_ingrediente_custo($ingrediente['item_nome']);
        $itemEstoque = $estoquePorNome[$chaveIngrediente] ?? null;
        $precoUnitario = $itemEstoque ? floatval($itemEstoque['preco_unitario']) : 0;
        $quantidadeCobrada = $itemEstoque
            ? quantidade_na_unidade_do_estoque($ingrediente['quantidade_usada'], $ingrediente['unidade_medida'], $itemEstoque['unidade_medida'])
            : floatval($ingrediente['quantidade_usada']);
        $custoItem = $quantidadeCobrada * $precoUnitario;

        $ingrediente['preco_unitario'] = round($precoUnitario, 6);
        $ingrediente['custo_item'] = round($custoItem, 2);
        $ingrediente['quantidade_calculo'] = round($quantidadeCobrada, 3);
        $ingrediente['unidade_calculo'] = $itemEstoque['unidade_medida'] ?? $ingrediente['unidade_medida'];

        if ($precoUnitario > 0) {
            $custoTotal += $custoItem;
        } else {
            $faltantes[] = $ingrediente['item_nome'];
        }
    }
    unset($ingrediente);

    $receita_publica['custo_real'] = round($custoTotal, 2);
    $receita_publica['custo_status'] = count($faltantes) > 0 ? 'parcial' : 'completo';
    $receita_publica['ingredientes_sem_preco'] = array_values(array_unique($faltantes));
    $receita_publica['preco_sugerido_calculado'] = round($custoTotal * DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO, 2);
    $receita_publica['multiplicador_preco'] = DOCE_APP_MULTIPLICADOR_PRECO_SUGERIDO;
}
unset($receita_publica);

if (defined('DOCE_APP_RETURN_CATALOGO')) {
    return $receitas_publicas;
}

$acao = $_REQUEST['acao'] ?? 'listar';

if ($acao === 'listar') {
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Lista de receitas públicas carregada.',
        'dados' => $receitas_publicas,
        'total' => count($receitas_publicas)
    ]);
} elseif ($acao === 'buscar') {
    $id = intval($_REQUEST['id'] ?? 0);
    $receita = null;
    foreach ($receitas_publicas as $r) {
        if ($r['id'] == $id) {
            $receita = $r;
            break;
        }
    }
    if ($receita) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Receita encontrada.', 'dados' => $receita]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Receita não encontrada.']);
    }
} elseif ($acao === 'buscar_por_nome') {
    $nome = trim(strtolower($_REQUEST['nome'] ?? ''));
    $resultados = [];
    if ($nome) {
        foreach ($receitas_publicas as $r) {
            if (strpos(strtolower($r['nome_receita']), $nome) !== false) {
                $resultados[] = $r;
            }
        }
    }
    echo json_encode(['sucesso' => true, 'mensagem' => count($resultados) . ' receita(s) encontrada(s).', 'dados' => $resultados, 'total' => count($resultados)]);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não reconhecida. Use: listar, buscar, buscar_por_nome']);
}
?>
