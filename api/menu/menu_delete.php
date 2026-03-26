<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';
session_start();

header('Content-Type: application/json');

try {

    $pdo = getPDO();

    /* ==========================
       AMBIL INPUT (SAFE)
    ========================== */
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception("ID tidak valid");
    }

    $id = (int)$data['id'];

    // 🔥 FIX BOOLEAN
    $force = filter_var($data['force'] ?? false, FILTER_VALIDATE_BOOLEAN);

    // 🔥 ROLE
    $role = $_SESSION['role'] ?? 'guest';

    /* ==========================
       CEK RELASI (USED)
    ========================== */
    $stmt = $pdo->prepare("
        SELECT EXISTS (
            SELECT 1 FROM order_items WHERE product_id = ?
        )
    ");
    $stmt->execute([$id]);

    $isUsed = (bool) $stmt->fetchColumn();

    /* ==========================
       CEK EXIST DATA
    ========================== */
    $exists = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $exists->execute([$id]);

    if (!$exists->fetch()) {
        throw new Exception("Produk tidak ditemukan");
    }

    /* ==========================
       LOGIC DELETE
    ========================== */

    // ✅ 1. BELUM DIGUNAKAN → SOFT DELETE
    // 🔥 JIKA SUDAH DIGUNAKAN
    if ($isUsed) {

        // ADMIN → NONAKTIFKAN (BUKAN DELETE)
        if ($role === 'admin' && $force) {

            $pdo->prepare("
            UPDATE products 
            SET status = 'inactive' 
            WHERE id = ?
        ")->execute([$id]);

            echo json_encode([
                'success' => true,
                'type' => 'disabled',
                'message' => 'Produk dinonaktifkan (tidak bisa dihapus karena ada transaksi)'
            ]);
            exit;
        }

        echo json_encode([
            'success' => false,
            'requires_confirmation' => true,
            'message' => 'Produk sudah digunakan. Hanya bisa dinonaktifkan'
        ]);
        exit;
    }

    // 🔥 2. SUDAH DIGUNAKAN → ADMIN FORCE
    if ($role === 'admin' && $force) {

        // ⚠️ NOTE: ini tetap bisa gagal jika FK = RESTRICT
        try {

            $pdo->prepare("DELETE FROM products WHERE id = ?")
                ->execute([$id]);

            echo json_encode([
                'success' => true,
                'type' => 'force',
                'message' => 'Produk dihapus permanen (admin override)'
            ]);
            exit;
        } catch (PDOException $e) {

            // 🔥 HANDLE FK ERROR
            echo json_encode([
                'success' => false,
                'message' => 'Gagal force delete: data masih terhubung dengan transaksi'
            ]);
            exit;
        }
    }

    // ⚠️ 3. SUDAH DIGUNAKAN → SARANKAN DISABLE
    echo json_encode([
        'success' => false,
        'requires_confirmation' => true,
        'message' => 'Produk sudah digunakan. Gunakan force delete (admin) atau nonaktifkan produk'
    ]);
} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
