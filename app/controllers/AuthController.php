<?php

session_start();

/* ==========================
   CSRF VALIDATION
========================== */

if (
    !isset($_POST['csrf'], $_SESSION['csrf']) ||
    !hash_equals($_SESSION['csrf'], $_POST['csrf'])
) {
    http_response_code(403);
    exit("CSRF token tidak valid");
}

require __DIR__ . '/../../config/database.php';

/* ==========================
   AMBIL INPUT
========================== */

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

/* ==========================
   VALIDASI INPUT
========================== */

if ($username === '' || $password === '') {

    $_SESSION['toast'] = [
        "type" => "error",
        "message" => "Username dan password wajib diisi."
    ];

    header("Location: /rkd-cafe/public/login.php");
    exit;
}

/* ==========================
   QUERY DATABASE
========================== */

$stmt = $conn->prepare("
    SELECT id, username, password, role
    FROM users
    WHERE username = ?
    LIMIT 1
");

$stmt->bind_param("s", $username);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

/* ==========================
   VERIFIKASI PASSWORD
========================== */

if ($user && password_verify($password, $user['password'])) {

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Login berhasil, selamat datang!"
    ];

    /* ==========================
       REMEMBER ME
    ========================== */

    if (!empty($_POST['remember'])) {

        setcookie(
            "remember_user",
            hash('sha256', $user['id'] . $_SERVER['HTTP_USER_AGENT']),
            [
                "expires" => time() + (86400 * 30),
                "path" => "/",
                "httponly" => true,
                "secure" => false,
                "samesite" => "Strict"
            ]
        );
    }

    header("Location: /rkd-cafe/public/index.php");
    exit;
}

/* ==========================
   LOGIN FAILED
========================== */

$_SESSION['toast'] = [
    "type" => "error",
    "message" => "Login gagal, pastikan username dan password benar."
];

header("Location: /rkd-cafe/public/login.php");
exit;
