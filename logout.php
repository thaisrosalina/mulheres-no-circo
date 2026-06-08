<?php
require_once "config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["usuario_id"])) {
    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET remember_token = NULL
        WHERE id = :id
    ");

    $stmt->execute([
        ":id" => $_SESSION["usuario_id"]
    ]);
}

setcookie("remember_token", "", time() - 3600, "/");

session_unset();
session_destroy();

header("Location: login.php");
exit;