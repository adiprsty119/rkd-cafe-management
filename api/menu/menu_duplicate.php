<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {

    $pdo = getPDO();
    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);

    if (!$id) {
        throw new Exception("ID tidak valid");
    }

    // ambil data lama
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Produk tidak ditemukan");
    }

    // insert copy
    $stmt = $pdo->prepare("
        INSERT INTO products (name, price, category_id, status)
        VALUES (?, ?, ?, 'active')
    ");

    $stmt->execute([
        $item['name'] . ' Copy',
        $item['price'],
        $item['category_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Menu berhasil diduplikasi'
    ]);
} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
