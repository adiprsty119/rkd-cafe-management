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
                TRIM(u.name) AS name,
                TRIM(u.email) AS email,

                CASE 
                    WHEN u.status IS NULL OR u.status = '' THEN 'inactive'
                    ELSE LOWER(TRIM(u.status))
                END AS status,

                u.login_method,
                u.foto,
                u.created_at,

                r.id AS request_id,

                CASE 
                    WHEN r.status IS NULL OR r.status = '' THEN NULL
                    ELSE LOWER(TRIM(r.status))
                END AS request_status

            FROM users u

            LEFT JOIN (
                SELECT rr1.*
                FROM registration_requests rr1
                INNER JOIN (
                    SELECT user_id, MAX(id) as max_id
                    FROM registration_requests
                    GROUP BY user_id
                ) rr2 
                ON rr1.user_id = rr2.user_id 
                AND rr1.id = rr2.max_id
            ) r ON r.user_id = u.id

            ORDER BY u.id DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingRequests()
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            r.id AS request_id,
            r.status AS request_status,
            r.created_at
        FROM registration_requests r
        JOIN users u ON u.id = r.user_id
        WHERE r.status = 'pending'
        ORDER BY r.created_at DESC
    ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
