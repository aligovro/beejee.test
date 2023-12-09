<?php

namespace app\models;

use app\database\Database;
use Exception;

class Task
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Создание новой задачи
     *
     * @param string $username
     * @param string $email
     * @param string $text
     * @param bool $status
     * @return bool|int
     * @throws Exception
     */
    public function createTask(string $username, string $email, string $text, bool $status = false)
    {
        $data = [
            'username' => $username,
            'email' => $email,
            'text' => $text,
            'status' => $status,
        ];

        try {
            return $this->db->insert('{#}tasks', $data);
        } catch (Exception $e) {
            throw new Exception("Failed to create task: " . $e->getMessage());
        }
    }

    /**
     * Редактирование задачи
     *
     * @param int $taskId
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function editTask(int $taskId, array $data)
    {
        try {
            return $this->db->update('{#}tasks', 'id = ?', $data);
        } catch (Exception $e) {
            throw new Exception("Failed to edit task: " . $e->getMessage());
        }
    }

    /**
     * Удаление задачи
     *
     * @param int $taskId
     * @return bool
     * @throws Exception
     */
    public function deleteTask(int $taskId)
    {
        try {
            return $this->db->delete('{#}tasks', 'id = ?', [$taskId]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete task: " . $e->getMessage());
        }
    }
}
