<?php

namespace app\views\auth;

use app\views\layouts\LayoutView;

class AuthView
{
    private $isAuthUser;

    public function __construct($isAuthUser)
    {
        $this->isAuthUser = $isAuthUser;
    }

    public function render()
    {
        ob_start();
        ?>
        <h2>Welcome to site</h2>
        <?php if (!$this->isAuthUser): ?>
            <form action="/auth/login" method="post" id="loginForm">
                <div class="mb-3">
                    <label for="loginUsername" class="form-label">username:</label>
                    <input type="text" class="form-control" id="loginUsername" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">password:</label>
                    <input type="password" class="form-control" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        <?php endif; ?>

        <?php

        $layoutView = new LayoutView(ob_get_clean());
        $layoutView->render();
    }
}
