<?php

/**
 * MODELO de credenciais do banco.
 *
 * PARA O DEPLOY (InfinityFree):
 *   1. Copie este arquivo para "db-credentials.php" (mesma pasta).
 *   2. Preencha com os dados do painel do InfinityFree
 *      (MySQL Databases → mostra host, usuário, senha e nome do banco).
 *   3. NÃO versione o db-credentials.php (já está no .gitignore).
 *
 * Localmente (XAMPP) você nem precisa deste arquivo: sem db-credentials.php,
 * o sistema usa root / sem senha / banco mulheres_no_circo.
 */

return [
    "host"     => "sqlXXX.infinityfree.com", // ex.: sql123.infinityfree.com
    "dbname"   => "epiz_XXXXXXX_mulheres",    // nome do banco no InfinityFree
    "username" => "epiz_XXXXXXX",             // usuário do InfinityFree
    "password" => "SUA_SENHA_AQUI",
];
