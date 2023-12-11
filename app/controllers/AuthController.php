<?php

namespace app\controllers;

use app\views\auth\AuthView;
use app\models\User;

class AuthController extends MainController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index()
    {
        if ($this->checkAuthentication()) {
            header("Location: /");
            exit();
        }

        $isAuthUser = false;

        $authView = new AuthView($isAuthUser);
        $authView->render();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(400, ['error' => 'Invalid request']);
        }

        $rawData = file_get_contents('php://input');
        $requestData = json_decode($rawData, true);

        $username = $requestData['username'];
        $password = $requestData['password'];
        if (!$this->validateRequiredText($username) || !$this->validateRequiredText($password)) {
            $this->jsonResponse(400, ['error' => 'Field is required']);
        }
        $userId = $this->userModel->authenticate($username, $password);

        if ($userId) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $username;
            $this->jsonResponse(200, ['success' => true]);
        } else {
            $this->jsonResponse(400, ['error' => 'Incorrect credentials. Try again.']);
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $userId = $this->userModel->createUser($username, $password);
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $username;
                header("Location: /");
                exit();
            } else {
                $this->jsonResponse(400, ['error' => 'Register error. Try again.']);
            }
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: /");
        exit();
    }
}
