<?php

namespace app\controllers;

use app\models\Task;
use app\views\tasks\TaskForm;
use app\views\tasks\TasksList;
use app\database\Database;

class TaskController extends MainController
{
    public function __construct()
    {
    }

    public function index()
    {
        $sort = isset($_GET['sort']) && trim($_GET['sort']) !== '' ? $_GET['sort'] : 'id';
        $currentPage = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 3;
        $taskModel = new Task();
        $paginationData = $taskModel->getTasks($currentPage, $perPage, $sort);
        $tasks = $paginationData['data'];
        $totalCount = $paginationData['totalCount'];
        $totalPages = floor($totalCount / $perPage);
        $isAuthUser = $this->checkAuthentication();

        if ($tasks !== false) {
            $indexView = new TasksList($tasks, $totalCount, $currentPage, $totalPages, $isAuthUser);
            $indexView->render();
        } else {
            echo "Error retrieving tasks from the database";
        }
    }

    public function view($taskId)
    {
        $this->checkAuthAndRedirect();
        $taskModel = new Task();
        $task = $taskModel->getTaskById($taskId);

        if ($task !== false) {
            $isAuthUser = $this->checkAuthentication();
            $view = new TaskForm($task, $isAuthUser);
            $view->render();
        } else {
            echo "Error retrieving task from the database";
        }
    }

    public function add()
    {
        $isAuthUser = $this->checkAuthentication();
        $view = new TaskForm(null, $isAuthUser);
        $view->render();
    }

    public function updateOrCreateTask()
    {
        $rawData = file_get_contents('php://input');
        $requestData = json_decode($rawData, true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskModel = new Task();
            $taskId = $requestData['taskId'] ?? null;
            $username = $requestData['taskUsername'];
            $email = $requestData['taskEmail'];
            $text = $requestData['taskText'];
            $status = $requestData['taskStatus'] ?? 0;
            $existingTask = $taskId ? $taskModel->getTaskById($taskId) : null;

            if ($existingTask) {
                $updateResult = $taskModel->updateTask($taskId,
                    [
                        'username' => $username,
                        'email' => $email,
                        'text' => $text,
                        'status' => $status,
                        'id' => $taskId
                    ]
                );
                if ($updateResult !== false) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error updating task']);
                }
            } else {
                $insertResult = $taskModel->createTask($username, $email, $text,$status);
                if ($insertResult !== false) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error creating task', 'details' => $taskModel->lastError()]);
                }
            }
        } else {
            echo json_encode(['error' => 'Invalid request']);
        }
    }



    public function checkAuthAndRedirect()
    {
        if (!$this->checkAuthentication()) {
            header("Location: /auth");
            exit();
        }
    }
}
