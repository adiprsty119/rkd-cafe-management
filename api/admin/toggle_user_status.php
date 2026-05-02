<?php

define('APP_INIT', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/config/database.php';

session_start();

header('Content-Type: application/json');

try {
    /* =========================
       🔐 VALIDATION
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        throw new Exception('CSRF token tidak valid');
    }

    $userId = intval($_POST['user_id'] ?? 0);
    $newStatus = strtolower(trim($_POST['status'] ?? ''));

    if (!$userId || !in_array($newStatus, ['active', 'inactive'])) {
        throw new Exception('Parameter tidak valid');
    }

    $pdo = getPDO();

    /* =========================
       🔒 PROTECTION
    ========================= */

    // ❗ tidak bisa ubah diri sendiri
    if ($userId == $_SESSION['user_id']) {
        throw new Exception('Tidak bisa mengubah akun sendiri');
    }

    // ❗ cek user exist + ambil status lama
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User tidak ditemukan');
    }

    // ❗ optional: block super admin
    if ($userId == 1) {
        throw new Exception('User utama tidak bisa diubah');
    }

    /* =========================
       🔄 UPDATE STATUS
    ========================= */

    $update = $pdo->prepare("
        UPDATE users 
        SET status = :status 
        WHERE id = :id
    ");

    $update->execute([
        ':status' => $newStatus,
        ':id' => $userId
    ]);

    echo json_encode([
        'success' => true,
        'message' => $newStatus === 'active'
            ? 'User berhasil diaktifkan'
            : 'User berhasil dinonaktifkan'
    ]);
} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
