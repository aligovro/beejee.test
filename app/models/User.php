<?php

namespace app\models;

use app\database\Database;
use Exception;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool|int
     * @throws Exception
     */
    public function createUser(string $username, string $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $data = [
            'username' => $username,
            'password_hash' => $hashedPassword,
        ];
        try {
            return $this->db->insert('{#}users', $data);
        } catch (Exception $e) {
            throw new Exception("Failed to create user: " . $e->getMessage());
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @return bool|int
     * @throws Exception
     */
    public function authenticate(string $username, string $password): ?int
    {
        $user = $this->getUserByUsername($username);

        if ($user !== null && $this->verifyPassword($password, $user['password_hash'])) {
            return $user['id'];
        }
        return null;
    }

    private function getUserByUsername(string $username): ?array
    {
        return $this->db->select('{#}users', ['id', 'password_hash'], 'username = ?', [$username])[0] ?? null;
    }

    private function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

}
