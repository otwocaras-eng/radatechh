-- =============================================================
-- SISTEMA DE CONTROLE DE ESTOQUE - PAPELARIA
-- Schema completo com tabelas, índices e chaves estrangeiras
-- Compatível com MySQL 5.7+ / MariaDB 10.3+
-- =============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -------------------------------------------------------------
-- Banco de dados
-- -------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `estoque_papelaria`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `estoque_papelaria`;

-- -------------------------------------------------------------
-- Tabela: usuarios
-- Armazena os usuários do sistema (admin e vendedor)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome`       VARCHAR(100)  NOT NULL,
    `login`      VARCHAR(50)   NOT NULL UNIQUE,
    `senha_hash` VARCHAR(255)  NOT NULL,
    `perfil`     ENUM('admin','vendedor') NOT NULL DEFAULT 'vendedor',
    `ativo`      TINYINT(1)   NOT NULL DEFAULT 1,
    `criado_em`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_usuarios_login` (`login`),
    INDEX `idx_usuarios_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabela: categorias
-- Categorias dos produtos (ex.: Canetas, Cadernos, Papéis)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabela: fornecedores
-- Fornecedores/distribuidores de produtos
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fornecedores` (
    `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome`      VARCHAR(150) NOT NULL,
    `telefone`  VARCHAR(20)  DEFAULT NULL,
    `email`     VARCHAR(150) DEFAULT NULL,
    `ativo`     TINYINT(1)   NOT NULL DEFAULT 1,
    `criado_em` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_fornecedores_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabela: produtos
-- Catálogo de produtos da papelaria
-- Exclusão lógica via coluna "ativo" (nunca DELETE físico)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome`           VARCHAR(200)   NOT NULL,
    `codigo_barras`  VARCHAR(50)    DEFAULT NULL,
    `categoria_id`   INT UNSIGNED   DEFAULT NULL,
    `marca`          VARCHAR(100)   DEFAULT NULL,
    `preco_custo`    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `preco_venda`    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `estoque_atual`  INT            NOT NULL DEFAULT 0,
    `estoque_minimo` INT            NOT NULL DEFAULT 5,
    `ativo`          TINYINT(1)     NOT NULL DEFAULT 1,
    `criado_em`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_codigo_barras` (`codigo_barras`),
    INDEX `idx_produtos_nome`       (`nome`),
    INDEX `idx_produtos_categoria`  (`categoria_id`),
    INDEX `idx_produtos_ativo`      (`ativo`),
    INDEX `idx_produtos_estoque`    (`estoque_atual`, `estoque_minimo`),
    CONSTRAINT `fk_produto_categoria`
        FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabela: movimentacoes
-- Histórico completo de todas as entradas e saídas de estoque
-- O estoque_atual do produto é atualizado via transação junto com
-- a inserção desta tabela, garantindo consistência.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `movimentacoes` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `produto_id`     INT UNSIGNED   NOT NULL,
    `tipo`           ENUM('entrada','saida') NOT NULL,
    `quantidade`     INT            NOT NULL,
    `motivo`         VARCHAR(100)   DEFAULT NULL,
    `custo_unitario` DECIMAL(10,2)  DEFAULT NULL,
    `fornecedor_id`  INT UNSIGNED   DEFAULT NULL,
    `usuario_id`     INT UNSIGNED   NOT NULL,
    `observacao`     TEXT           DEFAULT NULL,
    `criado_em`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mov_produto`    (`produto_id`),
    INDEX `idx_mov_tipo`       (`tipo`),
    INDEX `idx_mov_criado_em`  (`criado_em`),
    INDEX `idx_mov_usuario`    (`usuario_id`),
    CONSTRAINT `fk_mov_produto`
        FOREIGN KEY (`produto_id`)    REFERENCES `produtos`     (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_mov_fornecedor`
        FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_mov_usuario`
        FOREIGN KEY (`usuario_id`)    REFERENCES `usuarios`     (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
