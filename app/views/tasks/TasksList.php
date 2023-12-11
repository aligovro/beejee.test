<?php

namespace app\views\tasks;

use app\traits\StrTrait;
use app\views\layouts\LayoutView;

class TasksList
{
    private $tasks;
    private $totalCount;
    private $currentPage;
    private $totalPages;
    private $isAuthUser;
    private $sortBy;
    private $sortByType;

    use StrTrait;

    public function __construct($tasks, $totalCount, $currentPage, $totalPages, $isAuthUser, $sortBy, $sortByType)
    {
        $this->tasks = $tasks;
        $this->totalCount = $totalCount;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->isAuthUser = $isAuthUser;
        $this->sortBy = $sortBy;
        $this->sortByType = $sortByType;
    }

    public function render()
    {
        ob_start();
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Tasks List</h2>
        </div>


        <div class="tasks-list">
            <div class="d-flex justify-content-between">
                <div class="form-group d-flex">
                    <div>
                        <label for="sortSelect">Sort by:</label>
                        <select class="form-control" id="sortSelect" onchange="sortTasks(this.value)">
                            <option value="id">ID</option>
                            <option value="username">Username</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                    <div>
                        <label for="sortTypeSelect"></label>
                        <select class="form-control" id="sortTypeSelect" onchange="sortSortByType(this.value)">
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <a href="/tasks/add" class="btn btn-primary">Create Task</a>
                </div>
            </div>
            <ul class="list-group">
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
                        <span><?= $task['username'] ? $this->setHtmlspecialchars($task['username']) : '' ?></span>
                        <span><?= $task['email'] ?></span>
                        <span class="text"><?= $task['text'] ? $this->setHtmlspecialchars($task['text']) : '' ?></span>
                        <span><?= $task['status'] ? 'Completed' : 'In progress' ?></span>
                        <?php if ($this->isAuthUser): ?>
                            <a href="/task/<?= $task['id'] ?>" class="edit"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php
                $baseURL = "?";
                $queryParams = [];
                if ($this->sortBy) {
                    $queryParams['sort'] = $this->sortBy;
                }
                if ($this->sortByType) {
                    $queryParams['sortType'] = $this->sortByType;
                }
                $baseURL .= http_build_query($queryParams);
                $baseURL = rtrim($baseURL, '&');
                $previousPageURL = $baseURL . "&page=" . max($this->currentPage - 1, 1);
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $previousPageURL ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $this->totalPages; $i++): ?>
                    <?php $pageURL = $baseURL . "&page={$i}"; ?>
                    <li class="page-item <?= ($i == $this->currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $pageURL ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php
                    $nextPageURL = $baseURL . "&page=" . min($this->currentPage + 1, $this->totalPages);
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $nextPageURL ?>">Next</a>
                </li>
            </ul>
        </nav>

        <?php

        $layoutView = new LayoutView(ob_get_clean());
        $layoutView->render();
    }
}
