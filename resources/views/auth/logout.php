<?php

session_start();

/* HAPUS SEMUA SESSION */
$_SESSION = [];

/* HAPUS COOKIE SESSION */
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

/* HAPUS COOKIE REMEMBER ME */
setcookie("remember_user", "", time() - 3600, "/");

/* DESTROY SESSION */
session_destroy();

/* BUAT SESSION BARU UNTUK TOAST */
session_start();
session_regenerate_id(true);

$_SESSION['toast'] = [
    "type" => "success",
    "message" => "Logout berhasil."
];

/* REDIRECT INDEX */
header("Location: /rkd-cafe/public/index.php");
exit;
