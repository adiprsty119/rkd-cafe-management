<?php

/* ==========================
   SESSION SECURITY
========================== */

ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
ini_set('session.cookie_samesite', 'Lax');

session_start();

/* ==========================
   REQUIRE FILES
========================== */

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($pdo);


/* ==========================
   ROLE REDIRECT
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
            session_destroy();
            header("Location: /rkd-cafe/resources/views/auth/login.php");
            break;
    }

    exit;
}


/* ==========================
   ROUTER
========================== */

$action = $_GET['action'] ?? '';
$action = preg_replace('/[^a-zA-Z0-9]/', '', $action);

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

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM login_attempts
        WHERE (username = ? OR ip_address = ?)
        AND attempt_time > (NOW() - INTERVAL 5 MINUTE)
    ");

    $stmt->execute([$username, $ip]);

    return $stmt->fetchColumn() >= 5;
}

function recordLoginAttempt($username, $ip, $user_id = null)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (username, ip_address, user_id, attempt_time)
        VALUES (?, ?, ?, NOW())
    ");

    $stmt->execute([$username, $ip, $user_id]);
}

function clearLoginAttempts($username)
{
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE username=?");
    $stmt->execute([$username]);
}


/* ==========================
   Login Function
========================== */
function login($userModel)
{
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Method not allowed");
    }

    validateCSRF();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    /* ==========================
       SAFE IP DETECTION
    ========================== */

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $ip = explode(',', $ip)[0];

    /* ==========================
       SANITIZE USER AGENT
    ========================== */

    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);

    if ($username === '' || $password === '') {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Username dan password wajib diisi."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    /* ==========================
       BRUTE FORCE CHECK
    ========================== */

    if (isBruteForce($username, $ip)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Terlalu banyak percobaan login. Coba lagi dalam 5 menit."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    $user = $userModel->findByUsername($username);

    /* ==========================
       TIMING ATTACK PROTECTION
    ========================== */

    $dummyHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
    $hash = $user['password'] ?? $dummyHash;

    if (!password_verify($password, $hash)) {

        if ($user) {

            $stmt = $pdo->prepare("
                UPDATE users
                SET failed_login_attempts = failed_login_attempts + 1
                WHERE id = :id
            ");

            $stmt->execute(['id' => $user['id']]);

            $stmt = $pdo->prepare("
                UPDATE users
                SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                WHERE id = :id AND failed_login_attempts >= 5
            ");

            $stmt->execute(['id' => $user['id']]);

            recordLoginAttempt($username, $ip, $user['id']);
        } else {

            recordLoginAttempt($username, $ip);
        }

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Username atau password salah."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    /* ==========================
       ACCOUNT VALIDATION
    ========================== */

    if ($user['status'] !== 'active') {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Akun tidak aktif."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Akun terkunci sementara."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    if ($user['login_method'] !== 'manual') {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Silakan login menggunakan Google."
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    /* ==========================
       PASSWORD AUTO REHASH
    ========================== */

    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {

        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");

        $stmt->execute([
            'p' => $newHash,
            'id' => $user['id']
        ]);
    }

    /* ==========================
       LOGIN SUCCESS
    ========================== */

    $stmt = $pdo->prepare("
        UPDATE users
        SET 
            last_login = NOW(),
            last_ip = :ip,
            user_agent = :ua,
            failed_login_attempts = 0,
            locked_until = NULL
        WHERE id = :id
    ");

    $stmt->execute([
        'ip' => $ip,
        'ua' => $ua,
        'id' => $user['id']
    ]);

    session_regenerate_id(true);

    clearLoginAttempts($username);

    /* ==========================
       SESSION BINDING
    ========================== */

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['fingerprint'] = hash('sha256', $ip . $ua);

    /* ==========================
       REMEMBER ME (DUAL TOKEN)
    ========================== */

    if (!empty($_POST['remember'])) {

        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(33));

        $stmt = $pdo->prepare("
            UPDATE users
            SET remember_selector = :selector,
                remember_token = :token
            WHERE id = :id
        ");

        $stmt->execute([
            'selector' => $selector,
            'token' => password_hash($validator, PASSWORD_DEFAULT),
            'id' => $user['id']
        ]);

        setcookie(
            "remember",
            $selector . ':' . $validator,
            [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Login berhasil!"
    ];

    redirectByRole($user['role']);
}

/* ==========================
   REGISTER
========================== */

function register($userModel)
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Method not allowed");
    }

    validateCSRF();

    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
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

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Format email tidak valid"
        ];

        header("Location: /rkd-cafe/resources/views/auth/login.php");
        exit;
    }

    if ($userModel->findByUsername($username) || $userModel->findByEmail($email)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Username atau email sudah digunakan."
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

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $userModel->createUser($name, $username, $email, $hashedPassword);

    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Registrasi berhasil! Silakan login."
    ];

    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit;
}


/* ==========================
   GOOGLE LOGIN
========================== */

function loginGoogle()
{

    $_SESSION['oauth_state'] = bin2hex(random_bytes(32));

    $client_id = $_ENV['GOOGLE_CLIENT_ID_WEB'];
    $redirect_uri = $_ENV['GOOGLE_REDIRECT_URI'];

    $params = [
        "client_id" => $client_id,
        "redirect_uri" => $redirect_uri,
        "response_type" => "code",
        "scope" => "openid email profile",
        "prompt" => "select_account",
        "state" => $_SESSION['oauth_state']
    ];

    $url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

    header("Location: $url");
    exit;
}


/* ==========================
   GOOGLE REGISTER
========================== */
function registerGoogle()
{
    loginGoogle();
}


/* ==========================
   GOOGLE CALLBACK
========================== */

function callbackGoogle($userModel)
{
    global $pdo;

    /* ==========================
       VALIDATE OAUTH STATE
    ========================== */

    if (
        !isset($_GET['state'], $_SESSION['oauth_state']) ||
        !hash_equals($_SESSION['oauth_state'], $_GET['state'])
    ) {
        http_response_code(403);
        exit("Invalid OAuth state");
    }

    unset($_SESSION['oauth_state']);

    if (!isset($_GET['code'])) {
        exit("Google authentication failed");
    }

    $code = $_GET['code'];

    $token_url = "https://oauth2.googleapis.com/token";

    $redirect_uri = $_ENV['GOOGLE_REDIRECT_URI'];

    $data = [
        "code" => $code,
        "client_id" => $_ENV['GOOGLE_CLIENT_ID_WEB'],
        "client_secret" => $_ENV['GOOGLE_CLIENT_SECRET_WEB'],
        "redirect_uri" => $redirect_uri,
        "grant_type" => "authorization_code"
    ];

    /* ==========================
       REQUEST ACCESS TOKEN (cURL)
    ========================== */

    $ch = curl_init($token_url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        exit("Google token request error");
    }

    curl_close($ch);

    $token = json_decode($response, true);

    $access_token = $token['access_token'] ?? null;

    if (!$access_token) {
        exit("OAuth token invalid");
    }

    /* ==========================
       GET GOOGLE USER INFO
    ========================== */

    $userinfo_url = "https://www.googleapis.com/oauth2/v2/userinfo";

    $ch = curl_init($userinfo_url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $access_token
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $userinfo_response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        exit("Google userinfo request error");
    }

    curl_close($ch);

    $userinfo = json_decode($userinfo_response, true);

    $email = $userinfo['email'] ?? null;
    $name = $userinfo['name'] ?? 'User';
    $picture = $userinfo['picture'] ?? null;

    if ($picture) {
        $picture = preg_replace('/=s\d+-c$/', '=s256-c', $picture);
    }

    if (!$email) {
        exit("Google account email not available");
    }

    /* ==========================
       FIND USER
    ========================== */

    $user = $userModel->findByEmail($email);

    if (!$user) {

        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        $username .= bin2hex(random_bytes(3));

        $userModel->createGoogleUser($name, $username, $email, $picture);
    }

    $user = $userModel->findByEmail($email);

    /* ==========================
       SAFE IP & USER AGENT
    ========================== */

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $ip = explode(',', $ip)[0];
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);

    /* ==========================
       UPDATE LOGIN METADATA
    ========================== */

    $stmt = $pdo->prepare("
        UPDATE users
        SET last_login = NOW(),
            last_ip = :ip,
            user_agent = :ua
        WHERE id = :id
    ");

    $stmt->execute([
        'ip' => $ip,
        'ua' => $ua,
        'id' => $user['id']
    ]);

    /* ==========================
       CREATE SESSION
    ========================== */

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['foto'] = $user['foto'] ?? $picture;
    $_SESSION['login_method'] = 'google';
    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Login dengan Google berhasil!"
    ];

    redirectByRole($user['role']);
}
