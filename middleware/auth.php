<?php

session_start();

/* AUTO LOGIN VIA COOKIE */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {

    $_SESSION['user_id'] = $_COOKIE['remember_user'];
}

/* CEK SESSION LOGIN */
if (!isset($_SESSION['user_id'])) {

    header("Location: auth/login.php");
    exit();
}
