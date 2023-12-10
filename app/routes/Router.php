<?php

namespace app\routes;

class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function addRoute($path, $controllerClass, $action, $methods = ['GET'])
    {
        $this->routes[$path] = ['controller' => $controllerClass, 'action' => $action, 'methods' => $methods];
    }

    public function registerRoutes()
    {
        $this->addRoute('/', 'TaskController', 'index');
        $this->addRoute('/auth', 'AuthController', 'index');
        $this->addRoute('/auth/login', 'AuthController', 'login', ['POST']);
        $this->addRoute('/auth/register', 'AuthController', 'register', ['POST']);
        $this->addRoute('/auth/logout', 'AuthController', 'logout');
        $this->addRoute('/tasks/update', 'TaskController', 'updateOrCreateTask', ['POST']);
        $this->addRoute('/tasks/add', 'TaskController', 'add');
        $this->addRoute('/task/(\d+)', 'TaskController', 'view');
    }

    public function route()
    {
        $requestPath = $_SERVER['REQUEST_URI'];
        $requestPath = parse_url($requestPath, PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $controllerClass = 'DefaultController';
        $action = 'index';
        foreach ($this->routes as $path => $route) {
            if (preg_match("#^$path$#", $requestPath, $matches) && in_array($requestMethod, $route['methods'])) {
                $controllerClass = $route['controller'];
                $action = $route['action'];
                array_shift($matches);
                break;
            }
        }
        $controllerClass = "\\app\\controllers\\{$controllerClass}";
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action(...$matches);
            } else {
                echo "404 Not Found";
            }
        } else {
            echo "404 Not Found";
        }
    }
}


