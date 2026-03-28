<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

/* ==========================
   SESSION HELPER
========================== */
function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/* ==========================
   TOAST HELPER (FLASH)
========================== */
function setToast(string $type, string $message): void
{
    ensureSession();

    $_SESSION['toast'] = [
        "type" => $type,
        "message" => $message
    ];
}

/* ==========================
   CLEAR AUTH SESSION (SAFE)
========================== */
function clearAuthSession(): void
{
    ensureSession();

    unset(
        $_SESSION['user_id'],
        $_SESSION['username'],
        $_SESSION['role'],
        $_SESSION['role_id'],
        $_SESSION['permissions'],
        $_SESSION['fingerprint']
    );
}

/* ==========================
   FULL DESTROY (LOGOUT ONLY)
========================== */
function destroySession(): void
{
    ensureSession();

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
}

/* ==========================
   REDIRECT LOGIN
========================== */
function redirectToLogin(?string $message = null): void
{
    ensureSession();

    if ($message) {
        setToast("warning", $message);
    }

    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';

    // hapus remember cookie
    setcookie("remember", "", time() - 3600, "/");

    session_write_close(); // 🔥 penting untuk persist

    header("Location: /rkd-cafe/public/index.php");
    exit;
}

/* ==========================
   BASE AUTH CHECK
========================== */
function requireLogin(): int
{
    ensureSession();

    if (
        empty($_SESSION['user_id']) ||
        (!empty($_SESSION['is_remember_login']) && empty($_SESSION['login_verified']))
    ) {
        // ❗ jangan destroy → cukup clear auth
        clearAuthSession();

        redirectToLogin("Silakan login untuk melanjutkan");
    }

    validateFingerprint();

    return (int) $_SESSION['user_id'];
}

/* ==========================
   ROLE REDIRECT
========================== */
function redirectByRole(?string $roleName = null): void
{
    ensureSession();

    $roleName = $roleName ?? ($_SESSION['role'] ?? 'guest');

    switch ($roleName) {

        case 'admin':
            header("Location: /rkd-cafe/resources/views/dashboard/admin.php");
            break;

        case 'kasir':
            header("Location: /rkd-cafe/resources/views/dashboard/kasir.php");
            break;

        case 'owner':
            header("Location: /rkd-cafe/resources/views/dashboard/owner.php");
            break;

        default:
            clearAuthSession();
            redirectToLogin("Session tidak valid");
    }

    exit;
}

/* ==========================
   FINGERPRINT VALIDATION
========================== */
function validateFingerprint(): void
{
    ensureSession();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $ipParts = explode('.', $ip);
    $ipPartial = count($ipParts) >= 2
        ? $ipParts[0] . '.' . $ipParts[1]
        : $ip;

    $currentFingerprint = hash('sha256', $ipPartial . $ua);

    if (
        empty($_SESSION['fingerprint']) ||
        !hash_equals($_SESSION['fingerprint'], $currentFingerprint)
    ) {
        clearAuthSession();

        redirectToLogin("Sesi tidak valid, silakan login kembali");
    }
}

/* ==========================
   PERMISSION CHECK
========================== */
function hasPermission(string $permission): bool
{
    $permissions = $_SESSION['permissions'] ?? [];

    return in_array(
        strtolower(str_replace(' ', '_', $permission)),
        $permissions
    );
}

function requirePermission(string $permission): void
{
    requireLogin();

    if (!hasPermission($permission)) {
        clearAuthSession();

        redirectToLogin("Anda tidak memiliki akses ke halaman tersebut");
    }
}

/* ==========================
   GUEST ONLY
========================== */
function guestOnly(): void
{
    ensureSession();

    if (!empty($_SESSION['user_id'])) {
        redirectByRole();
    }
}
