<?php

/**
 * Funções utilitárias compartilhadas da plataforma.
 */

/**
 * Converte uma URL do YouTube em uma URL de incorporação (embed) válida.
 *
 * Aceita os formatos mais comuns e extrai apenas o ID do vídeo (11 caracteres),
 * descartando parâmetros extras como "&list=" ou "&start_radio=" que quebram o embed.
 *
 * Formatos suportados:
 *  - https://www.youtube.com/watch?v=VIDEO_ID
 *  - https://youtu.be/VIDEO_ID
 *  - https://www.youtube.com/shorts/VIDEO_ID
 *  - https://www.youtube.com/embed/VIDEO_ID
 *
 * @param string $url URL informada pela usuária.
 * @return string URL de embed (https://www.youtube.com/embed/VIDEO_ID) ou string vazia se inválida.
 */
function youtubeEmbedUrl($url)
{
    $url = trim((string) $url);

    if ($url === "") {
        return "";
    }

    $videoId = "";

    if (preg_match('~youtu\.be/([A-Za-z0-9_-]{11})~', $url, $m)) {
        // Formato curto: youtu.be/VIDEO_ID
        $videoId = $m[1];
    } elseif (preg_match('~[?&]v=([A-Za-z0-9_-]{11})~', $url, $m)) {
        // Formato padrão: watch?v=VIDEO_ID
        $videoId = $m[1];
    } elseif (preg_match('~youtube\.com/(?:embed|shorts|v)/([A-Za-z0-9_-]{11})~', $url, $m)) {
        // Formatos /embed/, /shorts/ e /v/
        $videoId = $m[1];
    }

    if ($videoId === "") {
        return "";
    }

    return "https://www.youtube.com/embed/" . $videoId;
}

/**
 * Extrai até duas iniciais de um nome ("Luna Araújo" -> "LA").
 *
 * @param string $nome
 * @return string Iniciais em maiúsculas, ou "?" se vazio.
 */
function iniciais($nome)
{
    $nome = trim((string) $nome);

    if ($nome === "") {
        return "?";
    }

    $partes = preg_split('/\s+/', $nome);
    $ini = mb_strtoupper(mb_substr($partes[0], 0, 1));

    if (count($partes) > 1) {
        $ini .= mb_strtoupper(mb_substr($partes[count($partes) - 1], 0, 1));
    }

    return $ini;
}

/**
 * Gera uma cor de fundo determinística (sempre a mesma para o mesmo nome).
 *
 * @param string $nome
 * @return string Cor hexadecimal.
 */
function corAvatar($nome)
{
    $cores = ["#f4564e", "#16b8a8", "#ff8c42", "#e5417e", "#0f766e", "#f7a23b", "#7c5cbf", "#ef5a6e"];
    $soma = 0;
    $nome = (string) $nome;

    for ($i = 0; $i < strlen($nome); $i++) {
        $soma += ord($nome[$i]);
    }

    return $cores[$soma % count($cores)];
}

/**
 * Devolve o HTML do avatar: a foto, se existir, ou um círculo/quadro com as iniciais.
 *
 * @param string|null $foto  Nome do arquivo em uploads/avatars/.
 * @param string      $nome  Nome artístico (para alt e iniciais).
 * @param string      $tipo  "card" (topo de card) ou "retrato" (coluna de perfil).
 * @return string HTML pronto para ecoar.
 */
function avatarHtml($foto, $nome, $tipo = "card")
{
    $alt = htmlspecialchars((string) $nome);
    $existe = !empty($foto) && file_exists(__DIR__ . "/../uploads/avatars/" . $foto);

    if ($existe) {
        $src = "uploads/avatars/" . htmlspecialchars($foto);
        if ($tipo === "card") {
            $classe = "card-img-top";
        } elseif ($tipo === "capa") {
            $classe = "avatar-capa";
        } else {
            $classe = "img-fluid rounded mb-3";
        }
        return '<img src="' . $src . '" class="' . $classe . '" alt="' . $alt . '">';
    }

    $ini = htmlspecialchars(iniciais($nome));
    $cor = corAvatar($nome);
    $classeBase = "avatar-placeholder d-flex align-items-center justify-content-center";
    if ($tipo === "card") {
        $classe = $classeBase . " card-img-top";
    } elseif ($tipo === "capa") {
        $classe = $classeBase . " avatar-capa";
    } else {
        $classe = $classeBase . " avatar-retrato rounded mb-3";
    }

    return '<div class="' . $classe . '" style="background:' . $cor . '">' . $ini . '</div>';
}

/**
 * Valida um arquivo de imagem enviado: erro de upload, extensão, tamanho e conteúdo real.
 *
 * @param array $file     Item de $_FILES.
 * @param int   $maxBytes Tamanho máximo permitido em bytes.
 * @return string Mensagem de erro, ou "" se válido.
 */
function validarImagemUpload($file, $maxBytes = 5242880)
{
    $permitidas = ["jpg", "jpeg", "png"];

    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return "Erro ao enviar a imagem. Tente novamente.";
    }

    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidas)) {
        return "A imagem deve ser JPG, JPEG ou PNG.";
    }

    if ($file["size"] > $maxBytes) {
        return "A imagem é maior que o tamanho máximo permitido.";
    }

    // Verifica o conteúdo real do arquivo (não confia só na extensão).
    $info = @getimagesize($file["tmp_name"]);
    $tiposReais = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    if ($info === false || !in_array($info[2], $tiposReais)) {
        return "O arquivo enviado não é uma imagem JPG ou PNG válida.";
    }

    return "";
}

/**
 * Card padrão de artista (componente único usado em home, diretório, etc.).
 *
 * @param array $a    Linha de usuarios (id, nome_artistico, foto_perfil, area_atuacao,
 *                    cidade_atual, cidade_origem, disponivel_contratacao, biografia).
 * @param array $opts ['bio' => bool, 'footer' => string HTML]
 * @return string HTML do card.
 */
function cardArtistaHtml($a, $opts = [])
{
    $bio = $opts["bio"] ?? true;
    $id = (int) $a["id"];
    $nome = htmlspecialchars($a["nome_artistico"]);
    $area = htmlspecialchars(($a["area_atuacao"] ?? "") ?: "Artista Circense");
    $cidade = htmlspecialchars(($a["cidade_atual"] ?? "") ?: (($a["cidade_origem"] ?? "") ?: "Brasil"));
    $footer = $opts["footer"] ?? '<a href="artista.php?id=' . $id . '" class="btn btn-circo w-100">Ver perfil</a>';

    ob_start();
    ?>
    <div class="card card-artista h-100">
        <div class="card-cover">
            <?= avatarHtml($a["foto_perfil"] ?? "", $a["nome_artistico"], "card") ?>
            <div class="card-cover-caption">
                <h5><?= $nome ?></h5>
                <span class="badge bg-light text-dark"><?= $area ?></span>
                <?php if (!empty($a["disponivel_contratacao"])): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Disponível</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small <?= $bio ? "mb-2" : "mb-0" ?>">
                <i class="bi bi-geo-alt"></i> <?= $cidade ?>
            </p>
            <?php if ($bio && !empty($a["biografia"])): ?>
                <p class="small mb-0"><?= htmlspecialchars(mb_substr($a["biografia"], 0, 100)) ?><?= mb_strlen($a["biografia"]) > 100 ? "..." : "" ?></p>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-white"><?= $footer ?></div>
    </div>
    <?php
    return ob_get_clean();
}

