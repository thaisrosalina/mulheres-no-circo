<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["usuario_id"]) && isset($_COOKIE["remember_token"])) {
    $token = $_COOKIE["remember_token"];

    $stmt = $pdo->prepare("
        SELECT id, nome_artistico, role
        FROM usuarios
        WHERE remember_token = :token
        LIMIT 1
    ");

    $stmt->execute([
        ":token" => $token
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome_artistico"];
        $_SESSION["usuario_role"] = $usuario["role"];
    }
}

function usuarioLogado()
{
    return isset($_SESSION["usuario_id"]);
}

function protegerPagina()
{
    if (!usuarioLogado()) {
        header("Location: login.php");
        exit;
    }
}

function protegerAdmin()
{
    if (!isset($_SESSION["usuario_role"]) || $_SESSION["usuario_role"] !== "admin") {
        header("Location: dashboard.php");
        exit;
    }
}

/**
 * Restringe a página a curadores(as) logados(as).
 */
function protegerCurador()
{
    protegerPagina();
    $role = $_SESSION["usuario_role"] ?? "";
    // Curadores acessam a área; admin também pode (para visualizar/testar).
    if ($role !== "curador" && $role !== "admin") {
        header("Location: dashboard.php");
        exit;
    }
}

/**
 * Destino pós-login conforme o papel.
 */
function destinoPorPapel($role)
{
    if ($role === "curador") {
        return "area-curador.php";
    }
    return "dashboard.php";
}