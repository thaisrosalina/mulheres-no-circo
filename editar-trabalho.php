<?php
require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/log.php";
require_once "includes/csrf.php";
require_once "includes/helpers.php";
require_once "includes/modalidades.php";

protegerPagina();

$usuario_id = $_SESSION["usuario_id"];
$erro = "";
$sucesso = "";

// ID do trabalho a editar (via GET ou POST).
$post_id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

// Busca o trabalho garantindo que pertence à usuária logada (evita IDOR).
$stmt = $pdo->prepare("
    SELECT *
    FROM posts
    WHERE id = :id AND usuario_id = :usuario_id
    LIMIT 1
");

$stmt->execute([
    ":id" => $post_id,
    ":usuario_id" => $usuario_id
]);

$trabalho = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trabalho) {
    die("Trabalho não encontrado ou você não tem permissão para editá-lo.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF($_POST["csrf_token"] ?? "")) {
        die("Token CSRF inválido.");
    }

    $titulo = trim($_POST["titulo"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $conteudo = trim($_POST["conteudo"] ?? "");
    $video_youtube = trim($_POST["video_youtube"] ?? "");

    // Mantém os arquivos atuais por padrão.
    $imagem_nome = $trabalho["imagem"];
    $pdf_nome = $trabalho["arquivo_pdf"];

    if (empty($titulo) || empty($categoria) || empty($conteudo)) {
        $erro = "Título, categoria e descrição são obrigatórios.";
    } elseif (strlen($titulo) > 200) {
        $erro = "O título deve ter no máximo 200 caracteres.";
    }

    // Nova imagem (opcional).
    if (!$erro && !empty($_FILES["imagem"]["name"])) {
        $imagem = $_FILES["imagem"];
        $erro = validarImagemUpload($imagem, 5 * 1024 * 1024);

        if (!$erro) {
            $ext = strtolower(pathinfo($imagem["name"], PATHINFO_EXTENSION));
            $novoNome = "trabalho_" . $usuario_id . "_" . time() . "." . $ext;
            $pastaImagem = __DIR__ . "/uploads/trabalhos/";

            if (!is_dir($pastaImagem)) {
                mkdir($pastaImagem, 0755, true);
            }

            if (move_uploaded_file($imagem["tmp_name"], $pastaImagem . $novoNome)) {
                // Remove a imagem antiga, se existir.
                if (!empty($trabalho["imagem"]) && file_exists($pastaImagem . $trabalho["imagem"])) {
                    @unlink($pastaImagem . $trabalho["imagem"]);
                }
                $imagem_nome = $novoNome;
            } else {
                $erro = "Não foi possível salvar a nova imagem.";
            }
        }
    }

    // Novo PDF (opcional).
    if (!$erro && !empty($_FILES["arquivo_pdf"]["name"])) {
        $pdf = $_FILES["arquivo_pdf"];
        $ext = strtolower(pathinfo($pdf["name"], PATHINFO_EXTENSION));

        if ($pdf["error"] !== UPLOAD_ERR_OK) {
            $erro = "Erro ao enviar o PDF. Tente novamente.";
        } elseif ($ext !== "pdf") {
            $erro = "A apresentação deve ser um arquivo PDF.";
        } elseif ($pdf["size"] > 10 * 1024 * 1024) {
            $erro = "O PDF deve ter no máximo 10MB.";
        } else {
            $novoPdf = "apresentacao_" . $usuario_id . "_" . time() . ".pdf";
            $pastaPdf = __DIR__ . "/uploads/apresentacoes/";

            if (!is_dir($pastaPdf)) {
                mkdir($pastaPdf, 0755, true);
            }

            if (move_uploaded_file($pdf["tmp_name"], $pastaPdf . $novoPdf)) {
                if (!empty($trabalho["arquivo_pdf"]) && file_exists($pastaPdf . $trabalho["arquivo_pdf"])) {
                    @unlink($pastaPdf . $trabalho["arquivo_pdf"]);
                }
                $pdf_nome = $novoPdf;
            } else {
                $erro = "Não foi possível salvar o novo PDF.";
            }
        }
    }

    if (!$erro) {
        $stmt = $pdo->prepare("
            UPDATE posts SET
                titulo = :titulo,
                categoria = :categoria,
                conteudo = :conteudo,
                imagem = :imagem,
                arquivo_pdf = :arquivo_pdf,
                video_youtube = :video_youtube
            WHERE id = :id AND usuario_id = :usuario_id
        ");

        $stmt->execute([
            ":titulo" => $titulo,
            ":categoria" => $categoria,
            ":conteudo" => $conteudo,
            ":imagem" => $imagem_nome,
            ":arquivo_pdf" => $pdf_nome,
            ":video_youtube" => $video_youtube,
            ":id" => $post_id,
            ":usuario_id" => $usuario_id
        ]);

        registrarLog($pdo, $usuario_id, "Trabalho atualizado");

        $sucesso = "Trabalho atualizado com sucesso.";

        // Recarrega os dados atualizados para refletir no formulário.
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id AND usuario_id = :usuario_id LIMIT 1");
        $stmt->execute([":id" => $post_id, ":usuario_id" => $usuario_id]);
        $trabalho = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

include "includes/header.php";
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Editar Trabalho</h1>
        <a href="perfil.php#trabalhos" class="btn btn-outline-dark btn-sm">Voltar aos meus trabalhos</a>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="editar-trabalho.php?id=<?= (int) $trabalho["id"] ?>">
        <input type="hidden" name="csrf_token" value="<?= gerarCSRF() ?>">
        <input type="hidden" name="id" value="<?= (int) $trabalho["id"] ?>">

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nome do trabalho</label>
                <input type="text" name="titulo" class="form-control" maxlength="200" required
                    value="<?= htmlspecialchars($trabalho["titulo"]) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Categoria (tipo de serviço)</label>
                <select name="categoria" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php if (!empty($trabalho["categoria"]) && !in_array($trabalho["categoria"], $TIPOS_SERVICO)): ?>
                        <option value="<?= htmlspecialchars($trabalho["categoria"]) ?>" selected>
                            <?= htmlspecialchars($trabalho["categoria"]) ?> (atual)
                        </option>
                    <?php endif; ?>
                    <?php foreach ($TIPOS_SERVICO as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"
                            <?= ($trabalho["categoria"] === $cat) ? "selected" : "" ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Descrição completa</label>
                <textarea name="conteudo" class="form-control" rows="5" required><?= htmlspecialchars($trabalho["conteudo"]) ?></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Foto do trabalho</label>
                <?php if (!empty($trabalho["imagem"])): ?>
                    <div class="mb-2">
                        <img src="uploads/trabalhos/<?= htmlspecialchars($trabalho["imagem"]) ?>"
                            class="img-thumbnail" style="max-height: 120px;" alt="Foto atual">
                    </div>
                <?php endif; ?>
                <input type="file" name="imagem" class="form-control" accept=".jpg,.jpeg,.png">
                <small class="text-muted">Deixe em branco para manter a foto atual. Máx. 5MB.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Apresentação completa em PDF</label>
                <?php if (!empty($trabalho["arquivo_pdf"])): ?>
                    <div class="mb-2">
                        <a href="download.php?file=<?= urlencode($trabalho["arquivo_pdf"]) ?>"
                            class="btn btn-sm btn-outline-dark" target="_blank">PDF atual</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="arquivo_pdf" class="form-control" accept=".pdf">
                <small class="text-muted">Deixe em branco para manter o PDF atual. Máx. 10MB.</small>
            </div>

            <div class="col-md-12">
                <label class="form-label">Link do vídeo no YouTube</label>
                <input type="url" name="video_youtube" class="form-control"
                    placeholder="https://www.youtube.com/watch?v=..."
                    value="<?= htmlspecialchars($trabalho["video_youtube"] ?? "") ?>">

                <?php $embed = youtubeEmbedUrl($trabalho["video_youtube"] ?? ""); ?>
                <?php if ($embed !== ""): ?>
                    <div class="ratio ratio-16x9 mt-3">
                        <iframe src="<?= htmlspecialchars($embed) ?>" allowfullscreen></iframe>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <button class="btn btn-circo">Salvar alterações</button>
                <a href="perfil.php#trabalhos" class="btn btn-link">Cancelar</a>
            </div>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
