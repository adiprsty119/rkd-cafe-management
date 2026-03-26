<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$pdo = getPDO();
$data = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
