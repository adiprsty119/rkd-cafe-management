<?php

require_once __DIR__ . '/../services/UserService.php';

use App\Services\UserService;

class UserManagementController
{
    public function getUsers()
    {
        header('Content-Type: application/json');

        try {

            session_start();

            if ($_SESSION['role'] !== 'admin') {
                http_response_code(403);
                throw new Exception("Unauthorized");
            }

            $service = new UserService();
            $users = $service->getAllUsersWithRequest();

            echo json_encode($users);
        } catch (Throwable $e) {

            http_response_code(400);

            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }
}
