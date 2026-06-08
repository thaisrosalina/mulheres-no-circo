<?php
require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/log.php";
require_once "includes/csrf.php";
require_once "includes/helpers.php";
require_once "includes/modalidades.php";

protegerPagina();

$usuario_id = $_SESSION["usuario_id"];

$erroPerfil = "";
$sucessoPerfil = "";
$erroTrab = "";
$sucessoTrab = "";

$categorias = $TIPOS_SERVICO;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF($_POST["csrf_token"] ?? "")) {
        die("Token CSRF inválido.");
    }

    $acao = $_POST["acao"] ?? "";

    // ---------- Salvar perfil ----------
    if ($acao === "salvar_perfil") {
        $nome_artistico = trim($_POST["nome_artistico"] ?? "");
        $cidade_origem = trim($_POST["cidade_origem"] ?? "");
        $cidade_atual = trim($_POST["cidade_atual"] ?? "");
        $data_nascimento = trim($_POST["data_nascimento"] ?? "");
        $genero = trim($_POST["genero"] ?? "");
        $orientacao_sexual = trim($_POST["orientacao_sexual"] ?? "");
        $biografia = trim($_POST["biografia"] ?? "");
        $area_atuacao = trim($_POST["area_atuacao"] ?? "");

        // Especialidades: vêm como lista; valida contra o catálogo e junta por vírgula.
        $espSelecionadas = $_POST["especialidades"] ?? [];
        if (!is_array($espSelecionadas)) {
            $espSelecionadas = [];
        }
        $espValidas = array_values(array_intersect($espSelecionadas, todasModalidades()));
        $especialidades = implode(", ", $espValidas);

        $instagram = trim($_POST["instagram"] ?? "");
        $youtube = trim($_POST["youtube"] ?? "");
        $linkedin = trim($_POST["linkedin"] ?? "");
        $site_oficial = trim($_POST["site_oficial"] ?? "");
        $mapa_cultura = trim($_POST["mapa_cultura"] ?? "");
        $necessidades_tecnicas = trim($_POST["necessidades_tecnicas"] ?? "");
        $fotos_divulgacao = trim($_POST["fotos_divulgacao"] ?? "");
        $disponivel_contratacao = isset($_POST["disponivel_contratacao"]) ? 1 : 0;
        $data_nascimento = ($data_nascimento !== "") ? $data_nascimento : null;

        $foto_nome = null;

        if (!empty($_FILES["foto_perfil"]["name"])) {
            $erroPerfil = validarImagemUpload($_FILES["foto_perfil"], 5 * 1024 * 1024);

            if (!$erroPerfil) {
                $extensao = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));
                $foto_nome = "avatar_" . $usuario_id . "_" . time() . "." . $extensao;
                $pastaUpload = __DIR__ . "/uploads/avatars/";

                if (!is_dir($pastaUpload)) {
                    mkdir($pastaUpload, 0755, true);
                }

                if (!move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $pastaUpload . $foto_nome)) {
                    $erroPerfil = "Erro ao salvar a imagem.";
                    $foto_nome = null;
                }
            }
        }

        if (!$erroPerfil) {
            $campos = [
                "nome_artistico = :nome_artistico", "cidade_origem = :cidade_origem",
                "cidade_atual = :cidade_atual", "data_nascimento = :data_nascimento",
                "genero = :genero", "orientacao_sexual = :orientacao_sexual",
                "biografia = :biografia", "area_atuacao = :area_atuacao",
                "especialidades = :especialidades", "instagram = :instagram",
                "youtube = :youtube", "linkedin = :linkedin",
                "site_oficial = :site_oficial", "mapa_cultura = :mapa_cultura",
                "necessidades_tecnicas = :necessidades_tecnicas", "fotos_divulgacao = :fotos_divulgacao",
                "disponivel_contratacao = :disponivel_contratacao",
            ];
            $dados = [
                ":nome_artistico" => $nome_artistico, ":cidade_origem" => $cidade_origem,
                ":cidade_atual" => $cidade_atual, ":data_nascimento" => $data_nascimento,
                ":genero" => $genero, ":orientacao_sexual" => $orientacao_sexual,
                ":biografia" => $biografia, ":area_atuacao" => $area_atuacao,
                ":especialidades" => $especialidades, ":instagram" => $instagram,
                ":youtube" => $youtube, ":linkedin" => $linkedin,
                ":site_oficial" => $site_oficial, ":mapa_cultura" => $mapa_cultura,
                ":necessidades_tecnicas" => $necessidades_tecnicas, ":fotos_divulgacao" => $fotos_divulgacao,
                ":disponivel_contratacao" => $disponivel_contratacao, ":id" => $usuario_id,
            ];
            if ($foto_nome) {
                $campos[] = "foto_perfil = :foto_perfil";
                $dados[":foto_perfil"] = $foto_nome;
            }

            $stmt = $pdo->prepare("UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :id");
            $stmt->execute($dados);

            $_SESSION["usuario_nome"] = $nome_artistico;
            $sucessoPerfil = "Perfil atualizado com sucesso.";
            registrarLog($pdo, $usuario_id, "Perfil atualizado");
        }
    }

    // ---------- Novo trabalho ----------
    elseif ($acao === "novo_trabalho") {
        $titulo = trim($_POST["titulo"] ?? "");
        $categoria = trim($_POST["categoria"] ?? "");
        $conteudo = trim($_POST["conteudo"] ?? "");
        $video_youtube = trim($_POST["video_youtube"] ?? "");
        $imagem_nome = null;
        $pdf_nome = null;

        if (empty($titulo) || empty($categoria) || empty($conteudo)) {
            $erroTrab = "Título, categoria e descrição são obrigatórios.";
        } elseif (strlen($titulo) > 200) {
            $erroTrab = "O título deve ter no máximo 200 caracteres.";
        }

        if (!$erroTrab && !empty($_FILES["imagem"]["name"])) {
            $erroTrab = validarImagemUpload($_FILES["imagem"], 5 * 1024 * 1024);
            if (!$erroTrab) {
                $ext = strtolower(pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION));
                $imagem_nome = "trabalho_" . $usuario_id . "_" . time() . "." . $ext;
                $pastaImagem = __DIR__ . "/uploads/trabalhos/";
                if (!is_dir($pastaImagem)) mkdir($pastaImagem, 0755, true);
                if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $pastaImagem . $imagem_nome)) {
                    $erroTrab = "Não foi possível salvar a imagem.";
                    $imagem_nome = null;
                }
            }
        }

        if (!$erroTrab && !empty($_FILES["arquivo_pdf"]["name"])) {
            $pdf = $_FILES["arquivo_pdf"];
            $ext = strtolower(pathinfo($pdf["name"], PATHINFO_EXTENSION));
            if (($pdf["error"] ?? 1) !== UPLOAD_ERR_OK) {
                $erroTrab = "Erro ao enviar o PDF.";
            } elseif ($ext !== "pdf") {
                $erroTrab = "A apresentação deve ser um arquivo PDF.";
            } elseif ($pdf["size"] > 10 * 1024 * 1024) {
                $erroTrab = "O PDF deve ter no máximo 10MB.";
            } else {
                $pdf_nome = "apresentacao_" . $usuario_id . "_" . time() . ".pdf";
                $pastaPdf = __DIR__ . "/uploads/apresentacoes/";
                if (!is_dir($pastaPdf)) mkdir($pastaPdf, 0755, true);
                if (!move_uploaded_file($pdf["tmp_name"], $pastaPdf . $pdf_nome)) {
                    $erroTrab = "Não foi possível salvar o PDF.";
                    $pdf_nome = null;
                }
            }
        }

        if (!$erroTrab) {
            $stmt = $pdo->prepare("
                INSERT INTO posts (usuario_id, titulo, categoria, conteudo, imagem, arquivo_pdf, video_youtube)
                VALUES (:usuario_id, :titulo, :categoria, :conteudo, :imagem, :arquivo_pdf, :video_youtube)
            ");
            $stmt->execute([
                ":usuario_id" => $usuario_id, ":titulo" => $titulo, ":categoria" => $categoria,
                ":conteudo" => $conteudo, ":imagem" => $imagem_nome,
                ":arquivo_pdf" => $pdf_nome, ":video_youtube" => $video_youtube,
            ]);
            registrarLog($pdo, $usuario_id, "Trabalho publicado");
            $sucessoTrab = "Trabalho cadastrado com sucesso.";
        }
    }

    // ---------- Excluir trabalho (PRG) ----------
    elseif ($acao === "excluir_trabalho") {
        $post_id = (int) ($_POST["id"] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->execute([":id" => $post_id, ":usuario_id" => $usuario_id]);
        registrarLog($pdo, $usuario_id, "Trabalho excluído");
        header("Location: perfil.php#trabalhos");
        exit;
    }
}

