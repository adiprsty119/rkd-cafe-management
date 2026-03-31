<?php

require_once '../services/RegisterService.php';

class RegisterController
{
    public function register()
    {
        header('Content-Type: application/json');

        try {

            /* ========================
               METHOD CHECK
            ======================== */
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Method not allowed'
                ]);
                return;
            }

            // session_start();

            /* ========================
                VALIDASI STRUKTUR DATA
            ======================== */
            if (!isset($_POST['owner'], $_POST['business'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Data tidak lengkap'
                ]);
                return;
            }

            /* ========================
               CSRF CHECK
            ======================== */
            if (
                empty($_POST['csrf_token']) ||
                empty($_SESSION['csrf_token']) ||
                $_POST['csrf_token'] !== $_SESSION['csrf_token']
            ) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'CSRF token tidak valid'
                ]);
                return;
            }

            /* ========================
               HANDLE REGISTER
            ======================== */
            $service = new RegisterService();
            $result = $service->handle($_POST);

            /* ========================
               RESPONSE
            ======================== */
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
        } catch (Throwable $e) {

            error_log("[REGISTER ERROR] " . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server'
            ]);
        }
    }
}
