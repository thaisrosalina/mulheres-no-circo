<?php
require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/csrf.php";
require_once "includes/helpers.php";
require_once "includes/modalidades.php";

protegerCurador();

$curador_id = (int) $_SESSION["usuario_id"];

// ----- Ações: salvar / remover da curadoria (POST + CSRF) -----
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF($_POST["csrf_token"] ?? "")) {
        die("Token CSRF inválido.");
    }
    $acao = $_POST["acao"] ?? "";
    $artista_id = (int) ($_POST["artista_id"] ?? 0);

    if ($acao === "salvar" && $artista_id > 0) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO curadoria_salva (curador_id, artista_id) VALUES (:c, :a)");
        $stmt->execute([":c" => $curador_id, ":a" => $artista_id]);
    } elseif ($acao === "remover" && $artista_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM curadoria_salva WHERE curador_id = :c AND artista_id = :a");
        $stmt->execute([":c" => $curador_id, ":a" => $artista_id]);
    }

    $voltar = ($_POST["aba"] ?? "") === "curadoria" ? "?aba=curadoria" : "";
    header("Location: area-curador.php" . $voltar);
    exit;
}

// ----- Aba e filtros -----
$aba = (($_GET["aba"] ?? "") === "curadoria") ? "curadoria" : "artistas";
$busca = trim($_GET["busca"] ?? "");
$area = trim($_GET["area"] ?? "");

$where = ["u.disponivel_contratacao = 1", "u.role <> 'curador'"];
$params = [];
if ($busca !== "") {
    $where[] = "(u.nome_artistico LIKE :b OR u.area_atuacao LIKE :b OR u.especialidades LIKE :b)";
    $params[":b"] = "%$busca%";
}
if ($area !== "") {
    $where[] = "u.area_atuacao = :a";
    $params[":a"] = $area;
}
if ($aba === "curadoria") {
    $where[] = "u.id IN (SELECT artista_id FROM curadoria_salva WHERE curador_id = :cid)";
    $params[":cid"] = $curador_id;
}

$sql = "SELECT u.* FROM usuarios u WHERE " . implode(" AND ", $where) . " ORDER BY u.nome_artistico";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Conjunto de salvas (para marcar a bandeirinha) e contagem.
$salvasIds = $pdo->prepare("SELECT artista_id FROM curadoria_salva WHERE curador_id = :c");
$salvasIds->execute([":c" => $curador_id]);
$salvas = array_map("intval", $salvasIds->fetchAll(PDO::FETCH_COLUMN));
$totalSalvas = count($salvas);

