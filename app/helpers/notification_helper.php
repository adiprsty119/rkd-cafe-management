<?php

function getUnreadNotificationCount($pdo, int $userId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->execute(['user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}
