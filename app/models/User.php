<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /* ==========================
       VALIDATE USERNAME
    ========================== */

    private function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username);
    }

    /* ==========================
       FIND USER BY USERNAME
    ========================== */

    public function findByUsername(string $username): ?array
    {
        if (!$this->isValidUsername($username)) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                username,
                email,
                password,
                role,
                login_method,
                foto,
                status,
                locked_until,
                failed_login_attempts
            FROM users
            WHERE username = :username
            LIMIT 1
        ");

        $stmt->execute([
            'username' => $username
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================
       FIND USER BY EMAIL
    ========================== */

    public function findByEmail(string $email): ?array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                username,
                email,
                password,
                role,
                login_method,
                foto,
                status,
                locked_until,
                failed_login_attempts
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            'email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================
       CREATE USER (MANUAL)
    ========================== */

    public function createUser(
        string $name,
        string $username,
        string $email,
        string $password
    ): bool {

        if (!$this->isValidUsername($username)) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO users 
            (
                name,
                username,
                email,
                password,
                role,
                login_method,
                status,
                created_at
            )
            VALUES
            (
                :name,
                :username,
                :email,
                :password,
                'kasir',
                'manual',
                'active',
                NOW()
            )
        ");

        return $stmt->execute([
            'name' => trim($name),
            'username' => strtolower($username),
            'email' => strtolower($email),
            'password' => $password
        ]);
    }

    /* ==========================
       CREATE USER GOOGLE
    ========================== */

    public function createGoogleUser(
        string $name,
        string $username,
        string $email,
        string $foto
    ): bool {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!$this->isValidUsername($username)) {
            $username = 'user' . rand(1000, 9999);
        }

        $password = password_hash(
            bin2hex(random_bytes(32)),
            PASSWORD_DEFAULT
        );

        $stmt = $this->db->prepare("
            INSERT INTO users 
            (
                name,
                username,
                email,
                password,
                role,
                login_method,
                status,
                foto,
                created_at
            )
            VALUES
            (
                :name,
                :username,
                :email,
                :password,
                'kasir',
                'google',
                'active',
                :foto,
                NOW()
            )
        ");

        return $stmt->execute([
            'name' => trim($name),
            'username' => strtolower($username),
            'email' => strtolower($email),
            'password' => $password,
            'foto' => $foto
        ]);
    }
}
