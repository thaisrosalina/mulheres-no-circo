<?php
require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/csrf.php";
require_once "includes/log.php";

protegerPagina();

$usuario_id = $_SESSION["usuario_id"];
$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF($_POST["csrf_token"] ?? "")) {
        die("Token CSRF inválido.");
    }

    $atual = $_POST["senha_atual"] ?? "";
    $nova = $_POST["nova_senha"] ?? "";
    $confirmar = $_POST["confirmar_senha"] ?? "";

    $stmt = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($atual, $usuario["senha_hash"])) {
        $erro = "A senha atual está incorreta.";
    } elseif (strlen($nova) < 8) {
        $erro = "A nova senha deve ter no mínimo 8 caracteres.";
    } elseif ($nova !== $confirmar) {
        $erro = "A confirmação não corresponde à nova senha.";
    } elseif ($nova === $atual) {
        $erro = "A nova senha deve ser diferente da atual.";
    } else {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = :hash WHERE id = :id");
        $stmt->execute([":hash" => $hash, ":id" => $usuario_id]);

        registrarLog($pdo, $usuario_id, "Senha alterada");
        $sucesso = "Senha alterada com sucesso.";
    }
}

include "includes/header.php";
?>

<div class="container my-5">
    <div class="auth-card">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-1 text-center">Alterar senha</h1>
                <p class="text-muted text-center mb-4">Escolha uma senha forte e que só você conheça.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= gerarCSRF() ?>">

                    <div class="mb-3">
                        <label class="form-label">Senha atual</label>
                        <input type="password" name="senha_atual" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="nova_senha" class="form-control" minlength="8" required>
                        <small class="text-muted">Mínimo de 8 caracteres.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar nova senha</label>
                        <input type="password" name="confirmar_senha" class="form-control" minlength="8" required>
                    </div>

                    <button class="btn btn-circo w-100">Salvar nova senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
