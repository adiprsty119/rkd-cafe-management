<?php

define('APP_INIT', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/config/database.php';

session_start();
header('Content-Type: application/json');

try {

    /* =========================
       VALIDASI DASAR
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')
    ) {
        throw new Exception('CSRF token tidak valid');
    }

    $requestId = intval($_POST['request_id'] ?? 0);

    if (!$requestId) {
        throw new Exception('Request ID tidak valid');
    }

    $pdo = getPDO();

    /* =========================
       🔍 AMBIL REQUEST
    ========================= */
    $stmt = $pdo->prepare("
        SELECT user_id, business_id
        FROM registration_requests
        WHERE id = ? AND status = 'pending'
    ");
    $stmt->execute([$requestId]);

    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request tidak ditemukan');
    }

    $userId = $request['user_id'];
    $businessId = $request['business_id'];

    /* =========================
       🔥 UPDATE USER
    ========================= */
    $stmt = $pdo->prepare("
        UPDATE users 
        SET 
            status = 'active',
            business_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$businessId, $userId]);

    /* =========================
       🔥 UPDATE REQUEST
    ========================= */
    $stmt = $pdo->prepare("
        UPDATE registration_requests
        SET 
            status = 'approved',
            approved_at = NOW(),
            approved_by = ?
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $requestId]);

    echo json_encode([
        'success' => true,
        'message' => 'User berhasil di-approve'
    ]);
} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
