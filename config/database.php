<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "rkd_cafe";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed");
}
