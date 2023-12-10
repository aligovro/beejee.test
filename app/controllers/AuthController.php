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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $userId = $this->userModel->authenticate($username, $password);
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $username;
                header("Location: /");
                exit();
            } else {
                echo "Неправильные учетные данные. Попробуйте снова.";
            }
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
                echo "Ошибка регистрации. Попробуйте снова.";
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