// PDFs (apresentações) das artistas listadas.
$pdfsPorArtista = [];
$ids = array_map(fn($a) => (int) $a["id"], $artistas);
if ($ids) {
    $in = implode(",", $ids);
    $pq = $pdo->query("SELECT usuario_id, titulo, arquivo_pdf FROM posts
                       WHERE arquivo_pdf IS NOT NULL AND arquivo_pdf <> '' AND usuario_id IN ($in)");
    foreach ($pq->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pdfsPorArtista[$p["usuario_id"]][] = $p;
    }
}

$csrf = gerarCSRF();
include "includes/header.php";
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-2">
        <div>
            <p class="contato-eyebrow">Área do Produtor(a) / Programador</p>
            <h1 class="display-6 mb-0">Olá, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?></h1>
        </div>
    </div>
    <p class="text-muted">Navegue pelas artistas disponíveis, baixe materiais e monte sua curadoria.</p>

    <ul class="nav nav-tabs mt-3">
        <li class="nav-item">
            <a class="nav-link <?= $aba === "artistas" ? "active" : "" ?>" href="area-curador.php">
                Artistas disponíveis
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $aba === "curadoria" ? "active" : "" ?>" href="area-curador.php?aba=curadoria">
                <i class="bi bi-bookmark-fill"></i> Minha curadoria
                <?php if ($totalSalvas): ?><span class="badge bg-circo ms-1"><?= $totalSalvas ?></span><?php endif; ?>
            </a>
        </li>
    </ul>

    <div class="border border-top-0 p-3 p-md-4">

        <?php if ($aba === "artistas"): ?>
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-5">
                    <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, especialidade..."
                        value="<?= htmlspecialchars($busca) ?>">
                </div>
                <div class="col-md-4">
                    <select name="area" class="form-select">
                        <option value="">Todas as áreas</option>
                        <?php foreach ($AREAS_ATUACAO as $a): ?>
                            <option value="<?= htmlspecialchars($a) ?>" <?= $area === $a ? "selected" : "" ?>><?= htmlspecialchars($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-circo"><i class="bi bi-search"></i> Filtrar</button>
                </div>
            </form>
        <?php endif; ?>

        <p class="text-muted"><?= count($artistas) ?> artista(s)<?= $aba === "curadoria" ? " na sua curadoria" : " disponível(is)" ?>.</p>

        <div class="row g-4">
            <?php if (count($artistas) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-light border text-center py-5">
                        <i class="bi <?= $aba === "curadoria" ? "bi-bookmark" : "bi-people" ?> fs-1 d-block mb-2 text-muted"></i>
                        <?= $aba === "curadoria"
                            ? "Você ainda não salvou artistas. Use a bandeirinha para montar sua curadoria."
                            : "Nenhuma artista disponível com esses filtros." ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($artistas as $a):
                $salva = in_array((int) $a["id"], $salvas, true);
                $pdfs = $pdfsPorArtista[$a["id"]] ?? [];
                $fotos = array_filter(array_map("trim", preg_split('/[\r\n]+/', $a["fotos_divulgacao"] ?? "")));
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-artista h-100">
                        <div class="card-cover">
                            <?= avatarHtml($a["foto_perfil"] ?? "", $a["nome_artistico"], "card") ?>
                            <div class="card-cover-caption">
                                <h5><?= htmlspecialchars($a["nome_artistico"]) ?></h5>
                                <?php if (!empty($a["area_atuacao"])): ?>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($a["area_atuacao"]) ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Bandeirinha salvar -->
                            <form method="POST" class="curadoria-flag">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="acao" value="<?= $salva ? "remover" : "salvar" ?>">
                                <input type="hidden" name="artista_id" value="<?= (int) $a["id"] ?>">
                                <input type="hidden" name="aba" value="<?= $aba ?>">
                                <button type="submit" class="btn-flag <?= $salva ? "ativo" : "" ?>"
                                    title="<?= $salva ? "Remover da curadoria" : "Salvar na curadoria" ?>">
                                    <i class="bi <?= $salva ? "bi-bookmark-fill" : "bi-bookmark" ?>"></i>
                                </button>
                            </form>
                        </div>

                        <div class="card-body">
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt"></i>
                                <?= htmlspecialchars($a["cidade_atual"] ?: ($a["cidade_origem"] ?: "Brasil")) ?>
                            </p>

                            <button class="btn btn-sm btn-outline-dark w-100 mb-2" type="button"
                                data-bs-toggle="collapse" data-bs-target="#mat<?= (int) $a["id"] ?>">
                                <i class="bi bi-folder2-open"></i> Materiais
                            </button>

                            <div class="collapse" id="mat<?= (int) $a["id"] ?>">
                                <?php if ($pdfs): ?>
                                    <p class="small fw-bold mb-1 mt-2">Apresentações (PDF)</p>
                                    <?php foreach ($pdfs as $p): if (file_exists(__DIR__ . "/uploads/apresentacoes/" . $p["arquivo_pdf"])): ?>
                                        <a class="d-block small mb-1"
                                            href="download.php?file=<?= urlencode($p["arquivo_pdf"]) ?>">
                                            <i class="bi bi-file-earmark-pdf"></i> <?= htmlspecialchars($p["titulo"]) ?>
                                        </a>
                                    <?php endif; endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($a["necessidades_tecnicas"])): ?>
                                    <p class="small fw-bold mb-1 mt-2">Necessidades técnicas</p>
                                    <p class="small text-muted"><?= nl2br(htmlspecialchars($a["necessidades_tecnicas"])) ?></p>
                                <?php endif; ?>

                                <?php if ($fotos): ?>
                                    <p class="small fw-bold mb-1 mt-2">Fotos de divulgação</p>
                                    <?php foreach ($fotos as $i => $url): ?>
                                        <a class="d-block small mb-1" href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener">
                                            <i class="bi bi-images"></i> Galeria <?= $i + 1 ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (!$pdfs && empty($a["necessidades_tecnicas"]) && !$fotos): ?>
                                    <p class="small text-muted mt-2 mb-0">Esta artista ainda não adicionou materiais.</p>
                                <?php endif; ?>
                            </div>

                            <a href="ficha-artista.php?id=<?= (int) $a["id"] ?>" class="btn btn-sm btn-outline-dark w-100 mb-2" target="_blank">
                                <i class="bi bi-file-earmark-person"></i> Baixar ficha (PDF)
                            </a>
                            <a href="artista.php?id=<?= (int) $a["id"] ?>" class="btn btn-sm btn-circo w-100" target="_blank">
                                Ver perfil completo
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
