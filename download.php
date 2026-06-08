<?php
/**
 * Download protegido de material das artistas (apresentações em PDF).
 * Só usuários logados (artistas, produtores ou admin) podem baixar.
 */
require_once "includes/auth.php";

protegerPagina(); // exige login

$file = basename($_GET["file"] ?? ""); // remove qualquer caminho (anti path traversal)

// Só nomes no padrão das apresentações geradas pelo sistema.
if (!preg_match('/^apresentacao_\d+_\d+\.pdf$/', $file)) {
    http_response_code(404);
    die("Arquivo não encontrado.");
}

$caminho = __DIR__ . "/uploads/apresentacoes/" . $file;

if (!is_file($caminho)) {
    http_response_code(404);
    die("Arquivo não encontrado.");
}

header("Content-Type: application/pdf");
header('Content-Disposition: attachment; filename="' . $file . '"');
header("Content-Length: " . filesize($caminho));
header("X-Content-Type-Options: nosniff");
readfile($caminho);
exit;
