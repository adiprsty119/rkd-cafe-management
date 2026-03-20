<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function getUserById(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT username, name, login_method, foto
        FROM users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $userId
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}


function getDisplayName(?array $user): string
{
    if (!$user) return 'User';

    $name = ($user['login_method'] ?? '') === 'google'
        ? ($user['name'] ?? 'User')
        : ($user['username'] ?? 'User');

    return ucwords($name);
}

function getUserRoleById(PDO $pdo, int $userId): string
{
    $stmt = $pdo->prepare("
        SELECT r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $userId
    ]);

    $role = $stmt->fetchColumn();

    // 🔐 fallback kalau null
    if (!$role) {
        return 'guest';
    }

    return $role;
}
