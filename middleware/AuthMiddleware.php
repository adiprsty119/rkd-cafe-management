<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

session_start();

require_once __DIR__ . '/../config/database.php';

$pdo = getPDO(); // ← TAMBAHKAN BARIS INI

/* ==========================
   AUTO LOGIN VIA REMEMBER TOKEN
========================== */

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember'])) {

    $cookie = $_COOKIE['remember'];

    // Validasi format cookie
    if (strpos($cookie, ':') !== false) {

        list($selector, $validator) = explode(':', $cookie);

        $stmt = $pdo->prepare("
            SELECT u.*, r.name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.remember_selector = ?
            LIMIT 1
        ");

        $stmt->execute([$selector]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($validator, $user['remember_token'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role'] = $user['role_name'] ?? 'guest';
            $_SESSION['sidebar_collapsed'] = $user['sidebar_collapsed'];

            // LOAD PERMISSIONS
            $stmt = $pdo->prepare("
                SELECT p.name
                FROM permissions p
                JOIN role_permissions rp ON rp.permission_id = p.id
                WHERE rp.role_id = ?
            ");

            $stmt->execute([$user['role_id']]);
            $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
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
$stmt = $pdo->prepare("SELECT u.username, u.role_id, r.name AS role_name, u.sidebar_collapsed FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);

$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

/* ==========================
   UPDATE SESSION
========================== */

if ($currentUser) {

    $_SESSION['username'] = $currentUser['username'];
    $_SESSION['role_id'] = $currentUser['role_id'];
    $_SESSION['role'] = $currentUser['role_name'];
    $_SESSION['sidebar_collapsed'] = $currentUser['sidebar_collapsed'];

    $sidebarCollapsed = (bool) $currentUser['sidebar_collapsed'];
} else {

    session_destroy();

    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit();
}

// LOAD PERMISSIONS
$stmt = $pdo->prepare("
    SELECT p.name
    FROM permissions p
    JOIN role_permissions rp ON rp.permission_id = p.id
    WHERE rp.role_id = ?
");

$stmt->execute([$_SESSION['role_id']]);
$permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
$_SESSION['permissions'] = !empty($permissions)
    ? array_map(fn($p) => strtolower(trim($p)), $permissions)
    : ($_SESSION['permissions'] ?? []);


// ==========================
// CACHE MENU CONFIG
// ==========================
if (!isset($_SESSION['menu_config'])) {
    $_SESSION['menu_config'] = require __DIR__ . '/../config/sidebar_menu.php';
}

function hasPermission($permission): bool
{
    $permissions = $_SESSION['permissions'] ?? [];

    return in_array(
        strtolower(str_replace(' ', '_', $permission)),
        $permissions
    );
}
