<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {

    $pdo = getPDO();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) throw new Exception("Invalid input");

    $name = trim($data['name'] ?? '');
    $rawPrice = $data['price'] ?? 0;
    $clean = str_replace(['.', ','], ['', '.'], $rawPrice);
    $price = (float) $clean;
    $cost = (float)($data['cost'] ?? 0);
    $stock = (int)($data['stock'] ?? 0);
    $category_id = (int)($data['category_id'] ?? 0);
    $status = $data['status'] ?? 'active';

    // VALIDASI
    if (!$name) throw new Exception("Nama wajib diisi");
    if ($price <= 0) throw new Exception("Harga tidak valid");
    if (!$category_id) throw new Exception("Kategori wajib dipilih");

    // CEK CATEGORY VALID
    $check = $pdo->prepare("SELECT id FROM categories WHERE id=? AND status='active'");
    $check->execute([$category_id]);

    if (!$check->fetch()) {
        throw new Exception("Kategori tidak valid");
    }

    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO products 
        (name, price, cost, stock, category_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $name,
        $price,
        $cost,
        $stock,
        $category_id,
        $status
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Menu berhasil ditambahkan'
    ]);
} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
