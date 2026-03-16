<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$pdo = getPDO(); // ← TAMBAHKAN BARIS INI

/* ==========================
   AUTO LOGIN VIA REMEMBER TOKEN
========================== */

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    $stmt = $pdo->query("
        SELECT 
            id,
            username,
            role,
            sidebar_collapsed,
            remember_token
        FROM users
        WHERE remember_token IS NOT NULL
    ");

    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if (password_verify($token, $user['remember_token'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['sidebar_collapsed'] = $user['sidebar_collapsed'];

            break;
        }
    }
}

/* ==========================
   CEK SESSION LOGIN
========================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit();
}

/* ==========================
   CSRF TOKEN GENERATION
========================== */

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ==========================
   LOAD USER DATA
========================== */

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        username,
        role,
        sidebar_collapsed
    FROM users
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    'id' => $userId
]);

$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

/* ==========================
   UPDATE SESSION
========================== */

if ($currentUser) {

    $_SESSION['username'] = $currentUser['username'];
    $_SESSION['role'] = $currentUser['role'];
    $_SESSION['sidebar_collapsed'] = $currentUser['sidebar_collapsed'];

    $sidebarCollapsed = $currentUser['sidebar_collapsed'] == 1;
} else {

    /* USER SUDAH DIHAPUS */

    session_destroy();

    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit();
}
