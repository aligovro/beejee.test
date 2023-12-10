<?php

namespace app\views\layouts;

use app\controllers\MainController;

class LayoutView extends MainController
{
    private $content;
    private $title;
    private $authUserName;


    public function __construct($content = '', $title = 'Task Management App')
    {
        $this->content = $content;
        $this->title = $title;
        $this->authUserName = $this->getAuthUserName();
    }

    public function render()
    {
        ob_start();
            ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <title><?= $this->title ?? 'Task Management App' ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
                    <link rel="stylesheet" href="/public/css/styles.css">
                    <script src="/public/js/script.js"></script>
                </head>
                <body class="d-flex flex-column h-100">
                <main class="container-xl d-flex">
                    <header class="bg-dark text-white d-flex">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <h1><a href="/"><?= $this->title ?></a></h1>
                                </div>
                                <?php if ($this->authUserName): ?>
                                    <div class="col-md-6 text-right d-flex">
                                        <span>Welcome <?= $this->authUserName; ?>&nbsp;</span>
                                        <a href="/auth/logout" class="text-white">Logout <i class="fas fa-sign-out-alt"></i></a>
                                    </div>
                                <?php else: ?>
                                    <div class="col-md-6 text-right d-flex">
                                        <a href="/auth" class="text-white">Login <i class="fas fa-sign-in-alt"></i></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </header>
                    <div class="container m-40">
                        <?= $this->content ?>
                    </div>
                    <footer class="bg-dark text-white mt-auto d-flex">
                        <span>&copy; <?= date('Y') ?> Task Management App. All rights reserved.</span>
                    </footer>
                </main>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
                </body>
                </html>
            <?php
        echo ob_get_clean();
    }
}