// Dados atuais
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([":id" => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM posts WHERE usuario_id = :id ORDER BY criado_em DESC");
$stmt->execute([":id" => $usuario_id]);
$trabalhos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$especialidades = array_filter(array_map("trim", explode(",", $usuario["especialidades"] ?? "")));

// Abre o collapse correspondente quando há erro naquela seção.
$abrirPerfil = $erroPerfil !== "";
$abrirNovoTrab = $erroTrab !== "";

$csrf = gerarCSRF();
include "includes/header.php";
?>

<div class="container my-5">

    <!-- ============ PERFIL ============ -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="mb-0">Meu Perfil</h1>
        <div class="d-flex gap-2">
            <a href="artista.php?id=<?= (int) $usuario_id ?>" class="btn btn-outline-dark" target="_blank">
                <i class="bi bi-eye"></i> Ver perfil público
            </a>
            <button class="btn btn-circo" type="button" data-bs-toggle="collapse" data-bs-target="#formEditarPerfil">
                <i class="bi bi-pencil"></i> Editar perfil
            </button>
        </div>
    </div>

    <?php if ($sucessoPerfil): ?>
        <div class="alert alert-success"><?= htmlspecialchars($sucessoPerfil) ?></div>
    <?php endif; ?>
    <?php if ($erroPerfil): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erroPerfil) ?></div>
    <?php endif; ?>

    <!-- Modo leitura (mesma capa do perfil público) -->
    <div class="perfil-capa mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-auto text-center">
                <?= avatarHtml($usuario["foto_perfil"] ?? "", $usuario["nome_artistico"] ?? "", "capa") ?>
            </div>
            <div class="col">
                <h1 class="mb-2"><?= htmlspecialchars($usuario["nome_artistico"] ?? "") ?></h1>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <?php if (!empty($usuario["area_atuacao"])): ?>
                        <span class="badge bg-light text-dark"><?= htmlspecialchars($usuario["area_atuacao"]) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($usuario["disponivel_contratacao"])): ?>
                        <span class="badge" style="background: var(--mnc-amarelo); color: #2b2533;">
                            <i class="bi bi-check-circle-fill"></i> Disponível para contratação
                        </span>
                    <?php endif; ?>
                </div>
                <p class="mb-0">
                    <i class="bi bi-geo-alt"></i>
                    Origem: <?= htmlspecialchars($usuario["cidade_origem"] ?: "—") ?> &middot;
                    Atual: <?= htmlspecialchars($usuario["cidade_atual"] ?: "—") ?>
                </p>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-lg-9">
            <h5>Biografia</h5>
            <p><?= nl2br(htmlspecialchars($usuario["biografia"] ?: "Biografia não informada.")) ?></p>

            <?php if (count($especialidades) > 0): ?>
                <h5 class="mt-3">Especialidades</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($especialidades as $esp): ?>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($esp) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php
            $redes = [
                "site_oficial" => ["bi-globe", "Site"], "instagram" => ["bi-instagram", "Instagram"],
                "youtube" => ["bi-youtube", "YouTube"], "linkedin" => ["bi-linkedin", "LinkedIn"],
                "mapa_cultura" => ["bi-geo-alt-fill", "Mapa da Cultura"],
            ];
            $temRede = false;
            foreach ($redes as $campo => $info) { if (!empty($usuario[$campo])) { $temRede = true; break; } }
            ?>
            <?php if ($temRede): ?>
                <h5 class="mt-3">Conexões</h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($redes as $campo => $info): ?>
                        <?php if (!empty($usuario[$campo])): ?>
                            <a href="<?= htmlspecialchars($usuario[$campo]) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-dark">
                                <i class="bi <?= $info[0] ?>"></i> <?= $info[1] ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário de edição (oculto até clicar em Editar) -->
    <div class="collapse <?= $abrirPerfil ? "show" : "" ?>" id="formEditarPerfil">
        <div class="card card-body bg-light mt-3">
            <h5 class="mb-3">Editar perfil</h5>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="acao" value="salvar_perfil">

                <div class="col-md-12">
                    <label class="form-label">Nome artístico</label>
                    <input type="text" name="nome_artistico" class="form-control" required
                        value="<?= htmlspecialchars($usuario["nome_artistico"] ?? "") ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Data de nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control"
                        value="<?= htmlspecialchars($usuario["data_nascimento"] ?? "") ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade de origem</label>
                    <input type="text" name="cidade_origem" class="form-control"
                        value="<?= htmlspecialchars($usuario["cidade_origem"] ?? "") ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade atual</label>
                    <input type="text" name="cidade_atual" class="form-control"
                        value="<?= htmlspecialchars($usuario["cidade_atual"] ?? "") ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Identidade de gênero</label>
                    <?php $generos = ["cisgenero" => "Cisgênero", "transgenero" => "Transgênero", "nao_binaria" => "Não binária"]; ?>
                    <select name="genero" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($generos as $val => $rot): ?>
                            <option value="<?= $val ?>" <?= ($usuario["genero"] ?? "") === $val ? "selected" : "" ?>><?= $rot ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Orientação sexual</label>
                    <?php $orientacoes = ["heterossexual"=>"Heterossexual","homossexual"=>"Homossexual","bissexual"=>"Bissexual","assexual"=>"Assexual","pansexual"=>"Pansexual","intersexual"=>"Intersexual","queer"=>"Queer","prefiro_nao_informar"=>"Prefiro não informar"]; ?>
                    <select name="orientacao_sexual" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($orientacoes as $val => $rot): ?>
                            <option value="<?= $val ?>" <?= ($usuario["orientacao_sexual"] ?? "") === $val ? "selected" : "" ?>><?= $rot ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Privado — não aparece no perfil público.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Área de atuação (categoria principal)</label>
                    <?php $areaAtual = $usuario["area_atuacao"] ?? ""; ?>
                    <select name="area_atuacao" class="form-select">
                        <option value="">Selecione...</option>
                        <?php if ($areaAtual !== "" && !in_array($areaAtual, $AREAS_ATUACAO)): ?>
                            <option value="<?= htmlspecialchars($areaAtual) ?>" selected><?= htmlspecialchars($areaAtual) ?> (atual)</option>
                        <?php endif; ?>
                        <?php foreach ($AREAS_ATUACAO as $a): ?>
                            <option value="<?= htmlspecialchars($a) ?>" <?= $areaAtual === $a ? "selected" : "" ?>><?= htmlspecialchars($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php $selecionadas = $especialidades; include "includes/seletor-modalidades.php"; ?>

                <div class="col-md-12">
                    <label class="form-label">Biografia</label>
                    <textarea name="biografia" class="form-control" rows="4"><?= htmlspecialchars($usuario["biografia"] ?? "") ?></textarea>
                </div>

                <div class="col-md-12">
                    <hr>
                    <h6 class="text-muted"><i class="bi bi-folder2-open"></i> Material para curadoria</h6>
                    <small class="text-muted d-block mb-2">Visível apenas para curadores(as) logados(as), que podem baixar/contatar.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Necessidades técnicas</label>
                    <textarea name="necessidades_tecnicas" class="form-control" rows="3"
                        placeholder="Ex.: pé-direito mínimo, ponto de fixação para aéreos, energia, tempo de montagem..."><?= htmlspecialchars($usuario["necessidades_tecnicas"] ?? "") ?></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Fotos de divulgação (links)</label>
                    <textarea name="fotos_divulgacao" class="form-control" rows="2"
                        placeholder="Links de pastas/galerias (Drive, site, etc.), um por linha"><?= htmlspecialchars($usuario["fotos_divulgacao"] ?? "") ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-instagram"></i> Instagram</label>
                    <input type="url" name="instagram" class="form-control" value="<?= htmlspecialchars($usuario["instagram"] ?? "") ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-youtube"></i> YouTube</label>
                    <input type="url" name="youtube" class="form-control" value="<?= htmlspecialchars($usuario["youtube"] ?? "") ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-linkedin"></i> LinkedIn</label>
                    <input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($usuario["linkedin"] ?? "") ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-globe"></i> Site oficial</label>
                    <input type="url" name="site_oficial" class="form-control" value="<?= htmlspecialchars($usuario["site_oficial"] ?? "") ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Mapa da Cultura</label>
                    <input type="url" name="mapa_cultura" class="form-control" value="<?= htmlspecialchars($usuario["mapa_cultura"] ?? "") ?>">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Foto de perfil</label>
                    <input type="file" name="foto_perfil" class="form-control" accept=".jpg,.jpeg,.png">
                    <small class="text-muted">JPG, JPEG ou PNG. Máx. 5MB.</small>
                </div>

                <div class="col-md-12 form-check ms-2">
                    <input type="checkbox" name="disponivel_contratacao" class="form-check-input" id="contratacao"
                        <?= !empty($usuario["disponivel_contratacao"]) ? "checked" : "" ?>>
                    <label class="form-check-label" for="contratacao">Disponível para contratação</label>
                </div>

                <div class="col-12">
                    <button class="btn btn-circo"><i class="bi bi-check-lg"></i> Salvar Perfil</button>
                    <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#formEditarPerfil">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-5" id="trabalhos">

    <!-- ============ MEUS TRABALHOS ============ -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="secao-titulo mb-0">Meus Trabalhos</h2>
        <button class="btn btn-circo" type="button" data-bs-toggle="collapse" data-bs-target="#formNovoTrabalho">
            <i class="bi bi-plus-lg"></i> Cadastrar novo trabalho
        </button>
    </div>

    <?php if ($sucessoTrab): ?>
        <div class="alert alert-success"><?= htmlspecialchars($sucessoTrab) ?></div>
    <?php endif; ?>
    <?php if ($erroTrab): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erroTrab) ?></div>
    <?php endif; ?>

    <!-- Formulário de novo trabalho (oculto até clicar) -->
    <div class="collapse <?= $abrirNovoTrab ? "show" : "" ?>" id="formNovoTrabalho">
        <div class="card card-body bg-light mb-4">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="acao" value="novo_trabalho">

                <div class="col-md-8">
                    <label class="form-label">Nome do trabalho</label>
                    <input type="text" name="titulo" class="form-control" maxlength="200" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select name="categoria" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Descrição completa</label>
                    <textarea name="conteudo" class="form-control" rows="4" required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto do trabalho</label>
                    <input type="file" name="imagem" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apresentação em PDF</label>
                    <input type="file" name="arquivo_pdf" class="form-control" accept=".pdf">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Link do vídeo no YouTube</label>
                    <input type="url" name="video_youtube" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div class="col-12">
                    <button class="btn btn-circo">Cadastrar Trabalho</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if (count($trabalhos) === 0): ?>
            <div class="col-12">
                <div class="alert alert-light border text-center py-5">
                    <i class="bi bi-collection fs-1 d-block mb-2 text-muted"></i>
                    Você ainda não cadastrou trabalhos. Clique em “Cadastrar novo trabalho”.
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($trabalhos as $trabalho): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm card-artista">
                    <?php if (!empty($trabalho["imagem"]) && file_exists(__DIR__ . "/uploads/trabalhos/" . $trabalho["imagem"])): ?>
                        <img src="uploads/trabalhos/<?= htmlspecialchars($trabalho["imagem"]) ?>" class="card-img-top" alt="<?= htmlspecialchars($trabalho["titulo"]) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder card-img-top d-flex align-items-center justify-content-center" style="background:<?= corAvatar($trabalho["titulo"]) ?>">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <span class="badge bg-dark mb-2"><?= htmlspecialchars($trabalho["categoria"]) ?></span>
                        <h4><?= htmlspecialchars($trabalho["titulo"]) ?></h4>
                        <p><?= htmlspecialchars(mb_substr($trabalho["conteudo"], 0, 120)) ?><?= mb_strlen($trabalho["conteudo"]) > 120 ? "..." : "" ?></p>
                    </div>

                    <div class="card-footer bg-white">
                        <button class="btn btn-circo w-100 mb-2" data-bs-toggle="modal" data-bs-target="#trabalho<?= $trabalho["id"] ?>">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                        <a href="editar-trabalho.php?id=<?= $trabalho["id"] ?>" class="btn btn-sm btn-outline-dark w-100 mb-2">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form method="POST" onsubmit="return confirm('Deseja excluir este trabalho?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="acao" value="excluir_trabalho">
                            <input type="hidden" name="id" value="<?= (int) $trabalho["id"] ?>">
                            <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Excluir</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="trabalho<?= $trabalho["id"] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($trabalho["titulo"]) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <span class="badge bg-dark mb-3"><?= htmlspecialchars($trabalho["categoria"]) ?></span>
                            <?php if (!empty($trabalho["imagem"]) && file_exists(__DIR__ . "/uploads/trabalhos/" . $trabalho["imagem"])): ?>
                                <img src="uploads/trabalhos/<?= htmlspecialchars($trabalho["imagem"]) ?>" class="img-fluid rounded mb-3">
                            <?php endif; ?>
                            <p><?= nl2br(htmlspecialchars($trabalho["conteudo"])) ?></p>
                            <?php $embed = youtubeEmbedUrl($trabalho["video_youtube"] ?? ""); ?>
                            <?php if ($embed !== ""): ?>
                                <div class="ratio ratio-16x9 my-3"><iframe src="<?= htmlspecialchars($embed) ?>" allowfullscreen></iframe></div>
                            <?php endif; ?>
                            <?php if (!empty($trabalho["arquivo_pdf"]) && file_exists(__DIR__ . "/uploads/apresentacoes/" . $trabalho["arquivo_pdf"])): ?>
                                <a href="download.php?file=<?= urlencode($trabalho["arquivo_pdf"]) ?>" class="btn btn-dark">
                                    <i class="bi bi-file-earmark-pdf"></i> Baixar apresentação
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
