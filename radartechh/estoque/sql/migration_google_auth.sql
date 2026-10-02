-- =============================================================
-- Migração: adiciona suporte a login com Google
-- Execute no phpMyAdmin ou via terminal MySQL
-- =============================================================

USE `estoque_papelaria`;

-- Adiciona colunas para armazenar o ID e e-mail do Google
ALTER TABLE `usuarios`
    ADD COLUMN `google_sub`   VARCHAR(255) DEFAULT NULL AFTER `ativo`,
    ADD COLUMN `google_email` VARCHAR(255) DEFAULT NULL AFTER `google_sub`,
    ADD UNIQUE KEY `uq_google_sub` (`google_sub`);
