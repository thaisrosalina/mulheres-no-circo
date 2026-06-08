<?php
require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/csrf.php";
require_once "includes/log.php";

protegerPagina();
protegerAdmin();

$meu_id = (int) $_SESSION["usuario_id"];

// ----- Ações administrativas (todas via POST + CSRF) -----
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF($_POST["csrf_token"] ?? "")) {
        die("Token CSRF inválido.");
    }

    $acao = $_POST["acao"] ?? "";
    $id = (int) ($_POST["id"] ?? 0);

    if ($acao === "excluir_usuario" && $id !== $meu_id) {
        $pdo->prepare("DELETE FROM usuarios WHERE id = :id")->execute([":id" => $id]);
        registrarLog($pdo, $meu_id, "Usuária excluída (ID {$id})");
    } elseif ($acao === "toggle_role" && $id !== $meu_id) {
        // Alterna entre 'admin' e 'user'.
        $stmt = $pdo->prepare("UPDATE usuarios SET role = IF(role = 'admin', 'user', 'admin') WHERE id = :id");
        $stmt->execute([":id" => $id]);
        registrarLog($pdo, $meu_id, "Papel alterado (ID {$id})");
    } elseif ($acao === "excluir_post") {
        $pdo->prepare("DELETE FROM posts WHERE id = :id")->execute([":id" => $id]);
        registrarLog($pdo, $meu_id, "Trabalho moderado/excluído (ID {$id})");
    } elseif ($acao === "msg_lida") {
        $pdo->prepare("UPDATE mensagens_contato SET lida = 1 WHERE id = :id")->execute([":id" => $id]);
    } elseif ($acao === "excluir_msg") {
        $pdo->prepare("DELETE FROM mensagens_contato WHERE id = :id")->execute([":id" => $id]);
    }

    // Âncora de retorno saneada (evita injeção no cabeçalho Location).
    $voltar = preg_replace('/[^#a-z\-]/', '', strtolower($_POST["voltar"] ?? ""));
    header("Location: admin.php" . $voltar);
    exit;
}

// ----- Listagem de usuárias: busca + paginação -----
$q = trim($_GET["q"] ?? "");
$pagina = max(1, (int) ($_GET["pagina"] ?? 1));
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

$whereSql = "";
$params = [];
if ($q !== "") {
    $whereSql = "WHERE nome_artistico LIKE :q OR email LIKE :q";
    $params[":q"] = "%$q%";
}

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM usuarios $whereSql");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / $porPagina));

