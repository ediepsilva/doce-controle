# Doce Controle

Aplicativo PHP para controle de receitas, estoque, clientes, pedidos e cardápio de uma confeitaria,
com painel de cozinha, cardápio público e instalação como aplicativo (PWA).

## Tecnologias

- PHP (procedural) com PDO
- MySQL / MariaDB
- HTML, CSS e JavaScript, sem framework e sem dependências de Composer ou Node
- PWA (`manifest.json` e `assets/pwa.js`)

## Requisitos

- XAMPP (Apache + PHP + MariaDB) ou equivalente
- PHP 8.x com a extensão `pdo_mysql`

## Como usar localmente

1. Coloque a pasta do projeto em `C:\xampp\htdocs\doce-controle`. O nome da pasta define o endereço.
2. Inicie o Apache e o MySQL pelo XAMPP.
3. Abra o phpMyAdmin e execute o arquivo `schema.sql` para criar o banco `doce_controle`.
4. Se o projeto já tinha tabelas antigas, execute também `database_updates.sql`.
5. Acesse `http://localhost/doce-controle/` no navegador.
6. Crie sua conta na tela de cadastro e entre com e-mail e senha.

Para bancos criados antes do login real, execute `database_updates.sql` para adicionar os campos de
usuário mais recentes, incluindo celular/WhatsApp e senha. Faça backup antes de rodar scripts SQL
sobre um banco com dados reais.

## Configuração

A conexão fica no início de [`config.php`](config.php): servidor `localhost`, banco `doce_controle`,
usuário `root` e senha vazia (padrão do XAMPP). Se o seu MySQL tiver senha ou for usado fora do seu
computador, altere esses valores e **não versione credenciais reais**. Não há variáveis de ambiente.
`testar_conexao.php` ajuda a verificar se o banco responde.

## Banco de dados

Nome padrão: `doce_controle`. `schema.sql` cria `users`, `clientes`, `estoque`, `receitas`,
`receitas_itens`, `pedidos` e `historico_precos`; `database_updates.sql` traz as atualizações
incrementais.

## Módulos

- Dashboard
- Receitas
- Estoque
- Clientes
- Pedidos e painel da cozinha
- Cardápio público

## Estrutura do projeto

```
index.php, login.php, cadastro.php, redefinir_senha.php   acesso e painel
estoque.php, salvar_*.php, editar_*.php, excluir_*.php    insumos, receitas, clientes, pedidos
pedidos.php, cozinha.php, atualizar_status_pedido.php     pedidos e cozinha
cardapio.php, api_receitas_publicas.php                   catálogo público
api_receitas.php                                          API de receitas (exige sessão)
cobranca.php                                              acesso de usuário inativo
config.php                                                conexão, sessão e regras de acesso
assets/                                                   logo, PWA e estilos
schema.sql, database_updates.sql                          banco de dados
```

## Como testar

Não há suíte de testes do sistema. `test_somar.py` e `teste_primeiro.ipynb` são experimentos soltos,
sem relação com a aplicação. Verificação manual sugerida: cadastrar usuário, insumo, receita e
pedido, e conferir o painel da cozinha.

## Segurança

`api_receitas.php` autentica **somente por sessão**. Um atalho de desenvolvimento por token na URL
foi removido porque permitia agir como qualquer usuário sem login. Se este sistema já esteve
acessível pela internet com esse atalho, trate os dados como expostos.

## Status atual

Em uso e em evolução. Concluído: login, cadastro e redefinição de senha, estoque, receitas com itens
e histórico de preços, clientes, pedidos, cozinha, cardápio público e PWA.

Pendências: mover a configuração do banco para fora do repositório antes de publicar online,
adicionar testes automatizados e remover os experimentos soltos.

## GitHub

Depois de alterar o projeto:

```bash
git status
git add .
git commit -m "descricao da mudanca"
git push
```
