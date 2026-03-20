<?php

define('APP_INIT', true);
require_once __DIR__ . '/../bootstrap.php';

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

$pdo = getPDO();

$userModel = new User($pdo);


/* ==========================
   ROLE REDIRECT
========================== */

function redirectByRole($roleName)
{
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

    case 'findAccount':
        findAccount($userModel);
        break;

    case 'verifyCode':
        verifyCode($userModel);
        break;

    case 'resetPassword':
        resetPassword($userModel);
        break;

    case 'resendOtp':
        resendOtp($userModel);
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

    $stmt = $pdo->prepare("
        SELECT u.*, r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.username = :username
        LIMIT 1
    ");

    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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

    // var_dump($user['role_name']);
    // exit;

    /* ==========================
       SESSION BINDING
    ========================== */

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['fingerprint'] = hash('sha256', $ip . $ua);

    // LOAD PERMISSIONS
    $stmt = $pdo->prepare("SELECT p.name FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id WHERE rp.role_id = ?");
    $stmt->execute([$user['role_id']]);
    $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

    redirectByRole($user['role_name']);
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

    $stmt = $pdo->prepare("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        $username .= bin2hex(random_bytes(3));

        $userModel->createGoogleUser($name, $username, $email, $picture);
    }

    $stmt = $pdo->prepare("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    /* ==========================
        CACHE GOOGLE AVATAR
    ========================== */

    require_once __DIR__ . '/../helpers/avatar_cache.php';

    $avatarPath = cacheGoogleAvatar($picture, $user['id']);

    if ($avatarPath) {

        $stmt = $pdo->prepare("
        UPDATE users
        SET foto = :foto
        WHERE id = :id
    ");

        $stmt->execute([
            'foto' => $avatarPath,
            'id' => $user['id']
        ]);

        $_SESSION['foto'] = $avatarPath;
    }

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
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role'] = $user['role_name'];

    $stmt = $pdo->prepare("SELECT p.name FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id WHERE rp.role_id = ?");
    $stmt->execute([$user['role_id']]);
    $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $_SESSION['foto'] = $user['foto'] ?? $picture;
    $_SESSION['login_method'] = 'google';
    $_SESSION['toast'] = [
        "type" => "success",
        "message" => "Login dengan Google berhasil!"
    ];

    redirectByRole($user['role_name']);
}


/* ==========================
   FIND ACCOUNT
========================== */

function findAccount($userModel)
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Method not allowed");
    }

    validateCSRF();
    unset($_SESSION['csrf']);

    if (!isset($_POST['form_time'])) {
        exit("Invalid request");
    }

    if (time() - (int)$_POST['form_time'] < 1) {
        exit;
    }

    if (!empty($_POST['website'])) {
        exit;
    }

    global $pdo;

    require_once __DIR__ . '/../services/MailService.php';

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $email === '') {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Username dan email wajib diisi."
        ];

        header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Format email tidak valid."
        ];

        header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
        exit;
    }

    $user = $userModel->findByUsername($username);

    if (!$user || strtolower($user['email']) !== strtolower($email)) {

        $_SESSION['toast'] = [
            "type" => "success",
            "message" => "Jika akun ditemukan, kode verifikasi akan dikirim."
        ];

        header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
        exit;
    }

    $code = random_int(100000, 999999);

    $stmt = $pdo->prepare("
        SELECT id 
        FROM password_resets
        WHERE user_id=? 
        AND expires_at > NOW()
        LIMIT 1
    ");

    $stmt->execute([$user['id']]);

    if ($stmt->fetch()) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Kode OTP masih berlaku. Tunggu hingga kadaluarsa."
        ];

        header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM password_resets
        WHERE user_id=? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");

    $stmt->execute([$user['id']]);

    if ($stmt->fetchColumn() >= 5) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Terlalu banyak permintaan reset password."
        ];

        header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
        exit;
    }

    $pdo->prepare("DELETE FROM password_resets WHERE user_id=? AND expires_at < NOW()")->execute([$user['id']]);

    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, otp_code, attempts, expires_at)
        VALUES (?, ?, 0, DATE_ADD(NOW(), INTERVAL 3 MINUTE))
    ");

    $stmt->execute([
        $user['id'],
        password_hash($code, PASSWORD_DEFAULT)
    ]);

    $_SESSION['reset_user_id'] = $user['id'];

    $subject = "Kode Verifikasi Reset Password - RKD Cafe";

    $htmlBody = "
        <h2>RKD Cafe</h2>
        <p>Halo <b>{$user['username']}</b></p>
        <p>Kode OTP Anda:</p>
        <h1>{$code}</h1>
        <p>Berlaku selama 3 menit</p>
    ";

    if (!sendEmailMessage($subject, $user['email'], $htmlBody)) {

        error_log("Failed sending OTP to: " . $user['email']);

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Gagal mengirim email."
        ];

        header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
        exit;
    }

    if (headers_sent($file, $line)) {
        die("Headers already sent in $file on line $line");
    }

    session_write_close();

    header("Location: /rkd-cafe/resources/views/auth/verify_code.php");
    exit;
}


