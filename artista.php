<?php
require_once "config/database.php";
require_once "includes/helpers.php";

$id = (int) ($_GET["id"] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$artista = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artista) {
    die("Artista não encontrada.");
}

$stmtPosts = $pdo->prepare("
    SELECT *
    FROM posts
    WHERE usuario_id = :usuario_id
    ORDER BY criado_em DESC
");

$stmtPosts->execute([
    ":usuario_id" => $id
]);

$posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

// Transforma a lista de especialidades (texto separado por vírgulas) em tags.
$especialidades = array_filter(array_map("trim", explode(",", $artista["especialidades"] ?? "")));

include "includes/header.php";
?>

<div class="container my-5">

    <a href="diretorio.php" class="btn btn-link ps-0 mb-3">
        <i class="bi bi-arrow-left"></i> Voltar ao diretório
    </a>

    <!-- Capa -->
    <div class="perfil-capa mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-auto text-center">
                <?= avatarHtml($artista["foto_perfil"] ?? "", $artista["nome_artistico"], "capa") ?>
            </div>
            <div class="col">
                <h1 class="mb-2"><?= htmlspecialchars($artista["nome_artistico"]) ?></h1>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <?php if (!empty($artista["area_atuacao"])): ?>
                        <span class="badge bg-light text-dark"><?= htmlspecialchars($artista["area_atuacao"]) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($artista["disponivel_contratacao"])): ?>
                        <span class="badge" style="background: var(--mnc-amarelo); color: #2b2533;">
                            <i class="bi bi-check-circle-fill"></i> Disponível para contratação
                        </span>
                    <?php endif; ?>
                </div>
                <p class="mb-0">
                    <i class="bi bi-geo-alt"></i>
                    Origem: <?= htmlspecialchars($artista["cidade_origem"] ?: "Não informado") ?>
                    &middot;
                    Atualmente em: <?= htmlspecialchars($artista["cidade_atual"] ?: "Não informado") ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="row g-4">
        <div class="col-lg-8">
            <h5>Biografia</h5>
            <p><?= nl2br(htmlspecialchars($artista["biografia"] ?: "Biografia não informada.")) ?></p>

            <?php if (count($especialidades) > 0): ?>
                <h5 class="mt-4">Especialidades</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($especialidades as $esp): ?>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($esp) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Coluna lateral: contratação + ficha + conexões -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($artista["disponivel_contratacao"])): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3">
                            <i class="bi bi-check-circle-fill"></i> Disponível para contratação
                        </div>
                    <?php endif; ?>

                    <a href="ficha-artista.php?id=<?= (int) $artista["id"] ?>" class="btn btn-circo w-100 mb-2" target="_blank">
                        <i class="bi bi-file-earmark-person"></i> Baixar ficha (PDF)
                    </a>
                    <a href="contato.php" class="btn btn-outline-dark w-100 mb-3">
                        <i class="bi bi-envelope"></i> Entrar em contato
                    </a>

                    <?php
                    $conexoes = [
                        "site_oficial" => ["bi-globe", "Site Oficial"],
                        "instagram" => ["bi-instagram", "Instagram"],
                        "youtube" => ["bi-youtube", "YouTube"],
                        "linkedin" => ["bi-linkedin", "LinkedIn"],
                        "mapa_cultura" => ["bi-geo-alt-fill", "Mapa da Cultura"],
                    ];
                    $temConexao = false;
                    foreach ($conexoes as $campo => $info) {
                        if (!empty($artista[$campo])) { $temConexao = true; break; }
                    }
                    ?>
                    <?php if ($temConexao): ?>
                        <h6 class="text-muted text-uppercase small mb-2">Conexões</h6>
                        <div class="d-grid gap-2">
                            <?php foreach ($conexoes as $campo => $info): ?>
                                <?php if (!empty($artista[$campo])): ?>
                                    <a href="<?= htmlspecialchars($artista[$campo]) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-dark">
                                        <i class="bi <?= $info[0] ?>"></i> <?= $info[1] ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="container my-5">
    <h2 class="secao-titulo mb-4">Trabalhos</h2>

    <?php if (count($posts) === 0): ?>
        <div class="alert alert-light border text-center py-5">
            <i class="bi bi-collection fs-1 d-block mb-2 text-muted"></i>
            Esta artista ainda não publicou trabalhos.
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm card-artista">
                    <?php if (!empty($post["imagem"]) && file_exists(__DIR__ . "/uploads/trabalhos/" . $post["imagem"])): ?>
                        <img
                            src="uploads/trabalhos/<?= htmlspecialchars($post["imagem"]) ?>"
                            class="card-img-top"
                            alt="<?= htmlspecialchars($post["titulo"]) ?>"
                        >
                    <?php else: ?>
                        <div class="avatar-placeholder card-img-top d-flex align-items-center justify-content-center" style="background:<?= corAvatar($post["titulo"]) ?>">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (!empty($post["categoria"])): ?>
                            <span class="badge bg-dark mb-2"><?= htmlspecialchars($post["categoria"]) ?></span>
                        <?php endif; ?>

                        <h4><?= htmlspecialchars($post["titulo"]) ?></h4>

                        <p>
                            <?= htmlspecialchars(mb_substr($post["conteudo"], 0, 120)) ?><?= mb_strlen($post["conteudo"]) > 120 ? "..." : "" ?>
                        </p>
                    </div>

                    <div class="card-footer bg-white">
                        <button
                            class="btn btn-circo w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#trabalhoPub<?= $post["id"] ?>"
                        >
                            <i class="bi bi-eye"></i> Ver Trabalho
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="trabalhoPub<?= $post["id"] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($post["titulo"]) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <?php if (!empty($post["categoria"])): ?>
                                <span class="badge bg-dark mb-3"><?= htmlspecialchars($post["categoria"]) ?></span>
                            <?php endif; ?>

                            <?php if (!empty($post["imagem"]) && file_exists(__DIR__ . "/uploads/trabalhos/" . $post["imagem"])): ?>
                                <img src="uploads/trabalhos/<?= htmlspecialchars($post["imagem"]) ?>" class="img-fluid rounded mb-3">
                            <?php endif; ?>

                            <p><?= nl2br(htmlspecialchars($post["conteudo"])) ?></p>

                            <?php $embed = youtubeEmbedUrl($post["video_youtube"] ?? ""); ?>
                            <?php if ($embed !== ""): ?>
                                <div class="ratio ratio-16x9 my-3">
                                    <iframe src="<?= htmlspecialchars($embed) ?>" allowfullscreen></iframe>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($post["arquivo_pdf"]) && file_exists(__DIR__ . "/uploads/apresentacoes/" . $post["arquivo_pdf"])): ?>
                                <a href="download.php?file=<?= urlencode($post["arquivo_pdf"]) ?>" class="btn btn-dark">
                                    <i class="bi bi-file-earmark-pdf"></i> Baixar apresentação completa
                                </a>
                            <?php endif; ?>

                            <p class="text-muted mt-3 mb-0">
                                <small>Publicado em <?= date("d/m/Y", strtotime($post["criado_em"])) ?></small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
