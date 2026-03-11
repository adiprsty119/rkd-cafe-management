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
       GOOGLE AVATAR
    ========================== */

    if ($method === 'google') {

        $url = filter_var($foto, FILTER_VALIDATE_URL);

        if (!$url) {
            return $default;
        }

        /* normalize google image size */
        $url = preg_replace('/=s\d+-c$/', '=s256-c', $url);

        return $url;
    }

    /* ==========================
       LOCAL AVATAR
    ========================== */

    $filename = basename($foto);

    if (!$filename) {
        return $default;
    }

    return "/rkd-cafe/public/storage/users/" . $filename;
}
