<?php

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {

        $host = getenv("DB_HOST") ?: "localhost";
        $dbname = getenv("DB_NAME") ?: "rkd_cafe";
        $user = getenv("DB_USER") ?: "root";
        $password = getenv("DB_PASS") ?: "";

        try {

            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {

            http_response_code(500);
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}
