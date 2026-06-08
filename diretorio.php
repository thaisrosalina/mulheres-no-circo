<?php
require_once "config/database.php";
require_once "includes/helpers.php";
require_once "includes/modalidades.php";

// ----- Filtros recebidos -----
$busca = trim($_GET["busca"] ?? "");
$area = trim($_GET["area"] ?? "");
$cidade = trim($_GET["cidade"] ?? "");
$disponivel = isset($_GET["disponivel"]) ? 1 : 0;

$pagina = max(1, (int) ($_GET["pagina"] ?? 1));
$porPagina = 8; // 2 fileiras × 4 colunas
$offset = ($pagina - 1) * $porPagina;

// ----- Monta o WHERE dinamicamente -----
$where = [];
$params = [];

if ($busca !== "") {
    $where[] = "(nome_artistico LIKE :busca
        OR cidade_origem LIKE :busca
        OR cidade_atual LIKE :busca
        OR area_atuacao LIKE :busca
        OR especialidades LIKE :busca)";
    $params[":busca"] = "%$busca%";
}

if ($area !== "") {
    $where[] = "area_atuacao = :area";
    $params[":area"] = $area;
}

if ($cidade !== "") {
    $where[] = "(cidade_atual LIKE :cidade OR cidade_origem LIKE :cidade)";
    $params[":cidade"] = "%$cidade%";
}

if ($disponivel) {
    $where[] = "disponivel_contratacao = 1";
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// ----- Total de resultados (para paginação) -----
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM usuarios $whereSql");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / $porPagina));

// ----- Página atual de artistas (LIMIT/OFFSET são inteiros já saneados) -----
$sql = "
    SELECT id, nome_artistico, cidade_origem, cidade_atual, biografia,
           foto_perfil, area_atuacao, especialidades, disponivel_contratacao
    FROM usuarios
    $whereSql
    ORDER BY nome_artistico
    LIMIT $porPagina OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----- Opções do filtro de área -----
// Sempre mostra todas as categorias do catálogo + valores legados existentes no banco.
$areasDB = $pdo->query("
    SELECT DISTINCT area_atuacao
    FROM usuarios
    WHERE area_atuacao IS NOT NULL AND area_atuacao <> ''
")->fetchAll(PDO::FETCH_COLUMN);

$areas = array_values(array_unique(array_merge($AREAS_ATUACAO, $areasDB)));
natcasesort($areas);
$areas = array_values($areas);

// Helper: gera querystring de paginação preservando os filtros.
function linkPagina($n, $busca, $area, $cidade, $disponivel)
{
    $q = ["pagina" => $n];
    if ($busca !== "") $q["busca"] = $busca;
    if ($area !== "") $q["area"] = $area;
    if ($cidade !== "") $q["cidade"] = $cidade;
    if ($disponivel) $q["disponivel"] = 1;
    return "diretorio.php?" . http_build_query($q);
}

include "includes/header.php";
?>

<div class="container my-5">
    <div class="mb-4">
        <h1 class="display-5">Artistas</h1>
        <p class="lead fs-5">Conheça mulheres que atuam nas artes circenses.</p>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="busca" class="form-control"
                placeholder="Buscar por nome, especialidade..."
                value="<?= htmlspecialchars($busca) ?>">
        </div>

        <div class="col-md-3">
            <select name="area" class="form-select">
                <option value="">Todas as áreas</option>
                <?php foreach ($areas as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= ($area === $a) ? "selected" : "" ?>>
                        <?= htmlspecialchars($a) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <input type="text" name="cidade" class="form-control"
                placeholder="Cidade" value="<?= htmlspecialchars($cidade) ?>">
        </div>

        <div class="col-md-2 d-grid">
            <button class="btn btn-circo"><i class="bi bi-search"></i> Buscar</button>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="disponivel" value="1" class="form-check-input"
                    id="filtroDisponivel" <?= $disponivel ? "checked" : "" ?>>
                <label class="form-check-label" for="filtroDisponivel">
                    Somente disponíveis para contratação
                </label>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted">
            <?= $total ?> <?= $total === 1 ? "artista encontrada" : "artistas encontradas" ?>
        </span>
        <?php if ($busca || $area || $cidade || $disponivel): ?>
            <a href="diretorio.php" class="btn btn-sm btn-link">Limpar filtros</a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php if (count($artistas) === 0): ?>
            <div class="col-12">
                <div class="alert alert-light border text-center py-5">
                    <i class="bi bi-search fs-1 d-block mb-2 text-muted"></i>
                    Nenhuma artista encontrada com esses filtros.
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($artistas as $artista): ?>
            <div class="col-6 col-md-3">
                <?= cardArtistaHtml($artista) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $pagina <= 1 ? "disabled" : "" ?>">
                    <a class="page-link" href="<?= linkPagina($pagina - 1, $busca, $area, $cidade, $disponivel) ?>">Anterior</a>
                </li>

                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <li class="page-item <?= $p === $pagina ? "active" : "" ?>">
                        <a class="page-link" href="<?= linkPagina($p, $busca, $area, $cidade, $disponivel) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $pagina >= $totalPaginas ? "disabled" : "" ?>">
                    <a class="page-link" href="<?= linkPagina($pagina + 1, $busca, $area, $cidade, $disponivel) ?>">Próxima</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
