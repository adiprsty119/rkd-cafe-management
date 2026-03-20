<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function getUnreadNotificationCount($pdo, int $userId): int
{
    // 🔥 cache 10 detik
    if (isset($_SESSION['notif_count'], $_SESSION['notif_time'])) {
        if (time() - $_SESSION['notif_time'] < 10) {
            return $_SESSION['notif_count'];
        }
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE user_id = :user_id 
        AND is_read = 0
    ");

    $stmt->execute(['user_id' => $userId]);

    $count = (int) $stmt->fetchColumn();

    $_SESSION['notif_count'] = $count;
    $_SESSION['notif_time'] = time();

    return $count;
}
