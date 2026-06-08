<?php
require_once "config/database.php";
require_once "includes/csrf.php";
require_once "includes/redes.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF($_POST["csrf_token"] ?? "")) {
        die("Token CSRF inválido.");
    }

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $assunto = trim($_POST["assunto"] ?? "");
    $mensagem = trim($_POST["mensagem"] ?? "");

    if ($nome === "" || $email === "" || $mensagem === "") {
        $erro = "Preencha nome, e-mail e mensagem.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um e-mail válido.";
    } elseif (mb_strlen($mensagem) > 5000) {
        $erro = "A mensagem é muito longa (máx. 5000 caracteres).";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO mensagens_contato (nome, email, assunto, mensagem)
            VALUES (:nome, :email, :assunto, :mensagem)
        ");
        $stmt->execute([
            ":nome" => $nome,
            ":email" => $email,
            ":assunto" => $assunto,
            ":mensagem" => $mensagem,
        ]);

        $sucesso = "Mensagem enviada com sucesso! Em breve entraremos em contato.";
    }
}

include "includes/header.php";
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <p class="contato-eyebrow">Fale com a gente</p>
        <h1 class="display-5 mb-2">Contato</h1>
        <p class="lead col-lg-7 mx-auto">Dúvidas, sugestões, parcerias ou imprensa — escolha o canal que preferir.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 mb-3">Envie uma mensagem</h2>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>

                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= gerarCSRF() ?>">

                <div class="col-md-6">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control" required
                        value="<?= htmlspecialchars($_POST["nome"] ?? "") ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required
                        value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Assunto</label>
                    <input type="text" name="assunto" class="form-control" maxlength="200"
                        value="<?= htmlspecialchars($_POST["assunto"] ?? "") ?>">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Mensagem</label>
                    <textarea name="mensagem" class="form-control" rows="5" required><?= htmlspecialchars($_POST["mensagem"] ?? "") ?></textarea>
                </div>

                        <div class="col-12">
                            <button class="btn btn-circo"><i class="bi bi-send"></i> Enviar mensagem</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Outros canais</h2>

                    <?php if (whatsappLink() !== ""): ?>
                        <a href="<?= htmlspecialchars(whatsappLink()) ?>" target="_blank" rel="noopener"
                            class="btn btn-success w-100 mb-3">
                            <i class="bi bi-whatsapp"></i> Falar no WhatsApp
                        </a>
                    <?php endif; ?>

                    <p class="mb-2">
                        <i class="bi bi-envelope"></i>
                        <a href="mailto:<?= htmlspecialchars(CONTATO_EMAIL) ?>" class="text-decoration-none">
                            <?= htmlspecialchars(CONTATO_EMAIL) ?>
                        </a>
                    </p>

                    <?php if (REDE_INSTAGRAM !== ""): ?>
                        <p class="mb-2">
                            <i class="bi bi-instagram"></i>
                            <a href="<?= htmlspecialchars(REDE_INSTAGRAM) ?>" target="_blank" rel="noopener" class="text-decoration-none">
                                Instagram
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if (REDE_FACEBOOK !== ""): ?>
                        <p class="mb-2">
                            <i class="bi bi-facebook"></i>
                            <a href="<?= htmlspecialchars(REDE_FACEBOOK) ?>" target="_blank" rel="noopener" class="text-decoration-none">Facebook</a>
                        </p>
                    <?php endif; ?>

                    <?php if (REDE_YOUTUBE !== ""): ?>
                        <p class="mb-2">
                            <i class="bi bi-youtube"></i>
                            <a href="<?= htmlspecialchars(REDE_YOUTUBE) ?>" target="_blank" rel="noopener" class="text-decoration-none">YouTube</a>
                        </p>
                    <?php endif; ?>

                    <?php if (REDE_LINKEDIN !== ""): ?>
                        <p class="mb-2">
                            <i class="bi bi-linkedin"></i>
                            <a href="<?= htmlspecialchars(REDE_LINKEDIN) ?>" target="_blank" rel="noopener" class="text-decoration-none">LinkedIn</a>
                        </p>
                    <?php endif; ?>

                    <?php if (REDE_TIKTOK !== ""): ?>
                        <p class="mb-2">
                            <i class="bi bi-tiktok"></i>
                            <a href="<?= htmlspecialchars(REDE_TIKTOK) ?>" target="_blank" rel="noopener" class="text-decoration-none">TikTok</a>
                        </p>
                    <?php endif; ?>

                    <p class="mb-0"><i class="bi bi-stars"></i> Projeto Mulheres no Circo</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
