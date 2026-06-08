<?php
require_once "config/database.php";
require_once "includes/log.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = "";

// Limite de tentativas de login por IP em uma janela de tempo (proteção contra força bruta).
const LOGIN_MAX_TENTATIVAS = 5;
const LOGIN_JANELA_MINUTOS = 15;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $lembrar = isset($_POST["lembrar"]);
    $ip = $_SERVER["REMOTE_ADDR"] ?? "desconhecido";

    // Conta falhas recentes deste IP dentro da janela de tempo.
    // A janela é uma constante interna (inteiro confiável), por isso é embutida com segurança.
    $janela = (int) LOGIN_JANELA_MINUTOS;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM tentativas_login
        WHERE ip = :ip
          AND sucesso = 0
          AND criado_em > (NOW() - INTERVAL {$janela} MINUTE)
    ");
    $stmt->bindValue(":ip", $ip);
    $stmt->execute();
    $falhasRecentes = (int) $stmt->fetchColumn();

    if ($falhasRecentes >= LOGIN_MAX_TENTATIVAS) {
        $erro = "Muitas tentativas de login. Aguarde " . LOGIN_JANELA_MINUTOS . " minutos e tente novamente.";
    }

    $usuario = null;

    if (!$erro) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([
            ":email" => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$erro && $usuario && password_verify($senha, $usuario["senha_hash"])) {
        // Renova o ID de sessão para evitar fixação de sessão.
        session_regenerate_id(true);

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome_artistico"];
        $_SESSION["usuario_role"] = $usuario["role"];

        // Registra a tentativa bem-sucedida e limpa as falhas recentes deste IP/email.
        $stmt = $pdo->prepare("
            INSERT INTO tentativas_login (email, ip, sucesso)
            VALUES (:email, :ip, 1)
        ");
        $stmt->execute([":email" => $email, ":ip" => $ip]);

        $stmt = $pdo->prepare("
            DELETE FROM tentativas_login
            WHERE sucesso = 0 AND (ip = :ip OR email = :email)
        ");
        $stmt->execute([":ip" => $ip, ":email" => $email]);

        if ($lembrar) {
            $token = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET remember_token = :token 
                WHERE id = :id
            ");

            $stmt->execute([
                ":token" => $token,
                ":id" => $usuario["id"]
            ]);

            setcookie("remember_token", $token, time() + (7 * 24 * 60 * 60), "/");
        }

        registrarLog($pdo, $usuario["id"], "Login realizado");

        $destino = ($usuario["role"] === "curador") ? "area-curador.php" : "dashboard.php";
        header("Location: " . $destino);
        exit;
    } elseif (!$erro) {
        $erro = "Email ou senha incorretos.";

        $stmt = $pdo->prepare("
            INSERT INTO tentativas_login (email, ip, sucesso)
            VALUES (:email, :ip, 0)
        ");

        $stmt->execute([
            ":email" => $email,
            ":ip" => $ip
        ]);

        registrarLog($pdo, null, "Tentativa de login falhou para o email: " . $email);
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container my-5">
    <div class="auth-card">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-1 text-center">Entrar</h1>
                <p class="text-muted text-center mb-4">Acesse seu painel na plataforma.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="lembrar" class="form-check-input" id="lembrar">
                        <label class="form-check-label" for="lembrar">Lembrar-me</label>
                    </div>

                    <button class="btn btn-circo w-100 mb-2">Entrar</button>
                </form>

                <div class="text-center">
                    <a href="recuperar-senha.php" class="btn btn-link btn-sm">Esqueci minha senha</a>
                </div>

                <hr class="my-4">
                <p class="text-center mb-0 text-muted">
                    Ainda não tem perfil?
                    <a href="cadastro.php">Cadastre-se</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>