/* ==========================
   VERIFY CODE
========================== */

function verifyCode($userModel)
{

    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    global $pdo;

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["message" => "Method not allowed"]);
        exit;
    }

    $code = trim($_POST['verification_code'] ?? '');

    // var_dump($code);
    // exit;

    if (!$code) {
        echo json_encode(["message" => "Kode wajib diisi"]);
        exit;
    }

    $user_id = $_SESSION['reset_user_id'] ?? null;

    // var_dump($_SESSION);
    // exit;

    if (!$user_id) {
        echo json_encode(["message" => "Session reset tidak ditemukan"]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM password_resets
        WHERE user_id=?
        AND expires_at > NOW()
        ORDER BY created_at DESC
        LIMIT 1
    ");

    $stmt->execute([$user_id]);
    $otp = $stmt->fetch();

    // var_dump($otp);
    // exit;

    if (!$otp) {
        echo json_encode(["message" => "OTP tidak ditemukan"]);
        exit;
    }

    if ($otp['attempts'] >= 5) {
        echo json_encode(["message" => "Terlalu banyak percobaan OTP"]);
        exit;
    }

    if (strtotime($otp['expires_at']) < time()) {
        echo json_encode(["message" => "OTP kadaluarsa"]);
        exit;
    }

    // var_dump($otp['expires_at']);
    // var_dump(time());
    // exit;

    if (!password_verify($code, $otp['otp_code'])) {

        $pdo->prepare("
            UPDATE password_resets
            SET attempts = attempts + 1
            WHERE id=?
        ")->execute([$otp['id']]);

        echo json_encode(["message" => "Kode OTP salah"]);
        exit;
    }

    // var_dump($code);
    // var_dump($otp['otp_code']);
    // var_dump(password_verify($code, $otp['otp_code']));
    // exit;

    $token = bin2hex(random_bytes(32));
    $hashed = hash('sha256', $token);

    $pdo->prepare("
        UPDATE password_resets
        SET reset_token=?
        WHERE id=?
    ")->execute([$hashed, $otp['id']]);

    echo json_encode([
        "redirect_url" => "/rkd-cafe/resources/views/auth/reset_password.php?token=" . $token
    ]);
}


/* ==========================
   RESET PASSWORD
========================== */

function resetPassword($userModel)
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    $token = $data['reset_token'] ?? '';
    $password = $data['password'] ?? '';
    $confirm = $data['confirm_password'] ?? '';

    if (!$token || !$password || !$confirm) {
        echo json_encode(["error" => "Semua data harus diisi"]);
        exit;
    }

    if ($password !== $confirm) {
        echo json_encode(["error" => "Password tidak cocok"]);
        exit;
    }

    $hashedToken = hash('sha256', $token);

    $stmt = $pdo->prepare("
        SELECT *
        FROM password_resets
        WHERE reset_token=?
        AND expires_at > NOW()
        LIMIT 1
    ");

    $stmt->execute([$hashedToken]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(["error" => "Token tidak valid"]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->prepare("
        UPDATE users
        SET password=?
        WHERE id=?
    ")->execute([$hash, $row['user_id']]);

    $pdo->prepare("
        DELETE FROM password_resets
        WHERE id=?
    ")->execute([$row['id']]);

    echo json_encode(["message" => "Password berhasil diubah"]);
}


/* ==========================
   RESEND OTP CODE
========================== */

function resendOtp($userModel)
{
    global $pdo;

    require_once __DIR__ . '/../services/MailService.php';

    $user_id = $_SESSION['reset_user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "Session tidak ditemukan"]);
        exit;
    }

    $user = $userModel->findById($user_id);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "User tidak ditemukan"]);
        exit;
    }

    $code = random_int(100000, 999999);

    $pdo->prepare("
        INSERT INTO password_resets (user_id,otp_code,attempts,expires_at)
        VALUES (?, ?, 0, DATE_ADD(NOW(),INTERVAL 3 MINUTE))
    ")->execute([
        $user_id,
        password_hash($code, PASSWORD_DEFAULT)
    ]);

    $subject = "Kode OTP Reset Password";
    $body = "<h2>$code</h2><p>Berlaku 3 menit</p>";

    sendEmailMessage($subject, $user['email'], $body);

    echo json_encode(["success" => true]);
}
