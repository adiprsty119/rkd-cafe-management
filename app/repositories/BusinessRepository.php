<?php

namespace App\Repositories;

use PDO;

require_once __DIR__ . '/../../config/database.php';

class BusinessRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* =========================
       CREATE BUSINESS
    ========================= */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO businesses (name, phone, category, address)
            VALUES (:name, :phone, :category, :address)
        ");

        $stmt->execute([
            ':name'     => $data['name'],
            ':phone'    => $data['phone'],
            ':category' => $data['category'],
            ':address'  => $data['address']
        ]);

        return $this->pdo->lastInsertId();
    }

    /* =========================
       FIND BY ID
    ========================= */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM businesses WHERE id=:id LIMIT 1
        ");

        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }
}
