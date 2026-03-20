<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$pdo = getPDO();

/* ==========================
   TOTAL SALES TODAY
========================== */
$totalSales = $pdo->query("
    SELECT COALESCE(SUM(total),0) 
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
")->fetchColumn();

/* ==========================
   ORDERS TODAY
========================== */
$ordersToday = $pdo->query("
    SELECT COUNT(*) 
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
")->fetchColumn();

/* ==========================
   MENU COUNT
========================== */
$menuCount = $pdo->query("
    SELECT COUNT(*) 
    FROM products 
    WHERE status = 'active'
")->fetchColumn();

/* ==========================
   CUSTOMERS
========================== */
$customers = $pdo->query("
    SELECT COUNT(*) 
    FROM users
")->fetchColumn();

/* ==========================
   RECENT ORDERS
========================== */
$recentOrders = $pdo->query("
    SELECT 
        o.id,
        u.name AS customer_name,
        o.total,
        o.status
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   TOP PRODUCTS
========================== */
$topProducts = $pdo->query("
    SELECT 
        p.name,
        SUM(oi.qty) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'total_sales' => (float)$totalSales,
    'orders_today' => (int)$ordersToday,
    'menu_items' => (int)$menuCount,
    'customers' => (int)$customers,
    'recent_orders' => $recentOrders,
    'top_products' => $topProducts
]);
