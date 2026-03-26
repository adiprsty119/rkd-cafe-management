<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getPDO();

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'])) {
        throw new Exception("ID tidak valid");
    }

    $id = (int)$data['id'];

    $stmt = $pdo->prepare("
        UPDATE products 
        SET status = 'active', is_deleted = 0
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Produk berhasil diaktifkan'
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
