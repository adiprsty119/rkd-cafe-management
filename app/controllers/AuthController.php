<?php
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

session_start();

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User();

/* ==========================
   ROLE ACTION
========================== */

function redirectByRole($role)
{

    switch ($role) {

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
            header("Location: /rkd-cafe/resources/views/auth/login.php");
            break;
    }

    exit;
}

/* ==========================
   ROUTER ACTION
========================== */

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'login':
        login($userModel);
        break;

    case 'register':
        register($userModel);
        break;

    case 'loginGoogle':
        loginGoogle();
        break;

    case 'registerGoogle':
        registerGoogle();
        break;

    case 'callbackGoogle':
        callbackGoogle($userModel);
        break;

    default:
        http_response_code(404);
        exit("Action not found");
}


/* ==========================
   CSRF VALIDATION
========================== */

function validateCSRF()
{
    if (
        !isset($_POST['csrf'], $_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        unset($_SESSION['csrf']);
        http_response_code(403);
        exit("CSRF token tidak valid");
    }
}


/* ==========================
   BRUTE FORCE PROTECTION
========================== */
function isBruteForce($username, $ip)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempt_time > (NOW() - INTERVAL 5 MINUTE)");

    $stmt->execute([$username, $ip]);

    return $stmt->fetchColumn() >= 5;
}

function recordLoginAttempt($username, $ip)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (username, ip_address, attempt_time)
        VALUES (?, ?, NOW())
    ");

    $stmt->execute([$username, $ip]);
}

function clearLoginAttempts($username)
{
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE username=?");
    $stmt->execute([$username]);
}

# login()
function login($userModel)
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Method not allowed");
    }

    validateCSRF();

    $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES);
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];

    if (isBruteForce($username, $ip)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Terlalu banyak percobaan login. Coba lagi dalam 5 menit."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);
        clearLoginAttempts($username);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $_SESSION['toast'] = [
            "type" => "success",
            "message" => "Login berhasil!"
        ];

        redirectByRole($user['role']);
    }

    recordLoginAttempt($username, $ip);

    $_SESSION['toast'] = [
        "type" => "error",
        "message" => "Username atau password salah."
    ];

    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit;
}

# register()
function register($userModel)
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Method not allowed");
    }

    validateCSRF();

    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES);
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || $username === '' || $password === '') {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Semua field wajib diisi."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    /* VALIDASI EMAIL */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Format email tidak valid"
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    /* VALIDASI PASSWORD */
    if (strlen($password) < 8) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Password minimal 8 karakter."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    /* VALIDASI KOMPLEKSITAS PASSWORD */
    if (
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Password harus mengandung huruf besar, kecil dan angka"
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    if ($password !== $confirm) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Konfirmasi password tidak cocok."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    if ($userModel->findByUsername($username)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Username sudah digunakan."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $userModel->createUser($name, $username, $email, $hashedPassword);
    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Registrasi berhasil! Silakan login."
    ];

    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit;
}

# loginGoogle()
function loginGoogle()
{

    $client_id = $_ENV['GOOGLE_CLIENT_ID_WEB'];

    $redirect_uri =
        "http://localhost:8081/rkd-cafe/app/controllers/AuthController.php?action=callbackGoogle";

    $url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        "client_id" => $client_id,
        "redirect_uri" => $redirect_uri,
        "response_type" => "code",
        "scope" => "openid email profile",
        "prompt" => "select_account"
    ]);

    header("Location: $url");
    exit;
}

# registerGoogle()
function registerGoogle()
{
    loginGoogle();
}

# callbackGoogle()
function callbackGoogle($userModel)
{

    if (!isset($_GET['code'])) {
        exit("Google authentication failed");
    }

    $code = $_GET['code'];

    /* EXCHANGE TOKEN */
    $token_url = "https://oauth2.googleapis.com/token";

    $data = [
        "code" => $code,
        "client_id" => $_ENV['GOOGLE_CLIENT_ID_WEB'],
        "client_secret" => $_ENV['GOOGLE_CLIENT_SECRET_WEB'],
        "redirect_uri" => "http://localhost:8081/rkd-cafe/app/controllers/AuthController.php?action=callbackGoogle",
        "grant_type" => "authorization_code"
    ];

    /* REQUEST OPTIONS */
    $options = [
        "http" => [
            "header"  => "Content-type: application/x-www-form-urlencoded",
            "method"  => "POST",
            "content" => http_build_query($data),
            "timeout" => 10
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($token_url, false, $context);

    /* ERROR HANDLING */
    if (!$response) {
        exit("Google token request gagal");
    }

    $token = json_decode($response, true);
    $access_token = $token['access_token'] ?? null;

    if (!$access_token) {
        exit("Token error");
    }

    /* GET USER INFO */
    $userinfo = json_decode(
        file_get_contents(
            "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $access_token
        ),
        true
    );

    $email = $userinfo['email'];
    $name  = $userinfo['name'];
    $picture = $userinfo['picture'] ?? null;
    $user = $userModel->findByEmail($email);

    if (!$user) {
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        $username .= rand(100, 999);
        $userModel->createGoogleUser($name, $username, $email, $picture);
    }

    $user = $userModel->findByEmail($email);
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Login dengan Google berhasil!"
    ];

    redirectByRole($user['role']);
    exit;
}
