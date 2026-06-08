<?php
require_once "includes/auth.php";
protegerPagina();

include "includes/header.php";
?>

<div class="container my-5">
    <h1>Olá, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?>!</h1>

    <p class="lead">
        Bem-vinda ao painel da plataforma Mulheres no Circo.
    </p>

    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="perfil.php" class="btn btn-circo w-100 p-3">Meu Painel</a>
        </div>

        <div class="col-md-4">
            <a href="perfil.php#trabalhos" class="btn btn-dark w-100 p-3">Meus Trabalhos</a>
        </div>

        <div class="col-md-4">
            <a href="diretorio.php" class="btn btn-outline-dark w-100 p-3">Ver Artistas</a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>