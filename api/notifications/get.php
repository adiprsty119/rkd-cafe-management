<?php

session_start();
header('Content-Type: application/json');

require __DIR__ . '/../../config/database.php';

try {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "notifications" => [],
            "unread" => 0
        ]);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            message,
            DATE_FORMAT(created_at,'%H:%i') as time,
            is_read
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");

    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    $stmt2 = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM notifications
        WHERE user_id = ? AND is_read = 0
    ");

    $stmt2->execute([$userId]);
    $count = $stmt2->fetch();

    echo json_encode([
        "notifications" => $notifications,
        "unread" => (int) $count['total']
    ]);
} catch (Throwable $e) {

    echo json_encode([
        "error" => "Notification fetch failed"
    ]);
}
