<?php
require_once "config/database.php";
require_once "includes/modalidades.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_artistico = trim($_POST["nome_artistico"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar_senha = $_POST["confirmar_senha"] ?? "";
    $aceite = isset($_POST["aceite_termos"]);
    $area_atuacao = trim($_POST["area_atuacao"] ?? "");

    // Especialidades: valida contra o catálogo e junta por vírgula.
    $espSelecionadas = $_POST["especialidades"] ?? [];
    if (!is_array($espSelecionadas)) {
        $espSelecionadas = [];
    }
    $especialidades = implode(", ", array_values(array_intersect($espSelecionadas, todasModalidades())));

    // Data vazia deve virar NULL (evita data inválida na coluna DATE).
    $data_nascimento = trim($_POST["data_nascimento"] ?? "");
    $data_nascimento = ($data_nascimento !== "") ? $data_nascimento : null;

    if (empty($nome_artistico) || empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um email válido.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } elseif (!$aceite) {
        $erro = "Você precisa aceitar a Política de Privacidade para se cadastrar.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios
        (nome_artistico, email, senha_hash, data_nascimento, cidade_origem, cidade_atual, genero, orientacao_sexual, area_atuacao, especialidades, aceite_termos, aceite_termos_em)
        VALUES
        (:nome_artistico, :email, :senha_hash, :data_nascimento, :cidade_origem, :cidade_atual, :genero, :orientacao_sexual, :area_atuacao, :especialidades, 1, NOW())";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ":nome_artistico" => $nome_artistico,
                ":email" => $email,
                ":senha_hash" => $senha_hash,
                ":data_nascimento" => $data_nascimento,
                ":cidade_origem" => trim($_POST["cidade_origem"] ?? ""),
                ":cidade_atual" => trim($_POST["cidade_atual"] ?? ""),
                ":genero" => trim($_POST["genero"] ?? ""),
                ":orientacao_sexual" => trim($_POST["orientacao_sexual"] ?? ""),
                ":area_atuacao" => $area_atuacao,
                ":especialidades" => $especialidades
            ]);

            $sucesso = "Cadastro realizado com sucesso. Agora você pode fazer login.";
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar. Este email pode já estar em uso.";
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container my-5">
    <div class="col-lg-9 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-1">Criar meu perfil</h1>
                <p class="text-muted mb-4">Cadastre-se para compor o diretório de artistas circenses.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($sucesso) ?>
                        <a href="login.php" class="alert-link">Ir para o login</a>.
                    </div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Nome artístico</label>
            <input type="text" name="nome_artistico" class="form-control" required
                value="<?= htmlspecialchars($_POST["nome_artistico"] ?? "") ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Data de nascimento</label>
            <input type="date" name="data_nascimento" class="form-control"
                value="<?= htmlspecialchars($_POST["data_nascimento"] ?? "") ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Cidade de origem</label>
            <input type="text" name="cidade_origem" class="form-control"
                value="<?= htmlspecialchars($_POST["cidade_origem"] ?? "") ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Cidade atual</label>
            <input type="text" name="cidade_atual" class="form-control"
                value="<?= htmlspecialchars($_POST["cidade_atual"] ?? "") ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Identidade de gênero</label>
            <select name="genero" class="form-select">
                <option value="">Selecione...</option>
                <option value="cisgenero">Cisgênero</option>
                <option value="transgenero">Transgênero</option>
                <option value="nao_binaria">Não binária</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Orientação sexual</label>
            <select name="orientacao_sexual" class="form-select">
                <option value="">Selecione...</option>
                <option value="heterossexual">Heterossexual</option>
                <option value="homossexual">Homossexual</option>
                <option value="bissexual">Bissexual</option>
                <option value="assexual">Assexual</option>
                <option value="pansexual">Pansexual</option>
                <option value="intersexual">Intersexual</option>
                <option value="queer">Queer</option>
                <option value="prefiro_nao_informar">Prefiro não informar</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Área de atuação (categoria principal)</label>
            <?php $areaSel = $_POST["area_atuacao"] ?? ""; ?>
            <select name="area_atuacao" class="form-select">
                <option value="">Selecione...</option>
                <?php foreach ($AREAS_ATUACAO as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= $areaSel === $a ? "selected" : "" ?>><?= htmlspecialchars($a) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php $selecionadas = $_POST["especialidades"] ?? []; include "includes/seletor-modalidades.php"; ?>

        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required
                value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Senha</label>
            <input type="password" name="senha" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Confirmar senha</label>
            <input type="password" name="confirmar_senha" class="form-control" required>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="aceite_termos" value="1" class="form-check-input" id="aceite" required>
                <label class="form-check-label" for="aceite">
                    Li e aceito a <a href="politica-privacidade.php" target="_blank"><strong>Política de Privacidade</strong></a>. Autorizo o uso dos meus dados
                    para compor meu perfil na plataforma. Dados de gênero e orientação são opcionais e
                    mantidos privados, usados apenas para fins estatísticos do projeto.
                </label>
            </div>
        </div>

                    <div class="col-12">
                        <button class="btn btn-circo btn-lg">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
