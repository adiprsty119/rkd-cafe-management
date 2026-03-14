<?php

session_start();
header('Content-Type: application/json');

require __DIR__ . '/../../config/database.php';

try {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'])) {
        echo json_encode(["error" => "Notification ID required"]);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];
    $id = (int) $data['id'];

    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$id, $userId]);

    echo json_encode([
        "success" => true
    ]);
} catch (Throwable $e) {

    echo json_encode([
        "error" => "Mark notification failed"
    ]);
}
