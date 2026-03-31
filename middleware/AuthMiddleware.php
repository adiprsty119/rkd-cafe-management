<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/helpers/auth_helper.php';

/* ==========================
   SESSION TIMEOUT (RINGAN)
========================== */
$timeout = 1800;

if (
    isset($_SESSION['last_activity']) &&
    time() - $_SESSION['last_activity'] > $timeout
) {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    redirectToLogin("Sesi Anda telah berakhir, silakan login kembali");
}

require_once __DIR__ . '/../config/database.php';
try {
    $pdo = getPDO();
} catch (Exception $e) {
    session_destroy();
    redirectToLogin("Terjadi kesalahan sistem");
}

/* ==========================
   AUTO LOGIN VIA REMEMBER
========================== */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember'])) {

    $cookie = $_COOKIE['remember'];

    if (preg_match('/^[a-f0-9]{18}:[a-f0-9]{66}$/', $cookie)) {

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

        if (
            $user &&
            $user['status'] === 'active' &&
            (!$user['locked_until'] || strtotime($user['locked_until']) < time()) &&
            password_verify($validator, $user['remember_token'])
        ) {

            session_regenerate_id(true);

            /* ==========================
               SESSION INIT
            ========================== */

            $_SESSION['last_activity'] = time();

            unset($_SESSION['csrf_token']);

            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ipParts = explode('.', $ip);
            $ipPartial = count($ipParts) >= 2
                ? $ipParts[0] . '.' . $ipParts[1]
                : $ip;

            $_SESSION['fingerprint'] = hash(
                'sha256',
                $ipPartial . ($_SERVER['HTTP_USER_AGENT'] ?? '')
            );

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role'] = $user['role_name'] ?? 'guest';
            $_SESSION['status'] = $user['status'];
            $_SESSION['is_remember_login'] = true;
            $_SESSION['login_verified'] = false;
            $_SESSION['sidebar_collapsed'] = $user['sidebar_collapsed'];

            /* ==========================
               ROTATE REMEMBER TOKEN
            ========================== */
            $newSelector = bin2hex(random_bytes(9));
            $newValidator = bin2hex(random_bytes(33));

            $stmt = $pdo->prepare("
                UPDATE users
                SET remember_selector = ?, remember_token = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $newSelector,
                password_hash($newValidator, PASSWORD_DEFAULT),
                $user['id']
            ]);

            setcookie(
                "remember",
                $newSelector . ':' . $newValidator,
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        } else {
            setcookie(
                "remember",
                "",
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    } else {
        setcookie("remember", "", time() - 3600, "/");
    }
}

/* ==========================
   CEK LOGIN WAJIB
========================== */
requireLogin();

/* ==========================
   VALIDASI STATUS AKUN
========================== */
if (($_SESSION['status'] ?? null) !== 'active') {
    session_destroy();
    redirectToLogin("Akun belum aktif");
}

/* ==========================
   VALIDASI BUSINESS (NON ADMIN)
========================== */
if (
    strtolower($_SESSION['role'] ?? '') !== 'admin' &&
    empty($_SESSION['business_id'])
) {
    session_destroy();
    redirectToLogin("Akun tidak terhubung dengan bisnis");
}

/* ==========================
   UPDATE LAST ACTIVITY
========================== */
$_SESSION['last_activity'] = time();


/* ==========================
   CSRF TOKEN
========================== */
if (
    !isset($_SESSION['csrf_token']) ||
    !isset($_SESSION['csrf_created']) ||
    time() - $_SESSION['csrf_created'] > 1800
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_created'] = time();
}

/* ==========================
   LOAD USER DATA
========================== */
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        u.username,
        u.role_id,
        r.name AS role_name,
        u.sidebar_collapsed,
        u.business_id,
        u.status
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.id = :id
    LIMIT 1
");

$stmt->execute(['id' => $userId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentUser) {
    $_SESSION['username'] = $currentUser['username'];
    $_SESSION['role_id'] = $currentUser['role_id'];
    $_SESSION['role'] = $_SESSION['role'] ?? 'guest';
    $_SESSION['business_id'] = $currentUser['business_id'];
    $_SESSION['status'] = $currentUser['status'];
    $_SESSION['sidebar_collapsed'] = $currentUser['sidebar_collapsed'];
    $sidebarCollapsed = (bool) $currentUser['sidebar_collapsed'];
} else {
    session_destroy();
    requireLogin();
}

/* ==========================
   PERMISSIONS CACHE
========================== */
if (
    !isset($_SESSION['permissions']) ||
    !isset($_SESSION['perm_loaded_at']) ||
    time() - $_SESSION['perm_loaded_at'] > 300
) {
    $stmt = $pdo->prepare("
        SELECT p.name
        FROM permissions p
        JOIN role_permissions rp ON rp.permission_id = p.id
        WHERE rp.role_id = ?
    ");

    $stmt->execute([$_SESSION['role_id']]);

    $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $_SESSION['perm_loaded_at'] = time();
}

if (!empty($_SESSION['permissions'])) {
    $_SESSION['permissions'] = array_map(
        fn($p) => strtolower(trim($p)),
        $_SESSION['permissions']
    );
}

/* ==========================
   MENU CACHE
========================== */
if (!isset($_SESSION['menu_config'])) {
    $_SESSION['menu_config'] = require __DIR__ . '/../config/sidebar_menu.php';
}
