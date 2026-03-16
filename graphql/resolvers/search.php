<?php

function searchGlobal($pdo, $keyword)
{
    $keyword = "%" . strtolower($keyword) . "%";

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            username,
            email,
            role
        FROM users
        WHERE
            LOWER(name) LIKE :keyword
            OR LOWER(username) LIKE :keyword
            OR LOWER(email) LIKE :keyword
        LIMIT 10
    ");

    $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);

    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "users" => $users
    ];
}
