<?php

define('APP_INIT', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/config/database.php';

session_start();

header('Content-Type: application/json');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        throw new Exception('CSRF token tidak valid');
    }

    $userIds = json_decode($_POST['user_ids'] ?? '[]', true);
    $status = strtolower(trim($_POST['status'] ?? ''));

    if (!is_array($userIds) || empty($userIds)) {
        throw new Exception('Tidak ada user dipilih');
    }

    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Status tidak valid');
    }

    $pdo = getPDO();

    // ❗ filter: tidak boleh ubah diri sendiri
    $userIds = array_filter($userIds, function ($id) {
        return $id != $_SESSION['user_id'];
    });

    if (empty($userIds)) {
        throw new Exception('Tidak ada user valid');
    }

    // ❗ limit safety (optional)
    if (count($userIds) > 100) {
        throw new Exception('Maksimal 100 user sekali aksi');
    }

    // 🔥 build query IN (...)
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));

    $sql = "UPDATE users SET status = ? WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);

    $params = array_merge([$status], $userIds);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => count($userIds) . ' user berhasil diperbarui'
    ]);
} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
