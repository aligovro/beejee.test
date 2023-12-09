<?php

use app\database\Database;
use app\routes\Router;

define('APP_ROOT', dirname(__DIR__));

define('SESSION_START', true);

header('Content-type:text/html; charset=utf-8');
header('X-Powered-By: Ali');

error_reporting(-1);

// Автозагрузка классов
function autoloader($class)
{
    $class = str_replace("\\", "/", $class);
    $file = APP_ROOT . "/{$class}.php";

    if (file_exists($file)) {
        require_once($file);
    }
}

spl_autoload_register('autoloader');

$db = Database::getInstance();
$router = new Router();
$router->registerRoutes();
$router->route();