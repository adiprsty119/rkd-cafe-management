<?php

require_once __DIR__ . '/../../config/database.php';

class User
{

    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    /* ==========================
       FIND USER BY USERNAME
       (LOGIN MANUAL)
    ========================== */

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE username = :username LIMIT 1"
        );

        $stmt->execute([
            'username' => $username
        ]);

        return $stmt->fetch();
    }

    /* ==========================
       FIND USER BY EMAIL
       (LOGIN GOOGLE)
    ========================== */

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );

        $stmt->execute([
            'email' => $email
        ]);

        return $stmt->fetch();
    }

    /* ==========================
       CREATE USER (MANUAL)
    ========================== */

    public function createUser($name, $username, $email, $password)
    {

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, username, email, password, role, login_method)
             VALUES (:name, :username, :email, :password, 'admin', 'manual')"
        );

        return $stmt->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);
    }

    /* ==========================
       CREATE USER GOOGLE
    ========================== */

    public function createGoogleUser($name, $username, $email, $foto)
    {

        $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, username, email, password, role, login_method, foto)
             VALUES (:name, :username, :email, :password, 'owner', 'google', :foto)"
        );

        return $stmt->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'foto' => $foto
        ]);
    }
}
