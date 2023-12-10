<?php

namespace app\views\tasks;

use app\views\layouts\LayoutView;

class TasksList
{
    private $tasks;
    private $totalCount;
    private $currentPage;
    private $totalPages;
    private $isAuthUser;

    public function __construct($tasks, $totalCount, $currentPage, $totalPages, $isAuthUser)
    {
        $this->tasks = $tasks;
        $this->totalCount = $totalCount;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->isAuthUser = $isAuthUser;
    }

    public function render()
    {
        ob_start();
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Tasks List</h2>
            <div class="form-group">
                <label for="sortSelect">Sort by:</label>
                <select class="form-control" id="sortSelect" onchange="sortTasks(this.value)">
                    <option value="id">ID</option>
                    <option value="username">Username</option>
                    <option value="email">Email</option>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <a href="/tasks/add" class="btn btn-primary">Create Task</a>
        </div>

        <ul class="list-group tasks-list">
            <li class="list-group-item list-group-item-info">
                <span>username</span>
                <span>email</span>
                <span class="text">text</span>
                <span>status</span>
                <?php if ($this->isAuthUser): ?>
                    <span>edit</span>
                <?php endif; ?>
            </li>
            <?php foreach ($this->tasks as $task): ?>
                <li class="list-group-item list-group-item-light">
                    <span><?= $task['username'] ?></span>
                    <span><?= $task['email'] ?></span>
                    <span class="text"><?= $task['text'] ?></span>
                    <span><?= $task['status'] ? 'Completed' : 'In progress' ?></span>
                    <?php if ($this->isAuthUser): ?>
                        <a href="/task/<?= $task['id'] ?>" class="edit"><i class="fas fa-edit"></i></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item"><a class="page-link" href="?page=<?= max($this->currentPage - 1, 1) ?>&sort=<?= $_GET['sort'] ?? '' ?>">Previous</a></li>
                <?php for ($i = 1; $i <= $this->totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $this->currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&sort=<?= $_GET['sort'] ?? '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item"><a class="page-link" href="?page=<?= min($this->currentPage + 1, $this->totalPages) ?>&sort=<?= $_GET['sort'] ?? '' ?>">Next</a></li>
            </ul>
        </nav>


        <?php

        $layoutView = new LayoutView(ob_get_clean());
        $layoutView->render();
    }
}
