<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function getUserAvatar(): string
{
    $default = "/rkd-cafe/public/assets/images/user.png";

    if (session_status() !== PHP_SESSION_ACTIVE) {
        return $default;
    }

    $foto = $_SESSION['foto'] ?? null;
    $method = $_SESSION['login_method'] ?? 'manual';

    if (!$foto) {
        return $default;
    }

    /* ==========================
       GOOGLE AVATAR (URL)
    ========================== */

    if ($method === 'google' && filter_var($foto, FILTER_VALIDATE_URL)) {

        $parsed = parse_url($foto);

        if (
            !isset($parsed['scheme'], $parsed['host']) ||
            !in_array($parsed['scheme'], ['http', 'https'])
        ) {
            return $default;
        }

        if (strpos($parsed['host'], 'googleusercontent.com') === false) {
            return $default;
        }

        $url = preg_replace('/=s\d+-c$/', '=s256-c', $foto);

        return $url;
    }

    /* ==========================
       LOCAL AVATAR (CACHED)
    ========================== */

    $filename = basename($foto);

    if (!$filename) {
        return $default;
    }

    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        return $default;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowedExt)) {
        return $default;
    }

    return "/rkd-cafe/storage/avatars/google/" . $filename;
}
