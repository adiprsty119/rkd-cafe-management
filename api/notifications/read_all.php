<?php

session_start();
header('Content-Type: application/json');

require __DIR__ . '/../../config/database.php';

$pdo = getPDO();

try {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
    ");

    $stmt->execute([$userId]);

    echo json_encode([
        "success" => true
    ]);
} catch (Throwable $e) {

    echo json_encode([
        "error" => "Mark all notifications failed"
    ]);
}
