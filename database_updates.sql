-- Atualizacoes de banco para os RNF/RN do Doce Controle.
-- Execute no phpMyAdmin ou no MySQL do XAMPP quando o banco estiver ligado.

CREATE DATABASE IF NOT EXISTS doce_controle
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE doce_controle;

ALTER TABLE estoque
    MODIFY preco_unitario DECIMAL(12,6) NOT NULL DEFAULT 0,
    MODIFY quantidade_atual DECIMAL(12,3) NOT NULL DEFAULT 0,
    MODIFY estoque_minimo DECIMAL(12,3) NOT NULL DEFAULT 0;

ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS estoque_baixado TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS nome VARCHAR(120) NOT NULL DEFAULT 'Usuario',
    ADD COLUMN IF NOT EXISTS email VARCHAR(160) NULL,
    ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'ativo',
    ADD COLUMN IF NOT EXISTS plano VARCHAR(20) NOT NULL DEFAULT 'ativo',
    ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL;

ALTER TABLE receitas
    ADD COLUMN IF NOT EXISTS imagem_produto VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS mostrar_cardapio TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS descricao_publica TEXT NULL;

UPDATE users
SET nome = COALESCE(NULLIF(nome_confeitaria, ''), NULLIF(nome, ''), 'Usuario')
WHERE nome = 'Usuario' AND nome_confeitaria IS NOT NULL;
