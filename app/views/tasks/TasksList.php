<?php

namespace app\views\tasks;


use app\views\layouts\LayoutView;

class TasksList
{
    private $tasks;

    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    public function render()
    {
        ob_start(); // Включаем буферизацию вывода

        // Генерация HTML-кода для списка задач
        ?>
        <h2>Task List</h2>
        <ul>
            <?php foreach ($this->tasks as $task): ?>
                <li><a href='/coding-challenges/some-developer-name/public/index.php/task/view/<?= $task['id'] ?>'><?= $task['title'] ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php

        $layoutView = new LayoutView(ob_get_clean());
        $layoutView->render();
    }

}