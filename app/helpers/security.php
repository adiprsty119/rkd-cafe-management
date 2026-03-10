<?php

function isBruteForce($username, $ip)
{

    global $pdo;

    $stmt = $pdo->prepare("
SELECT COUNT(*)
FROM login_attempts
WHERE username = ?
AND ip_address = ?
AND attempt_time > (NOW() - INTERVAL 5 MINUTE)
");

    $stmt->execute([$username, $ip]);

    return $stmt->fetchColumn() >= 5;
}

function recordLoginAttempt($username, $ip)
{

    global $pdo;

    $stmt = $pdo->prepare("
INSERT INTO login_attempts (username,ip_address,attempt_time)
VALUES (?, ?, NOW())
");

    $stmt->execute([$username, $ip]);
}
