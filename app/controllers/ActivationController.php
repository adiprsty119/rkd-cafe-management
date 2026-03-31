<?php

require_once __DIR__ . '/../../config/database.php';

class ActivationController
{
    public function activate()
    {
        try {

            /* ========================
               VALIDASI TOKEN
            ======================== */
            if (empty($_GET['token'])) {
                throw new Exception('Token tidak ditemukan');
            }

            $token = $_GET['token'];

            if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
                throw new Exception('Token tidak valid');
            }

            $pdo = getPDO();

            /* ========================
               CEK TOKEN
            ======================== */
            $stmt = $pdo->prepare("
                SELECT id FROM users
                WHERE activation_token = :token
                AND activation_expiry > NOW()
                AND status = 'approved'
                LIMIT 1
            ");

            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('Token invalid / expired');
            }

            /* ========================
               AKTIVASI
            ======================== */
            $stmt = $pdo->prepare("
                UPDATE users
                SET status='active',
                    activation_token=NULL,
                    activation_expiry=NULL,
                    activated_at=NOW()
                WHERE id=:id
            ");

            $stmt->execute([':id' => $user['id']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Gagal aktivasi akun');
            }

            /* ========================
               REDIRECT SUCCESS
            ======================== */
            header("Location: /login?activated=1");
            exit;
        } catch (Throwable $e) {

            error_log("[ACTIVATION ERROR] " . $e->getMessage());

            header("Location: /login?error=activation_failed");
            exit;
        }
    }
}
