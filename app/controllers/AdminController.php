<?php

require_once '../services/UserService.php';

use App\Services\UserService;

class AdminController
{
    public function approve()
    {
        header('Content-Type: application/json');

        try {

            session_start();

            if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
                http_response_code(403);
                throw new Exception('Unauthorized');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                throw new Exception('Method not allowed');
            }

            if (empty($_POST['request_id'])) {
                throw new Exception('Request ID tidak valid');
            }

            $request_id = (int) $_POST['request_id'];

            $service = new UserService();
            $service->approveRegistration($request_id, $_SESSION['user']['id']);

            echo json_encode([
                'success' => true,
                'message' => 'User berhasil di-approve'
            ]);
        } catch (Throwable $e) {

            error_log("[ADMIN APPROVE ERROR] " . $e->getMessage());

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
