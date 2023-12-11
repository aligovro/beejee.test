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
     * Получение задач с возможностью пагинации
     *
     * @param int|null $page Номер страницы (опционально)
     * @param int $perPage Количество задач на странице
     * @return array|false Массив задач или false в случае ошибки
     * @throws Exception
     */
    public function getTasks(?int $page = 1, int $perPage = 3, $orderBy = 'id', $orderByType = 'desc')
    {
        try {
            return $this->db->selectWithPagination('{#}tasks', ['*'], '', [], "{$orderBy} {$orderByType}", $page, $perPage);
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve tasks: " . $e->getMessage());
        }
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
            $this->db->insert('{#}tasks', $data);
            return true;
        } catch (Exception $e) {
            return false;
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
    public function updateTask(int $taskId, array $data)
    {
        try {
            return $this->db->update('{#}tasks', 'id = ?', $data);
        } catch (Exception $e) {
            throw new Exception("Failed to edit task: " . $e->getMessage());
        }
    }

    /**
     * Получение задачи по ID
     *
     * @param int $taskId
     * @return array|false
     * @throws Exception
     */
    public function getTaskById(int $taskId)
    {
        try {
            return $this->db->selectById('{#}tasks', $taskId);
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve task by ID: " . $e->getMessage());
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
