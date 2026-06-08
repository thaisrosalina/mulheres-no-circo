<?php

function registrarLog($pdo, $usuario_id, $evento)
{
    $ip = $_SERVER["REMOTE_ADDR"] ?? "desconhecido";
    $user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "desconhecido";

    $stmt = $pdo->prepare("
        INSERT INTO logs_acesso (usuario_id, evento, ip, user_agent)
        VALUES (:usuario_id, :evento, :ip, :user_agent)
    ");

    $stmt->execute([
        ":usuario_id" => $usuario_id,
        ":evento" => $evento,
        ":ip" => $ip,
        ":user_agent" => $user_agent
    ]);
}