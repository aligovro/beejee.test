<?php

namespace app\views\tasks;

use app\traits\StrTrait;
use app\views\layouts\LayoutView;

class TaskForm
{
    private $isAuthUser;
    private $task;

    use StrTrait;
    public function __construct($task = null, $isAuthUser = false)
    {
        $this->task = $task;
        $this->isAuthUser = $isAuthUser;
    }

    public function render()
    {
        ob_start();
        ?>
        <h2><?= $this->task['text'] ? $this->setHtmlspecialchars($this->task['text']) : 'Creating new task' ?></h2>

        <form id="addTaskForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= $this->task['username'] ? $this->setHtmlspecialchars($this->task['username']) : '' ?>" required>
                <div class="invalid-feedback username">
                    Invalid username
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control <?= isset($this->task['email']) && !filter_var($this->task['email'], FILTER_VALIDATE_EMAIL) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= $this->task['email'] ?? '' ?>" required>
                <div class="invalid-feedback email">
                    Invalid email address
                </div>
            </div>
            <div class="mb-3">
                <label for="text" class="form-label">Text</label>
                <input type="text" class="form-control" id="text" name="text" value="<?= $this->task['text'] ? $this->setHtmlspecialchars($this->task['text']) : '' ?>" required>
                <div class="invalid-feedback text">
                    Invalid text
                </div>
            </div>
            <?php if ($this->isAuthUser): ?>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" <?= isset($this->task['status']) && $this->task['status'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Is completed</label>
                    </div>
                </div>
                <input type="hidden" id="taskId" name="taskId" value="<?= $this->task['id'] ?? '' ?>">
            <?php endif; ?>
            <button type="button" class="btn btn-primary" id="updateTaskBtn"><?= $this->task ? 'Update task' : 'Create task' ?></button>
        </form>
        <?php

        $layoutView = new LayoutView(ob_get_clean());
        $layoutView->render();
    }
}
?>
