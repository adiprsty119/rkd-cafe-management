<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$pdo = getPDO();

$stmt = $pdo->query("
    SELECT 
    p.id,
    p.name,
    p.price,
    p.status,
    p.image,
    c.name AS category,

    EXISTS (
        SELECT 1 
        FROM order_items oi 
        WHERE oi.product_id = p.id
    ) AS used

    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id

    WHERE p.is_deleted = 0

    ORDER BY p.id DESC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   FORMAT IMAGE (SIMPLE 🔥)
========================== */
$data = array_map(function ($item) {

    // hanya format path jika ada
    if (!empty($item['image'])) {
        $item['image'] = "/rkd-cafe/uploads/" . $item['image'];
    } else {
        $item['image'] = null; // 🔥 penting
    }

    return $item;
}, $data);

echo json_encode($data);
