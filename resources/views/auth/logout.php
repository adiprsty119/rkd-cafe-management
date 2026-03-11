<?php

session_start();

/* ==========================
   VALIDASI REQUEST METHOD
========================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   http_response_code(405);
   exit('Invalid request method');
}

/* ==========================
   VALIDASI CSRF TOKEN
========================== */

if (
   !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
   !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
   http_response_code(403);
   exit('CSRF validation failed');
}

/* ==========================
   HAPUS SEMUA SESSION
========================== */

$_SESSION = [];

/* ==========================
   HAPUS COOKIE SESSION
========================== */

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

/* ==========================
   HAPUS COOKIE REMEMBER ME
========================== */

if (isset($_COOKIE['remember_user'])) {

   setcookie(
      "remember_user",
      "",
      [
         'expires' => time() - 3600,
         'path' => '/',
         'secure' => isset($_SERVER['HTTPS']),
         'httponly' => true,
         'samesite' => 'Strict'
      ]
   );
}

/* ==========================
   DESTROY SESSION
========================== */

session_destroy();

/* ==========================
   SESSION BARU UNTUK TOAST
========================== */

session_start();
session_regenerate_id(true);

$_SESSION['toast'] = [
   "type" => "success",
   "message" => "Logout berhasil."
];

/* ==========================
   REDIRECT
========================== */

header("Location: /rkd-cafe/resources/views/auth/login.php");
exit;
