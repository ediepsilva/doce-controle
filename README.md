# Doce Controle

Aplicativo PHP para controle de receitas, estoque, clientes, pedidos e cardapio de uma confeitaria.

## Como usar localmente

1. Coloque a pasta do projeto em `C:\xampp\htdocs\doce-app`.
2. Inicie o Apache e o MySQL pelo XAMPP.
3. Abra o phpMyAdmin e execute o arquivo `schema.sql` para criar o banco `doce_controle`.
4. Se o projeto ja tinha tabelas antigas, execute tambem `database_updates.sql`.
5. Acesse `http://localhost/doce-app/` no navegador.
6. Crie sua conta na tela de cadastro e entre com e-mail e senha.

Para bancos criados antes do login real, execute `database_updates.sql` para adicionar os campos de usuario mais recentes, incluindo celular/WhatsApp e senha.

## Banco online / producao

Para o sistema ficar disponivel sem depender do MySQL do XAMPP, hospede o banco em um servidor MySQL online e configure estas variaveis de ambiente no servidor PHP:

```env
DB_HOST=host-do-banco
DB_PORT=3306
DB_NAME=doce_controle
DB_USER=usuario_do_banco
DB_PASS=senha_do_banco
```

Sem essas variaveis, o projeto continua usando o banco local do XAMPP:

```env
DB_HOST=localhost
DB_NAME=doce_controle
DB_USER=root
DB_PASS=
```

Depois de criar o banco online, execute `schema.sql` nele. Se estiver migrando dados locais, exporte o banco pelo phpMyAdmin do XAMPP e importe no banco online.

Em hospedagens que nao permitem configurar variaveis de ambiente pelo painel, crie um arquivo `.env` na raiz do projeto usando `.env.example` como modelo:

```env
DB_HOST=host-do-banco
DB_PORT=3306
DB_NAME=nome_do_banco
DB_USER=usuario_do_banco
DB_PASS=senha_do_banco
APP_DEBUG=0
APP_URL=https://seu-dominio.example
```

O arquivo `.env` nao deve ser enviado para o GitHub. Ele fica somente no servidor, com a senha real do banco.

`APP_URL` deve conter o endereco publico do aplicativo, sem barra no final. Ele e usado para criar links seguros de redefinicao de senha. O servidor PHP tambem precisa estar configurado para enviar e-mails com a funcao `mail()`.

## Modulos

- Dashboard
- Receitas
- Estoque
- Clientes
- Pedidos
- Cozinha
- Cardapio publico
- Perfil da confeitaria

## Fluxo principal

1. Cadastre uma conta e entre no sistema.
2. Ajuste o perfil da confeitaria com nome, WhatsApp e logo do cardapio.
3. Complete o estoque com ingredientes, quantidades compradas e valores pagos.
4. Crie receitas e adicione ingredientes para calcular custos automaticamente.
5. Cadastre clientes e registre encomendas.
6. Acompanhe a fila de producao na tela de cozinha.
7. Edite, exclua ou avance o status dos pedidos no painel de pedidos.
8. Divulgue o cardapio publico da conta pelo link `cardapio.php?user_id=SEU_ID`.

O link publico do cardapio tambem aparece no dashboard depois do login.

## GitHub

Depois de alterar o projeto:

```bash
git status
git add .
git commit -m "descricao da mudanca"
git push
```
