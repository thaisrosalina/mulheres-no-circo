<?php
require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/helpers.php";

protegerPagina(); // só logados (produtores, artistas, admin) baixam a ficha

$id = (int) ($_GET["id"] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([":id" => $id]);
$a = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$a) {
    die("Artista não encontrada.");
}

$stmtP = $pdo->prepare("SELECT titulo, categoria, conteudo FROM posts WHERE usuario_id = :id ORDER BY criado_em DESC");
$stmtP->execute([":id" => $id]);
$posts = $stmtP->fetchAll(PDO::FETCH_ASSOC);

$especialidades = array_filter(array_map("trim", explode(",", $a["especialidades"] ?? "")));
$fotos = array_filter(array_map("trim", preg_split('/[\r\n]+/', $a["fotos_divulgacao"] ?? "")));
$gerado = date("d/m/Y");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha — <?= htmlspecialchars($a["nome_artistico"]) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=13">
    <style>
        body { background: #fff; }
        .ficha { max-width: 820px; margin: 0 auto; }
        .ficha-capa { background: linear-gradient(125deg, var(--mnc-vinho), var(--mnc-turquesa)); color: #fff; border-radius: 14px; padding: 1.6rem; }
        .ficha-capa h1 { color: #fff; }
        .ficha-avatar img, .ficha-avatar .avatar-placeholder { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; font-size: 2.6rem; }
        @media print {
            .d-print-none { display: none !important; }
            .ficha-capa { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            a { text-decoration: none !important; color: inherit !important; }
            body { font-size: 12px; }
        }
    </style>
</head>
<body>
<div class="container my-4 ficha">

    <div class="d-print-none d-flex justify-content-between mb-3">
        <a href="javascript:history.back()" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
        <button onclick="window.print()" class="btn btn-circo btn-sm">
            <i class="bi bi-download"></i> Baixar / Imprimir (PDF)
        </button>
    </div>

    <div class="ficha-capa mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="ficha-avatar"><?= avatarHtml($a["foto_perfil"] ?? "", $a["nome_artistico"], "capa") ?></div>
            <div>
                <h1 class="mb-1"><?= htmlspecialchars($a["nome_artistico"]) ?></h1>
                <?php if (!empty($a["area_atuacao"])): ?>
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($a["area_atuacao"]) ?></span>
                <?php endif; ?>
                <?php if (!empty($a["disponivel_contratacao"])): ?>
                    <span class="badge" style="background:var(--mnc-amarelo);color:#2b2533;">Disponível para contratação</span>
                <?php endif; ?>
                <p class="mb-0 mt-2"><i class="bi bi-geo-alt"></i>
                    Origem: <?= htmlspecialchars($a["cidade_origem"] ?: "—") ?> ·
                    Atual: <?= htmlspecialchars($a["cidade_atual"] ?: "—") ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($a["biografia"])): ?>
        <h2 class="h5">Biografia</h2>
        <p><?= nl2br(htmlspecialchars($a["biografia"])) ?></p>
    <?php endif; ?>

    <?php if ($especialidades): ?>
        <h2 class="h5 mt-3">Modalidades e habilidades</h2>
        <p><?= htmlspecialchars(implode(" · ", $especialidades)) ?></p>
    <?php endif; ?>

    <?php if (!empty($a["necessidades_tecnicas"])): ?>
        <h2 class="h5 mt-3">Necessidades técnicas</h2>
        <p><?= nl2br(htmlspecialchars($a["necessidades_tecnicas"])) ?></p>
    <?php endif; ?>

    <?php if ($posts): ?>
        <h2 class="h5 mt-3">Trabalhos</h2>
        <ul>
            <?php foreach ($posts as $p): ?>
                <li><strong><?= htmlspecialchars($p["titulo"]) ?></strong>
                    <?php if ($p["categoria"]): ?>(<?= htmlspecialchars($p["categoria"]) ?>)<?php endif; ?>
                    — <?= htmlspecialchars(mb_substr($p["conteudo"], 0, 160)) ?><?= mb_strlen($p["conteudo"]) > 160 ? "..." : "" ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2 class="h5 mt-3">Contato e links</h2>
    <ul class="list-unstyled">
        <?php foreach (["instagram" => "Instagram", "youtube" => "YouTube", "linkedin" => "LinkedIn", "site_oficial" => "Site", "mapa_cultura" => "Mapa da Cultura"] as $campo => $rot): ?>
            <?php if (!empty($a[$campo])): ?>
                <li><strong><?= $rot ?>:</strong> <?= htmlspecialchars($a[$campo]) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($fotos as $i => $url): ?>
            <li><strong>Fotos de divulgação <?= $i + 1 ?>:</strong> <?= htmlspecialchars($url) ?></li>
        <?php endforeach; ?>
    </ul>

    <hr>
    <p class="text-muted small">
        Ficha gerada em <?= $gerado ?> pela plataforma <strong>Mulheres no Circo</strong> — mulheresnocirco.
    </p>
</div>
</body>
</html>
