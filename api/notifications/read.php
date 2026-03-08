<?php

session_start();
require '../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(["error" => "Notification ID required"]);
    exit;
}

$id = $data['id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode([
    "success" => true
]);
