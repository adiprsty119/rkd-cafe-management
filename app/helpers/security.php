<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function isBruteForce($username, $ip, $userId = null): bool
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM login_attempts
        WHERE (username = :username OR ip_address = :ip" .
        ($userId ? " OR user_id = :user_id" : "") . ")
        AND attempt_time > (NOW() - INTERVAL 5 MINUTE)
    ");

    $params = [
        'username' => $username,
        'ip' => $ip
    ];

    if ($userId) {
        $params['user_id'] = $userId;
    }

    $stmt->execute($params);

    return $stmt->fetchColumn() >= 5;
}

function recordLoginAttempt($username, $ip, $userId = null): void
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (username, ip_address, user_id, attempt_time)
        VALUES (:username, :ip, :user_id, NOW())
    ");

    $stmt->execute([
        'username' => $username,
        'ip' => $ip,
        'user_id' => $userId
    ]);
}

function cleanupLoginAttempts(): void
{
    global $pdo;

    $pdo->exec("
        DELETE FROM login_attempts
        WHERE attempt_time < (NOW() - INTERVAL 1 DAY)
    ");
}
