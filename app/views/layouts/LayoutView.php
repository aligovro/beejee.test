<?php

namespace app\views\layouts;

class LayoutView
{
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function render()
    {
        ob_start();
            ?>
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Task Management App</title>
                    </head>
                    <body>
                    <header>
                        <h1>Task Management App</h1>
                    </header>
                    <main>
                        <?= $this->content ?>
                    </main>
                    <footer>
                        <p>&copy; <?= date('Y') ?> Your Company</p>
                    </footer>
                    </body>
                </html>
            <?php
        echo ob_get_clean();
    }
}
