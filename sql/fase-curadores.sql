-- ============================================================
-- Fase G — Curadores(as) / Área do Curador
-- Plataforma Mulheres no Circo. Idempotente (MariaDB).
-- ============================================================

-- Novos campos da artista (material para curadoria) + dados do curador.
ALTER TABLE `usuarios`
    ADD COLUMN IF NOT EXISTS `necessidades_tecnicas` TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `fotos_divulgacao` TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `organizacao` VARCHAR(150) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `telefone` VARCHAR(40) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `quer_novidades` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `canal_novidades` VARCHAR(20) DEFAULT NULL;

-- Curadoria salva (artistas favoritadas pelo curador).
CREATE TABLE IF NOT EXISTS `curadoria_salva` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `curador_id` INT(11) NOT NULL,
    `artista_id` INT(11) NOT NULL,
    `criado_em` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_cur_art` (`curador_id`, `artista_id`),
    KEY `artista_id` (`artista_id`),
    CONSTRAINT `cs_curador` FOREIGN KEY (`curador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `cs_artista` FOREIGN KEY (`artista_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
