<?php

session_start();
require '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, title, message, TIME_FORMAT(created_at,'%H:%i') as time, is_read FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id=? AND is_read=0");
$stmt2->bind_param("i", $userId);
$stmt2->execute();

$res2 = $stmt2->get_result();
$count = $res2->fetch_assoc();

echo json_encode([
    "notifications" => $notifications,
    "unread" => $count['total']
]);
