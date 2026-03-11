<?php

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

    return "/rkd-cafe/storage/avatars/google/" . $filename;
}
