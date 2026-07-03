<?php

namespace app\core;

class Router
{
    private array $routes = [];
    private string $defaultController = 'HomeController';
    private string $defaultMethod = 'index';

    private function normalizeRoute(string $route): string
    {
        return trim($route, '/');
    }

    private function resolvePath(): string
    {
        if (isset($_GET['url']) && $_GET['url'] !== '') {
            return $this->normalizeRoute((string) $_GET['url']);
        }

        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        if ($scriptDir !== '/' && str_starts_with($requestUri, $scriptDir)) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        }

        return $this->normalizeRoute($requestUri);
    }

    public function get(string $route, string $action): void
    {
        $route = $this->normalizeRoute($route);

        $this->routes[] = [
            'method' => 'get',
            'route' => $route,
            'action' => $action
        ];
    }

    public function post(string $route, string $action): void
    {
        $route = $this->normalizeRoute($route);

        $this->routes[] = [
            'method' => 'post',
            'route' => $route,
            'action' => $action
        ];
    }


    public function run(): void
    {
        $path = $this->resolvePath();
        $method = strtolower($_SERVER['REQUEST_METHOD'] ?? 'get');

        if ($path === '') {
            $this->dispatchTo($this->defaultController, $this->defaultMethod);
            return;
        }

        foreach ($this->routes as $route) {
            if ($route['route'] === $path && $route['method'] === $method) {

                $this->dispatch($route);
                return;
            }
        }

        http_response_code(404);
        exit('Rota não encontrada');
    }

    public function dispatch(array $route): void
    {
        [$controller, $method] = explode('@', $route['action'], 2);
        $this->dispatchTo($controller, $method);
    }

    private function dispatchTo(string $controller, string $method): void
    {
        $controllerClass = "app\\controllers\\$controller";

        if (!class_exists($controllerClass)) {
            print "Controller $controller não encontrado";
            die;
        }

        if (!method_exists($controllerClass, $method)) {
            print "Método $method não encontrado em $controllerClass";
            die;
        }
        
        $controller = new $controllerClass;
        $controller->$method();

    }

    public function getAllRoutes(): array
    {
        return $this->routes;
    }

}