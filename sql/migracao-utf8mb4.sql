-- ============================================================
-- Migração de charset: latin1 -> utf8mb4
-- Plataforma Mulheres no Circo
--
-- Use este script em um banco JÁ EXISTENTE (ex.: seu ambiente local)
-- para converter o banco e as tabelas para utf8mb4 sem perder dados.
--
-- Como rodar:
--   - phpMyAdmin: selecione o banco "mulheres_no_circo" > aba SQL > cole e execute.
--   - CLI: mysql -u root mulheres_no_circo < sql/migracao-utf8mb4.sql
--
-- Observação: os acentos do português cabem em latin1, então a conversão
-- com CONVERT TO CHARACTER SET preserva o conteúdo corretamente.
-- ============================================================

ALTER DATABASE `mulheres_no_circo`
    CHARACTER SET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `usuarios`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `posts`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `logs_acesso`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `tentativas_login`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
