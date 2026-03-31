<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../repositories/BusinessRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/MailService.php';


use App\Repositories\BusinessRepository;
use App\Repositories\UserRepository;

class RegisterService
{
    public function handle($data)
    {
        $pdo = getPDO();

        $businessRepo = new BusinessRepository($pdo);
        $userRepo = new UserRepository($pdo);

        try {

            $pdo->beginTransaction();

            /* =========================
               VALIDASI
            ========================= */
            $this->validate($data);

            /* =========================
               CEK EMAIL DUPLICATE
            ========================= */
            if ($userRepo->findByEmail($data['owner']['email'])) {
                throw new Exception('Email sudah terdaftar');
            }

            /* =========================
               INSERT BUSINESS
            ========================= */
            $business_id = $businessRepo->create([
                'name'     => $data['business']['name'],
                'phone'    => $data['business']['phone'],
                'category' => $data['business']['category'],
                'address'  => $data['business']['address']
            ]);

            if (!$business_id) {
                throw new Exception('Gagal membuat bisnis');
            }

            /* =========================
               INSERT USER
            ========================= */
            $password = password_hash($data['owner']['password'], PASSWORD_BCRYPT);

            $user_id = $userRepo->create([
                'business_id' => $business_id,
                'role_id'     => 4,
                'status'      => 'inactive',
                'name'        => $data['owner']['name'],
                'email'       => $data['owner']['email'],
                'phone'       => $data['owner']['phone'],
                'username'    => $data['owner']['username'],
                'password'    => $password
            ]);

            if (!$user_id) {
                throw new Exception('Gagal membuat user');
            }

            /* =========================
               INSERT REQUEST
            ========================= */
            $stmt = $pdo->prepare("
                INSERT INTO registration_requests (user_id, business_id)
                VALUES (:user_id, :business_id)
            ");

            $stmt->execute([
                ':user_id'     => $user_id,
                ':business_id' => $business_id
            ]);

            $pdo->commit();

            /* =========================
               EMAIL ADMIN
            ========================= */
            sendAdminNotification([
                'business_name' => $data['business']['name'],
                'owner_name'    => $data['owner']['name'],
                'email'         => $data['owner']['email']
            ]);

            return [
                'success' => true,
                'message' => 'Registrasi berhasil, menunggu approval admin'
            ];
        } catch (Throwable $e) {

            $pdo->rollBack();

            error_log("[REGISTER ERROR] " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /* =========================
        VALIDATION
    ========================= */
    private function validate($data)
    {
        if (empty($data['owner']['email'])) {
            throw new Exception('Email wajib diisi');
        }

        if (!filter_var($data['owner']['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }

        if (strlen($data['owner']['password']) < 8) {
            throw new Exception('Password minimal 8 karakter');
        }

        if (empty($data['business']['name'])) {
            throw new Exception('Nama usaha wajib diisi');
        }
    }
}
