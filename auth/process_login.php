<?php

session_start();

/* VALIDASI CSRF TOKEN */
if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    die("CSRF token tidak valid.");
}

require '../config/database.php';

/* AMBIL INPUT */
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

/* PREPARED STATEMENT (ANTI SQL INJECTION) */
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

/* VERIFIKASI PASSWORD */
if ($user && password_verify($password, $user['password'])) {

    /* REGENERATE SESSION (ANTI SESSION FIXATION) */
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    /* TOAST MESSAGE */
    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Login berhasil, selamat datang!"
    ];

    /* REMEMBER ME */
    if (isset($_POST['remember'])) {

        setcookie(
            "remember_user",
            $user['id'],
            time() + (86400 * 30),
            "/"
        );
    }

    header("Location: ../index.php");
    exit();
} else {

    $_SESSION['error'] = "Username atau password salah.";
    $_SESSION['toast'] = [
        "type" => "error",
        "message" => "Login gagal, pastikan username dan password benar."
    ];

    header("Location: login.php");
    exit();
}
