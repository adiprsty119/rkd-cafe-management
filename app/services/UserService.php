<?php

namespace App\Services;

use PDO;
use Exception;
use Throwable;

use function getPDO;
use function sendApprovalEmail;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/MailService.php';

class UserService
{
    public function approveRegistration($request_id, $admin_id)
    {
        if (!is_numeric($request_id) || $request_id <= 0) {
            throw new Exception('Request ID tidak valid');
        }

        $pdo = getPDO();

        try {

            $pdo->beginTransaction();

            /* =========================
               1. AMBIL REQUEST
            ========================= */
            $stmt = $pdo->prepare("
                SELECT * FROM registration_requests WHERE id = :id
            ");

            $stmt->execute([':id' => $request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                throw new Exception('Request tidak ditemukan');
            }

            if ($request['status'] !== 'pending') {
                throw new Exception('Request sudah diproses');
            }

            /* =========================
               2. UPDATE REQUEST
            ========================= */
            $stmt = $pdo->prepare("
                UPDATE registration_requests
                SET status = 'approved',
                    approved_by = :admin_id,
                    approved_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ':admin_id' => $admin_id,
                ':id'       => $request_id
            ]);

            /* =========================
               3. AKTIFKAN USER
            ========================= */
            $stmt = $pdo->prepare("
                UPDATE users
                SET status = 'active'
                WHERE id = :user_id
            ");

            $stmt->execute([
                ':user_id' => $request['user_id']
            ]);

            /* =========================
               4. AMBIL EMAIL USER
            ========================= */
            $stmt = $pdo->prepare("
                SELECT email, name FROM users WHERE id = :id
            ");

            $stmt->execute([':id' => $request['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdo->commit();

            /* =========================
               5. EMAIL NOTIFIKASI
            ========================= */
            if ($user && !empty($user['email'])) {
                sendApprovalEmail($user['email'], $user['name']);
            }
        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("[APPROVE REGISTRATION ERROR] " . $e->getMessage());

            throw $e;
        }
    }

    public function getAllUsersWithRequest()
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.status,
                r.id AS request_id,
                r.status AS request_status
            FROM users u
            LEFT JOIN registration_requests r 
                ON r.user_id = u.id
            ORDER BY u.id DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
