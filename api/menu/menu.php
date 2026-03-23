<?php
require_once __DIR__ . '/../../config/database.php';

$pdo = getPDO();

$stmt = $pdo->query("SELECT * FROM menus ORDER BY id DESC");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
