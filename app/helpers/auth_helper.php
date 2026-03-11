<?php

function requireLogin(): int
{
    $userId = intval($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    return $userId;
}
