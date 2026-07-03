CREATE DATABASE IF NOT EXISTS doce_controle
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE doce_controle;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    whatsapp VARCHAR(40) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'ativo',
    plano VARCHAR(20) NOT NULL DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nome VARCHAR(160) NOT NULL,
    whatsapp VARCHAR(40) NOT NULL,
    email VARCHAR(160) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clientes_user_nome (user_id, nome),
    CONSTRAINT fk_clientes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_nome VARCHAR(160) NOT NULL,
    unidade_medida VARCHAR(20) NOT NULL DEFAULT 'un',
    preco_unitario DECIMAL(12,6) NOT NULL DEFAULT 0,
    quantidade_atual DECIMAL(12,3) NOT NULL DEFAULT 0,
    estoque_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estoque_user_nome (user_id, item_nome),
    CONSTRAINT fk_estoque_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nome_receita VARCHAR(180) NOT NULL,
    rendimento_porcoes INT NOT NULL DEFAULT 1,
    preco_venda_sugerido DECIMAL(12,2) NOT NULL DEFAULT 0,
    imagem_produto VARCHAR(255) NULL,
    mostrar_cardapio TINYINT(1) NOT NULL DEFAULT 1,
    descricao_publica TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_receitas_user_nome (user_id, nome_receita),
    CONSTRAINT fk_receitas_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receitas_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    insumo_id INT NOT NULL,
    quantidade_usada DECIMAL(12,3) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_receitas_itens_receita (receita_id),
    INDEX idx_receitas_itens_insumo (insumo_id),
    CONSTRAINT fk_receitas_itens_receita FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    CONSTRAINT fk_receitas_itens_insumo FOREIGN KEY (insumo_id) REFERENCES estoque(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cliente_id INT NOT NULL,
    receita_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    data_entrega DATETIME NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'Pendente',
    valor_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    estoque_baixado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pedidos_user_status (user_id, status),
    INDEX idx_pedidos_cliente (cliente_id),
    INDEX idx_pedidos_receita (receita_id),
    CONSTRAINT fk_pedidos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_pedidos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_pedidos_receita FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historico_precos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estoque_id INT NOT NULL,
    user_id INT NOT NULL,
    preco_compra DECIMAL(12,2) NOT NULL DEFAULT 0,
    quantidade_comprada DECIMAL(12,3) NULL,
    nota TEXT NULL,
    data_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_historico_estoque_data (estoque_id, data_compra),
    CONSTRAINT fk_historico_estoque FOREIGN KEY (estoque_id) REFERENCES estoque(id) ON DELETE CASCADE,
    CONSTRAINT fk_historico_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
