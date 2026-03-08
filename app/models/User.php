<?php

require_once __DIR__ . '/../../config/database.php';

class User
{

    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /* ==========================
       FIND USER BY USERNAME
       (LOGIN MANUAL)
    ========================== */

    public function findByUsername($username)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM users WHERE username = ? LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /* ==========================
       FIND USER BY EMAIL
       (LOGIN GOOGLE)
    ========================== */

    public function findByEmail($email)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /* ==========================
       CREATE USER (MANUAL)
    ========================== */

    public function createUser($name, $username, $email, $password)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, username, email, password, role, login_method)
             VALUES (?, ?, ?, ?, 'admin', 'manual')"
        );

        $stmt->bind_param("ssss", $name, $username, $email, $password);

        return $stmt->execute();
    }

    /* ==========================
       CREATE USER GOOGLE
    ========================== */

    public function createGoogleUser($name, $username, $email, $foto)
    {
        $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, username, email, password, role, login_method, foto)
         VALUES (?, ?, ?, ?, 'owner', 'google', ?)"
        );

        $stmt->bind_param("sssss", $name, $username, $email, $password, $foto);

        return $stmt->execute();
    }
}
