<?php
require_once "includes/redes.php";
include "includes/header.php";
?>

<section class="hero">
    <div class="container">
        <span class="eyebrow">Apoie a iniciativa</span>
        <h1 class="display-4 mb-2">Fortaleça as Mulheres no Circo</h1>
        <p class="lead col-lg-8 mb-0">
            Mulheres no Circo é uma iniciativa sem fins lucrativos para ajudar o setor circense —
            dar visibilidade às artistas, conectar com quem contrata e preservar a memória do circo.
        </p>
    </div>
</section>

<div class="container my-5">
    <p class="lead text-center col-lg-9 mx-auto mb-5">
        <em>(Texto provisório — você poderá ajustar.)</em> Manter uma plataforma viva, com cadastro
        ativo e curadoria de qualidade, exige trabalho contínuo. Existem várias formas de apoiar —
        escolha a que combina com você.
    </p>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card h-100 p-4 text-center">
                <div class="feature-icon mx-auto mb-3"><i class="bi bi-megaphone-fill"></i></div>
                <h3 class="h5">Divulgue</h3>
                <p class="text-muted mb-0">Compartilhe a plataforma e os perfis das artistas nas suas redes e com produtoras e festivais.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card h-100 p-4 text-center">
                <div class="feature-icon mx-auto mb-3"><i class="bi bi-people-fill"></i></div>
                <h3 class="h5">Seja parceiro(a)</h3>
                <p class="text-muted mb-0">Instituições, escolas de circo e coletivos podem apoiar como realizadores e ampliar o alcance do mapeamento.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card h-100 p-4 text-center">
                <div class="feature-icon mx-auto mb-3"><i class="bi bi-heart-fill"></i></div>
                <h3 class="h5">Contribua</h3>
                <p class="text-muted mb-0">Doações e patrocínios ajudam a manter a plataforma no ar e a desenvolver novas funcionalidades. <em>(Dados de doação em breve.)</em></p>
            </div>
        </div>
    </div>

    <div class="p-5 rounded text-center mt-5" style="background: var(--mnc-rose);">
        <h2 class="mb-2">Quer apoiar ou ser parceiro(a)?</h2>
        <p class="lead mb-4">Fale com a gente — vamos adorar construir essa rede junto com você.</p>
        <a href="contato.php" class="btn btn-circo btn-lg">Entrar em contato</a>
        <?php if (whatsappLink() !== ""): ?>
            <a href="<?= htmlspecialchars(whatsappLink()) ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-lg">
                <i class="bi bi-whatsapp"></i> WhatsApp
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
