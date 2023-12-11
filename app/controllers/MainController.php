<?php

namespace app\controllers;


class MainController
{
    public function checkAuthentication()
    {
        return isset($_SESSION['user_id']);
    }

    public function getAuthUserName()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_name'])
            ? $_SESSION['user_name']
            : false;
    }

    protected function jsonResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateRequiredText($text): bool
    {
        return !empty($text);
    }

}
