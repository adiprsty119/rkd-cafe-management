<?php

session_start();
require '../../config/database.php';

$pdo = getPDO(); // ← TAMBAHKAN BARIS INI

/* ==========================
   AUTH VALIDATION
========================== */

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

/* ==========================
   METHOD VALIDATION
========================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

/* ==========================
   GET JSON INPUT
========================== */

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['collapsed'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$userId = $_SESSION['user_id'];
$collapsed = $data['collapsed'] ? 1 : 0;

/* ==========================
   UPDATE DATABASE
========================== */

$stmt = $pdo->prepare(
    "UPDATE users SET sidebar_collapsed = :collapsed WHERE id = :id"
);

$result = $stmt->execute([
    "collapsed" => $collapsed,
    "id" => $userId
]);

if ($result) {

    echo json_encode([
        "success" => true,
        "collapsed" => $collapsed
    ]);
} else {

    http_response_code(500);

    echo json_encode([
        "error" => "Database error"
    ]);
}
