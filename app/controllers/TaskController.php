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
        $sortBy = isset($_GET['sort']) && trim($_GET['sort']) !== '' ? $_GET['sort'] : 'id';
        $sortByType = isset($_GET['sortType']) && trim($_GET['sortType']) !== '' ? $_GET['sortType'] : 'desc';
        $currentPage = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 3;
        $taskModel = new Task();
        $paginationData = $taskModel->getTasks($currentPage, $perPage, $sortBy, $sortByType);
        $tasks = $paginationData['data'];
        $totalCount = $paginationData['totalCount'];
        $totalPages = floor($totalCount / $perPage);
        $isAuthUser = $this->checkAuthentication();

        if ($tasks !== false) {
            $indexView = new TasksList($tasks, $totalCount, $currentPage, $totalPages, $isAuthUser, $sortBy, $sortByType);
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(400, ['error' => 'Invalid request']);
        }

        $rawData = file_get_contents('php://input');
        $requestData = json_decode($rawData, true);

        $taskModel = new Task();
        $taskId = $requestData['taskId'] ?? null;
        $isAuthUser = $this->checkAuthentication();

        if ($taskId && !$isAuthUser) {
            $this->jsonResponse(401, ['error' => 'Unauthorized action']);
        }

        $username = $requestData['taskUsername'];
        $email = $requestData['taskEmail'];
        $text = $requestData['taskText'];
        $status = $requestData['taskStatus'] ?? 0;

        $existingTask = $taskId ? $taskModel->getTaskById($taskId) : null;

        if ($existingTask) {
            $updateData = compact('username', 'email', 'text', 'status');
            $updateData['id'] = $taskId;

            $result = $taskModel->updateTask($taskId, $updateData);
        } else {
            $result = $taskModel->createTask($username, $email, $text, $status);
        }

        if ($result !== false) {
            $this->jsonResponse(200, ['success' => true]);
        } else {
            $errorMessage = $existingTask ? 'Error updating task' : 'Error creating task';
            $this->jsonResponse(500, ['error' => $errorMessage, 'details' => $taskModel->lastError()]);
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
