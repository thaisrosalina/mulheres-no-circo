<?php
require_once "config/database.php";
require_once "includes/helpers.php";

// Mostra artistas de forma justa (ordem aleatória, rotaciona a cada visita) — sem priorizar.
$destaques = $pdo->query("
    SELECT id, nome_artistico, area_atuacao, cidade_atual, cidade_origem, foto_perfil
    FROM usuarios
    ORDER BY RAND()
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

$totalArtistas = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

include "includes/header.php";
?>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="eyebrow">Diretório de artistas circenses</span>
                <h1 class="display-3 mb-3">Mulheres que fazem<br>o circo acontecer</h1>
                <p class="lead mb-4 col-lg-11">
                    Mapeamento, visibilidade e conexão para mulheres das artes circenses.
                    Um espaço para mostrar trajetória, trabalhos e talento.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="cadastro.php" class="btn btn-light btn-lg">Criar meu perfil</a>
                    <a href="diretorio.php" class="btn btn-outline-light btn-lg">Conhecer as artistas</a>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-md-block">
                <img src="assets/img/hero-artista.jpg" alt="Artista circense"
                    class="hero-img img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- PILARES -->
<main class="container my-5 py-3">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-pin-map-fill"></i></div>
                <h3 class="h4">Mapeamento</h3>
                <p class="mb-0 text-muted">Reúne artistas circenses em uma plataforma profissional e acessível.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-megaphone-fill"></i></div>
                <h3 class="h4">Visibilidade</h3>
                <p class="mb-0 text-muted">Valoriza trajetórias, especialidades e produções artísticas.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card h-100 p-4">
                <div class="feature-icon mb-3"><i class="bi bi-people-fill"></i></div>
                <h3 class="h4">Conexão</h3>
                <p class="mb-0 text-muted">Aproxima artistas, curadoras, produtoras, festivais e instituições culturais.</p>
            </div>
        </div>
    </div>
</main>

<!-- MAPEAMENTO DE MULHERES NO CIRCO -->
<section class="container my-5">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <h2 class="secao-titulo mb-0">Mapeamento de Mulheres no Circo</h2>
        <a href="diretorio.php" class="btn btn-outline-dark">Ver todas as artistas <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-4">
        <?php foreach ($destaques as $a): ?>
            <div class="col-6 col-md-3">
                <?= cardArtistaHtml($a, ["bio" => false]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CHAMADA FINAL -->
<section class="container my-5">
    <div class="p-5 rounded text-center" style="background: var(--mnc-rose);">
        <h2 class="mb-2">Você é artista circense?</h2>
        <p class="lead mb-4">Crie seu perfil gratuito e mostre seu trabalho para o Brasil inteiro.</p>
        <a href="cadastro.php" class="btn btn-circo btn-lg">Quero participar</a>
    </div>
</section>

<?php include "includes/footer.php"; ?>
