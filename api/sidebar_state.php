<?php

session_start();
require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$userId = $_SESSION['user_id'];
$collapsed = $data['collapsed'] ? 1 : 0;

$stmt = $conn->prepare("UPDATE users SET sidebar_collapsed=? WHERE id=?");
$stmt->bind_param("ii", $collapsed, $userId);
$stmt->execute();
