-- ============================================================
-- Fase 3 — Evolução: tabela de contato e consentimento LGPD
-- Plataforma Mulheres no Circo
-- Rodar em banco já existente. Idempotente.
-- ============================================================

CREATE TABLE IF NOT EXISTS `mensagens_contato` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `assunto` VARCHAR(200) DEFAULT NULL,
  `mensagem` TEXT NOT NULL,
  `lida` TINYINT(1) NOT NULL DEFAULT 0,
  `criado_em` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consentimento LGPD em usuarios (adiciona só se ainda não existir).
SET @col_aceite := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'aceite_termos'
);
SET @sql := IF(@col_aceite = 0,
  'ALTER TABLE `usuarios`
     ADD COLUMN `aceite_termos` TINYINT(1) NOT NULL DEFAULT 0,
     ADD COLUMN `aceite_termos_em` DATETIME DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
