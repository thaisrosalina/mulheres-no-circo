<?php

/**
 * Conexão PDO.
 * As credenciais ficam em config/db-credentials.php (NÃO versionado).
 * Se o arquivo não existir, usa os padrões locais (XAMPP).
 * Para o deploy: copie db-credentials.example.php para db-credentials.php
 * e preencha com os dados do InfinityFree.
 */

$credenciaisFile = __DIR__ . "/db-credentials.php";

if (file_exists($credenciaisFile)) {
    $cfg = require $credenciaisFile;
} else {
    $cfg = [
        "host" => "localhost",
        "dbname" => "mulheres_no_circo",
        "username" => "root",
        "password" => "",
    ];
}

try {
    $pdo = new PDO(
        "mysql:host={$cfg["host"]};dbname={$cfg["dbname"]};charset=utf8mb4",
        $cfg["username"],
        $cfg["password"]
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Não expõe detalhes (host/credenciais) ao usuário em produção.
    error_log("Erro de conexão com o banco: " . $e->getMessage());
    http_response_code(500);
    die("Não foi possível conectar ao banco de dados no momento. Tente novamente mais tarde.");
}
