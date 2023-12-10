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

}
