<?php
require_once "config/database.php";

$mensagem = "";
$link = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([":email" => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mensagem genérica sempre — não revela se o e-mail existe (anti-enumeração).
    $mensagem = "Se o e-mail estiver cadastrado, um link de recuperação será gerado.";

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET token_recuperacao = :token,
                token_expira_em = :expira
            WHERE id = :id
        ");

        $stmt->execute([
            ":token" => $token,
            ":expira" => $expira,
            ":id" => $usuario["id"]
        ]);

        // URL dinâmica (host/protocolo reais) — funciona em localhost e no deploy.
        $protocolo = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
        $basePath = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
        $base = $protocolo . "://" . $_SERVER["HTTP_HOST"] . $basePath;

        // Em produção o link deve ser ENVIADO POR E-MAIL (ex.: PHPMailer/SMTP).
        // Em ambiente local, exibimos na tela apenas para facilitar os testes.
        $hostLocal = in_array($_SERVER["HTTP_HOST"] ?? "", ["localhost", "127.0.0.1", "localhost:8000"])
            || str_starts_with($_SERVER["HTTP_HOST"] ?? "", "localhost");

        if ($hostLocal) {
            $link = $base . "/redefinir-senha.php?token=" . $token;
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container my-5">
    <h1>Recuperar Senha</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if ($link): ?>
        <div class="alert alert-success">
            <p>Use este link para redefinir sua senha:</p>
            <a href="<?= htmlspecialchars($link) ?>">
                <?= htmlspecialchars($link) ?>
            </a>
        </div>
    <?php endif; ?>

    <form method="POST" class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Email cadastrado</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <button class="btn btn-circo">Gerar link</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>