$stmt = $pdo->prepare("
    SELECT id, nome_artistico, email, cidade_atual, role, criado_em
    FROM usuarios
    $whereSql
    ORDER BY criado_em DESC
    LIMIT $porPagina OFFSET $offset
");
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----- Trabalhos (para moderação) -----
$trabalhos = $pdo->query("
    SELECT posts.id, posts.titulo, posts.categoria, posts.criado_em, usuarios.nome_artistico
    FROM posts
    LEFT JOIN usuarios ON posts.usuario_id = usuarios.id
    ORDER BY posts.criado_em DESC
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

// ----- Produtores(as) / Curadores(as) -----
$produtores = $pdo->query("
    SELECT id, nome_artistico, email, organizacao, telefone, quer_novidades, canal_novidades, criado_em
    FROM usuarios
    WHERE role = 'curador'
    ORDER BY criado_em DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ----- Mensagens de contato -----
$mensagens = $pdo->query("
    SELECT * FROM mensagens_contato ORDER BY criado_em DESC LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
$naoLidas = 0;
foreach ($mensagens as $m) {
    if (!$m["lida"]) $naoLidas++;
}

// ----- Logs -----
$logs = $pdo->query("
    SELECT logs_acesso.*, usuarios.nome_artistico
    FROM logs_acesso
    LEFT JOIN usuarios ON logs_acesso.usuario_id = usuarios.id
    ORDER BY logs_acesso.criado_em DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

$csrf = gerarCSRF();
include "includes/header.php";
?>

<div class="container my-5">
    <h1>Administração</h1>
    <p class="lead">Gestão de usuárias, trabalhos, mensagens e logs.</p>

    <ul class="nav nav-tabs mt-4" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-usuarias">Usuárias (<?= $total ?>)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trabalhos">Trabalhos (<?= count($trabalhos) ?>)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-produtores">Produtores (<?= count($produtores) ?>)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-mensagens">Mensagens <?php if ($naoLidas): ?><span class="badge bg-danger"><?= $naoLidas ?></span><?php endif; ?></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-logs">Logs</button></li>
    </ul>

    <div class="tab-content border border-top-0 p-3">

        <!-- USUÁRIAS -->
        <div class="tab-pane fade show active" id="tab-usuarias">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou e-mail" value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-circo"><i class="bi bi-search"></i> Buscar</button>
                    <?php if ($q): ?><a href="admin.php" class="btn btn-link">Limpar</a><?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Nome</th><th>Email</th><th>Cidade</th><th>Papel</th><th>Criado</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= $u["id"] ?></td>
                                <td><?= htmlspecialchars($u["nome_artistico"]) ?></td>
                                <td><?= htmlspecialchars($u["email"]) ?></td>
                                <td><?= htmlspecialchars($u["cidade_atual"] ?? "") ?></td>
                                <td>
                                    <span class="badge <?= $u["role"] === "admin" ? "bg-warning text-dark" : "bg-secondary" ?>">
                                        <?= htmlspecialchars($u["role"]) ?>
                                    </span>
                                </td>
                                <td><?= date("d/m/Y", strtotime($u["criado_em"])) ?></td>
                                <td class="d-flex gap-1 flex-wrap">
                                    <a href="artista.php?id=<?= $u["id"] ?>" class="btn btn-sm btn-dark">Ver</a>
                                    <?php if ($u["id"] != $meu_id): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="acao" value="toggle_role">
                                            <input type="hidden" name="id" value="<?= $u["id"] ?>">
                                            <button class="btn btn-sm btn-outline-dark" title="Alternar papel">
                                                <?= $u["role"] === "admin" ? "Rebaixar" : "Promover" ?>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Excluir esta usuária? Irreversível.')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="acao" value="excluir_usuario">
                                            <input type="hidden" name="id" value="<?= $u["id"] ?>">
                                            <button class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small align-self-center">(você)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <nav><ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                        <li class="page-item <?= $p === $pagina ? "active" : "" ?>">
                            <a class="page-link" href="admin.php?pagina=<?= $p ?><?= $q !== "" ? "&q=" . urlencode($q) : "" ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            <?php endif; ?>
        </div>

        <!-- TRABALHOS -->
        <div class="tab-pane fade" id="tab-trabalhos">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark"><tr><th>ID</th><th>Título</th><th>Categoria</th><th>Autora</th><th>Data</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php if (count($trabalhos) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted">Nenhum trabalho cadastrado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($trabalhos as $t): ?>
                            <tr>
                                <td><?= $t["id"] ?></td>
                                <td><?= htmlspecialchars($t["titulo"]) ?></td>
                                <td><?= htmlspecialchars($t["categoria"] ?? "") ?></td>
                                <td><?= htmlspecialchars($t["nome_artistico"] ?? "—") ?></td>
                                <td><?= date("d/m/Y", strtotime($t["criado_em"])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Excluir este trabalho?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="acao" value="excluir_post">
                                        <input type="hidden" name="id" value="<?= $t["id"] ?>">
                                        <input type="hidden" name="voltar" value="#tab-trabalhos">
                                        <button class="btn btn-sm btn-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRODUTORES -->
        <div class="tab-pane fade" id="tab-produtores">
            <p class="text-muted">Produtores(as)/curadores(as) cadastrados. Use o canal indicado para enviar novidades manualmente.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark"><tr><th>Nome</th><th>Organização</th><th>E-mail</th><th>Telefone</th><th>Novidades</th><th>Canal</th><th>Desde</th></tr></thead>
                    <tbody>
                        <?php if (count($produtores) === 0): ?>
                            <tr><td colspan="7" class="text-center text-muted">Nenhum produtor cadastrado ainda.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($produtores as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p["nome_artistico"]) ?></td>
                                <td><?= htmlspecialchars($p["organizacao"] ?? "") ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($p["email"]) ?>"><?= htmlspecialchars($p["email"]) ?></a></td>
                                <td><?= htmlspecialchars($p["telefone"] ?? "") ?></td>
                                <td><?= $p["quer_novidades"] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></td>
                                <td><?= htmlspecialchars($p["canal_novidades"] ?? "") ?></td>
                                <td><?= date("d/m/Y", strtotime($p["criado_em"])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MENSAGENS -->
        <div class="tab-pane fade" id="tab-mensagens">
            <?php if (count($mensagens) === 0): ?>
                <p class="text-muted">Nenhuma mensagem recebida.</p>
            <?php endif; ?>
            <?php foreach ($mensagens as $m): ?>
                <div class="card mb-2 <?= $m["lida"] ? "" : "border-warning" ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">
                                <?= htmlspecialchars($m["assunto"] ?: "(sem assunto)") ?>
                                <?php if (!$m["lida"]): ?><span class="badge bg-warning text-dark">nova</span><?php endif; ?>
                            </h6>
                            <small class="text-muted"><?= date("d/m/Y H:i", strtotime($m["criado_em"])) ?></small>
                        </div>
                        <p class="mb-1"><strong><?= htmlspecialchars($m["nome"]) ?></strong> &lt;<?= htmlspecialchars($m["email"]) ?>&gt;</p>
                        <p class="mb-2"><?= nl2br(htmlspecialchars($m["mensagem"])) ?></p>
                        <div class="d-flex gap-1">
                            <?php if (!$m["lida"]): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <input type="hidden" name="acao" value="msg_lida">
                                    <input type="hidden" name="id" value="<?= $m["id"] ?>">
                                    <input type="hidden" name="voltar" value="#tab-mensagens">
                                    <button class="btn btn-sm btn-outline-dark">Marcar como lida</button>
                                </form>
                            <?php endif; ?>
                            <a href="mailto:<?= htmlspecialchars($m["email"]) ?>" class="btn btn-sm btn-dark">Responder</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Excluir mensagem?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="acao" value="excluir_msg">
                                <input type="hidden" name="id" value="<?= $m["id"] ?>">
                                <input type="hidden" name="voltar" value="#tab-mensagens">
                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- LOGS -->
        <div class="tab-pane fade" id="tab-logs">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-dark"><tr><th>Usuária</th><th>Evento</th><th>IP</th><th>Data</th></tr></thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log["nome_artistico"] ?? "Não identificada") ?></td>
                                <td><?= htmlspecialchars($log["evento"]) ?></td>
                                <td><?= htmlspecialchars($log["ip"]) ?></td>
                                <td><?= date("d/m/Y H:i", strtotime($log["criado_em"])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include "includes/footer.php"; ?>
