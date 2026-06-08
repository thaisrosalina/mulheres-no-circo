<?php

/**
 * Catálogo de modalidades/habilidades circenses e tipos de serviço.
 * Ponto único de edição — usado no cadastro de perfil e de trabalhos.
 */

// Modalidades e Habilidades Circenses, agrupadas por categoria.
$MODALIDADES_CIRCENSES = [
    "Aéreos" => [
        "Tecido acrobático", "Trapézio fixo", "Trapézio voador", "Trapézio dance",
        "Lira aérea", "Corda lisa", "Faixas aéreas", "Straps", "Rede aérea",
        "Bambu aéreo", "Pole aéreo", "Cabelo suspenso", "Suspensão corporal", "Quadrante aéreo",
    ],
    "Acrobacias" => [
        "Acrobacia de solo", "Acrobacia em dupla", "Acrobacia em grupo", "Mão a mão",
        "Portagem acrobática", "Banquine", "Pirâmides humanas", "Tumbling", "Parada de mãos",
        "Contorção", "Equilíbrio corporal", "Acrodança", "Acroyoga", "Acrobacia cênica",
    ],
    "Equilíbrio" => [
        "Arame", "Arame baixo", "Arame alto", "Slackline", "Perna de pau", "Monociclo",
        "Rola-rola", "Bola de equilíbrio", "Escada livre", "Bicicleta acrobática", "Equilíbrio sobre objetos",
    ],
    "Manipulação de Objetos" => [
        "Malabares", "Claves", "Bolas", "Aros", "Diabolô", "Devil stick", "Swing poi",
        "Bastão", "Bastão de fogo", "Leques", "Bambolê", "Chapéus",
        "Manipulação de tecido", "Manipulação de objetos cênicos",
    ],
    "Palhaçaria e Comicidade" => [
        "Palhaçaria", "Palhaçaria feminina", "Palhaçaria clássica", "Palhaçaria contemporânea",
        "Comicidade física", "Bufonaria", "Mímica", "Máscara", "Improvisação",
        "Interação com público", "Teatro físico", "Clown",
    ],
    "Fogo" => [
        "Malabares com fogo", "Bastão de fogo", "Leques de fogo", "Poi de fogo",
        "Bambolê de fogo", "Cuspir fogo", "Engolir fogo", "Dança com fogo", "Performance pirotécnica",
    ],
    "Mágica e Ilusionismo" => [
        "Mágica", "Ilusionismo", "Prestidigitação", "Cartomagia", "Mentalismo",
        "Escapismo", "Manipulação mágica", "Grandes ilusões",
    ],
    "Corpo, Dança e Cena" => [
        "Dança circense", "Teatro circense", "Teatro físico", "Performance", "Dramaturgia circense",
        "Expressão corporal", "Criação cênica", "Direção circense", "Preparação corporal",
        "Dança contemporânea aplicada ao circo",
    ],
    "Circo Social e Educação" => [
        "Circo social", "Oficinas circenses", "Pedagogia do circo", "Circo para infância",
        "Circo inclusivo", "Circo terapêutico", "Mediação cultural", "Formação artística", "Educação popular",
    ],
    "Produção e Técnica" => [
        "Direção artística", "Produção cultural", "Técnica de rigging", "Segurança em aéreos",
        "Montagem de equipamentos", "Iluminação cênica", "Sonoplastia", "Cenografia", "Figurino",
        "Maquiagem artística", "Criação de espetáculo", "Elaboração de projetos culturais",
    ],
];

// Categorias (grupos) — úteis para o campo "Área de atuação".
$AREAS_ATUACAO = array_keys($MODALIDADES_CIRCENSES);

// Tipos de Serviço oferecidos na plataforma (categoria do trabalho).
$TIPOS_SERVICO = [
    "Performance", "Espetáculo", "Oficina", "Palestra", "Show", "Intervenção urbana",
    "Vivência", "Residência artística", "Consultoria", "Direção artística",
    "Preparação corporal", "Mediação cultural", "Formação", "Curadoria", "Produção cultural",
];

/**
 * Lista achatada (sem grupos) de todas as modalidades — para validação.
 *
 * @return string[]
 */
function todasModalidades()
{
    global $MODALIDADES_CIRCENSES;
    $todas = [];
    foreach ($MODALIDADES_CIRCENSES as $itens) {
        foreach ($itens as $item) {
            $todas[$item] = true; // chave evita duplicatas entre grupos
        }
    }
    return array_keys($todas);
}
