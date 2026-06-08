<?php
require_once "config/database.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $organizacao = trim($_POST["organizacao"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar_senha = $_POST["confirmar_senha"] ?? "";
    $aceite = isset($_POST["aceite_termos"]);
    $quer_novidades = isset($_POST["quer_novidades"]) ? 1 : 0;
    $canal_novidades = trim($_POST["canal_novidades"] ?? "");

    $canaisValidos = ["email", "whatsapp"];
    if (!in_array($canal_novidades, $canaisValidos)) {
        $canal_novidades = "email";
    }

    if ($nome === "" || $email === "" || $senha === "") {
        $erro = "Preencha nome, e-mail e senha.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um e-mail válido.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (!$aceite) {
        $erro = "Você precisa aceitar a Política de Privacidade para se cadastrar.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios
            (nome_artistico, email, senha_hash, organizacao, telefone, role,
             quer_novidades, canal_novidades, aceite_termos, aceite_termos_em)
            VALUES
            (:nome, :email, :senha_hash, :organizacao, :telefone, 'curador',
             :quer_novidades, :canal_novidades, 1, NOW())";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ":nome" => $nome,
                ":email" => $email,
                ":senha_hash" => $senha_hash,
                ":organizacao" => $organizacao,
                ":telefone" => $telefone,
                ":quer_novidades" => $quer_novidades,
                ":canal_novidades" => $canal_novidades,
            ]);
            $sucesso = "Cadastro realizado com sucesso. Agora você pode acessar sua área.";
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar. Este e-mail pode já estar em uso.";
        }
    }
}

include "includes/header.php";
?>

<div class="container my-5">
    <div class="col-lg-7 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <p class="contato-eyebrow">Área do Produtor(a) / Programador</p>
                <h1 class="h3 mb-1">Cadastro de Produtor(a) / Programador(a)</h1>
                <p class="text-muted mb-4">
                    Para produtores(as), programadores(as), curadores(as) e festivais que buscam
                    contratar artistas e baixar materiais.
                </p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($sucesso) ?>
                        <a href="login.php" class="alert-link">Entrar agora</a>.
                    </div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="nome" class="form-control" required
                            value="<?= htmlspecialchars($_POST["nome"] ?? "") ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Organização / Festival</label>
                        <input type="text" name="organizacao" class="form-control"
                            value="<?= htmlspecialchars($_POST["organizacao"] ?? "") ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control"
                            placeholder="(31) 99999-9999"
                            value="<?= htmlspecialchars($_POST["telefone"] ?? "") ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar senha</label>
                        <input type="password" name="confirmar_senha" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="quer_novidades" value="1" class="form-check-input" id="novidades" checked>
                            <label class="form-check-label" for="novidades">
                                Quero receber novidades quando novas artistas entrarem na plataforma.
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Canal preferido para novidades</label>
                        <select name="canal_novidades" class="form-select">
                            <option value="email">E-mail</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="aceite_termos" value="1" class="form-check-input" id="aceite" required>
                            <label class="form-check-label" for="aceite">
                                Li e aceito a <a href="politica-privacidade.php" target="_blank"><strong>Política de Privacidade</strong></a>.
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-circo btn-lg">Criar minha conta</button>
                    </div>
                </form>

                <hr class="my-4">
                <p class="text-center mb-0 text-muted">Já tem conta? <a href="login.php">Entrar</a></p>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
