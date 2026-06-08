-- ============================================================
-- Padronização de área de atuação para as categorias do catálogo
-- Plataforma Mulheres no Circo. Idempotente.
-- ============================================================

UPDATE usuarios SET area_atuacao = 'Acrobacias'
    WHERE area_atuacao = 'Acrobacia';

UPDATE usuarios SET area_atuacao = 'Manipulação de Objetos'
    WHERE area_atuacao = 'Malabares';

UPDATE usuarios SET area_atuacao = 'Aéreos'
    WHERE area_atuacao IN ('Trapézio', 'Tecido Acrobático');
