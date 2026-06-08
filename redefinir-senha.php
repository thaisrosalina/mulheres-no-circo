<?php
require_once "config/database.php";

$token = $_GET["token"] ?? "";
$erro = "";
$sucesso = "";

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE token_recuperacao = :token
    AND token_expira_em > NOW()
    LIMIT 1
");

$stmt->execute([":token" => $token]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Token inválido ou expirado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET senha_hash = :senha_hash,
                token_recuperacao = NULL,
                token_expira_em = NULL
            WHERE id = :id
        ");

        $stmt->execute([
            ":senha_hash" => $senha_hash,
            ":id" => $usuario["id"]
        ]);

        $sucesso = "Senha redefinida com sucesso. Você já pode fazer login.";
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container my-5">
    <h1>Redefinir Senha</h1>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($sucesso) ?>
        </div>

        <a href="login.php" class="btn btn-circo">Ir para login</a>
    <?php else: ?>

        <form method="POST" class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar nova senha</label>
                <input type="password" name="confirmar_senha" class="form-control" required>
            </div>

            <button class="btn btn-circo">Salvar nova senha</button>
        </form>

    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>