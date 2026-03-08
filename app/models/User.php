<?php

require_once __DIR__ . '/../../config/database.php';

class User
{

    public function findByUsername($username)
    {

        global $conn;

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");

        $stmt->bind_param("s", $username);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
