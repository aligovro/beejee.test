<?php

namespace app\controllers;

use app\views\tasks\TasksList;

class TaskController
{
    public function __construct () {

    }

    public function index()
    {
        $tasks = [
            ['id' => 1, 'title' => 'Task 1'],
            ['id' => 2, 'title' => 'Task 2'],
        ];

        $indexView = new TasksList($tasks);
        $indexView->render();
    }

    public function view($taskId)
    {

    }

}