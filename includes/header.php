<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Mulheres no Circo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css?v=18">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/img/logo.svg" alt="" width="38" height="38">
            <span>Mulheres no Circo</span>
        </a>

        <button 
            class="navbar-toggler" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#menuPrincipal"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <!-- PARTE 1: PÚBLICA -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Início</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="sobre.php">Sobre</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="diretorio.php">Artistas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="apoie.php">Apoie</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="contato.php">Contato</a>
                </li>

                <!-- DIVISOR VISUAL -->
                <li class="nav-item d-none d-lg-block">
                    <span class="menu-divisor d-inline-block"></span>
                </li>

                <!-- PARTE 2: ÁREA LOGADA (menu da conta) -->
                <?php if (isset($_SESSION["usuario_id"])): ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle area-logada" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($_SESSION["usuario_nome"] ?? "Minha conta") ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (($_SESSION["usuario_role"] ?? "") === "curador"): ?>
                                <li><a class="dropdown-item" href="area-curador.php"><i class="bi bi-search-heart me-2"></i>Área do Produtor(a)</a></li>
                                <li><a class="dropdown-item" href="area-curador.php?aba=curadoria"><i class="bi bi-bookmark me-2"></i>Minha curadoria</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person me-2"></i>Meu Painel</a></li>
                            <?php endif; ?>

                            <?php if (($_SESSION["usuario_role"] ?? "") === "admin"): ?>
                                <li><a class="dropdown-item" href="area-curador.php"><i class="bi bi-search-heart me-2"></i>Área do Produtor</a></li>
                                <li><a class="dropdown-item" href="admin.php"><i class="bi bi-gear me-2"></i>Admin</a></li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="alterar-senha.php"><i class="bi bi-key me-2"></i>Alterar senha</a></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link area-logada" href="login.php">
                            Entrar
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link area-logada" href="cadastro.php">
                            Cadastrar
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>