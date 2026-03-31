<?php

namespace App\Repositories;

use PDO;

require_once __DIR__ . '/../../config/database.php';

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* =========================
       CREATE USER
    ========================= */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (
                business_id, name, email, phone,
                username, password, status
            ) VALUES (
                :business_id, :name, :email, :phone,
                :username, :password, 'pending'
            )
        ");

        $stmt->execute([
            ':business_id' => $data['business_id'],
            ':name'        => $data['name'],
            ':email'       => $data['email'],
            ':phone'       => $data['phone'],
            ':username'    => $data['username'],
            ':password'    => $data['password']
        ]);

        return $this->pdo->lastInsertId();
    }

    /* =========================
       FIND BY EMAIL
    ========================= */
    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE email = :email LIMIT 1
        ");

        $stmt->execute([':email' => $email]);

        return $stmt->fetch();
    }

    /* =========================
       FIND BY ID
    ========================= */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE id = :id LIMIT 1
        ");

        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /* =========================
       APPROVE USER
    ========================= */
    public function approve($id, $token, $expiry)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET status='approved',
                activation_token=:token,
                activation_expiry=:expiry,
                approved_at=NOW()
            WHERE id=:id AND status='pending'
        ");

        $stmt->execute([
            ':id'     => $id,
            ':token'  => $token,
            ':expiry' => $expiry
        ]);

        return $stmt->rowCount();
    }

    /* =========================
       ACTIVATE USER
    ========================= */
    public function activateByToken($token)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET status='active',
                activation_token=NULL,
                activation_expiry=NULL,
                activated_at=NOW()
            WHERE activation_token=:token
            AND activation_expiry > NOW()
            AND status='approved'
        ");

        $stmt->execute([':token' => $token]);

        return $stmt->rowCount();
    }

    /* =========================
       FIND BY TOKEN
    ========================= */
    public function findByToken($token)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users
            WHERE activation_token=:token
            AND activation_expiry > NOW()
            LIMIT 1
        ");

        $stmt->execute([':token' => $token]);

        return $stmt->fetch();
    }
}
