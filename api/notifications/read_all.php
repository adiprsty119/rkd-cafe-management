<?php

session_start();
require '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();

echo json_encode([
    "success" => true
]);
