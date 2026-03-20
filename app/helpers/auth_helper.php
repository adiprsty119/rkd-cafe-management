<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function requireLogin(): int
{
    if (empty($_SESSION['user_id'])) {
        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    // 🔐 Session fingerprint check (anti hijacking)
    $currentFingerprint = hash(
        'sha256',
        ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '')
    );

    if (!isset($_SESSION['fingerprint']) || !hash_equals($_SESSION['fingerprint'], $currentFingerprint)) {
        session_destroy();
        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    return (int) $_SESSION['user_id'];
}
