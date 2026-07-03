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

## Modulos

- Dashboard
- Receitas
- Estoque
- Clientes
- Pedidos
- Cardapio publico

## GitHub

Depois de alterar o projeto:

```bash
git status
git add .
git commit -m "descricao da mudanca"
git push
```
