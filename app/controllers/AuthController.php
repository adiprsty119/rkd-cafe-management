<?php

session_start();

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User();

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
        http_response_code(403);
        exit("CSRF token tidak valid");
    }
}

# login()
function login($userModel)
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Method not allowed");
    }

    validateCSRF();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $_SESSION['toast'] = [
            "type" => "success",
            "message" => "Login berhasil!"
        ];

        header("Location: /rkd-cafe/public/index.php");
        exit;
    }

    $_SESSION['error'] = "Username atau password salah.";

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

    if (strlen($password) < 8) {

        $_SESSION['toast'] = [
            "type" => "error",
            "message" => "Password minimal 8 karakter."
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

    $options = [
        "http" => [
            "header" => "Content-type: application/x-www-form-urlencoded",
            "method" => "POST",
            "content" => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($token_url, false, $context);
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

        $username = strtolower(str_replace(' ', '', $name));

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

    header("Location: /rkd-cafe/public/index.php");
    exit;
}
