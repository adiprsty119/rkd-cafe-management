<?php

session_start();

require_once __DIR__ . '/../config/database.php';

/* ==========================
   AUTO LOGIN VIA COOKIE
========================== */

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {

    $userId = intval($_COOKIE['remember_user']);

    $stmt = $pdo->prepare(
        "SELECT id, username, role, sidebar_collapsed 
         FROM users 
         WHERE id = :id LIMIT 1"
    );

    $stmt->execute([
        'id' => $userId
    ]);

    $user = $stmt->fetch();

    if ($user) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['sidebar_collapsed'] = $user['sidebar_collapsed'];
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
   LOAD USER DATA
========================== */

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT username, role, sidebar_collapsed
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    'id' => $userId
]);

$currentUser = $stmt->fetch();

/* ==========================
   UPDATE SESSION
========================== */

if ($currentUser) {

    $_SESSION['username'] = $currentUser['username'];
    $_SESSION['role'] = $currentUser['role'];
    $_SESSION['sidebar_collapsed'] = $currentUser['sidebar_collapsed'];

    $sidebarCollapsed = $currentUser['sidebar_collapsed'] == 1;
}
