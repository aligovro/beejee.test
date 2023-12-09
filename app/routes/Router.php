<?php

namespace app\routes;

// app\routes\Router.php

namespace app\routes;

class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function addRoute($path, $controllerClass, $action)
    {
        $this->routes[$path] = ['controller' => $controllerClass, 'action' => $action];
    }

    public function registerRoutes()
    {
        $this->addRoute('/', 'TaskController', 'index');
        $this->addRoute('/blbl', 'TaskController', 'index');
    }

    public function route()
    {
        $requestPath = $_SERVER['REQUEST_URI'];
        $requestPath = parse_url($requestPath, PHP_URL_PATH);

        $controllerClass = 'DefaultController';
        $action = 'index';

        foreach ($this->routes as $path => $route) {
            if (strpos($requestPath . '/', $path . '/') === 0) {
                $controllerClass = $route['controller'];
                $action = $route['action'];
                break;
            }
        }

        $controllerClass = "\\app\\controllers\\{$controllerClass}";

        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                echo "404 Not Found";
            }
        } else {
            echo "404 Not Found";
        }
    }
}

