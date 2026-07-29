<?php

namespace app\core;

use RuntimeException;

class Router
{
    private array $routes = [];

    public function get(string $route, string $action): void
    {
        $this->addRoute('get', $route, $action);
    }

    public function post(string $route, string $action): void
    {
        $this->addRoute('post', $route, $action);
    }

    private function addRoute(string $method, string $route, string $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'route'  => $route,
            'action' => $action
        ];
    }

    public function run(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('\\', '/', $basePath);

        if ($basePath === '/') {
            $basePath = '';
        }

        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = preg_replace('#/+#', '/', $uri);

        if (empty($uri) || $uri === '/') {
            $uri = '/';
        } else {
            $uri = rtrim($uri, '/');
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {
            $registeredRoute = $route['route'] !== '/' ? rtrim($route['route'], '/') : '/';

            // Converte parâmetros dinâmicos como {id} para regex (?P<id>[^/]+)
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $registeredRoute);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Extrai apenas os parâmetros nomeados capturados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->dispatch($route, $params);
                return;
            }
        }

        $this->handleNotFound($uri);
    }

    private function dispatch(array $route, array $params = []): void
    {
        list($controller, $method) = explode('@', $route['action']);

        $controller = str_replace('/', '\\', $controller);
        $controllerClass = "app\\controllers\\$controller";

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Controller {$controllerClass} não encontrado.");
        }

        $controllerObj = new $controllerClass();

        if (!method_exists($controllerObj, $method)) {
            throw new RuntimeException("Método {$method} não encontrado em {$controllerClass}.");
        }

        // Executa o método do controller passando os parâmetros dinâmicos da URL
        call_user_func_array([$controllerObj, $method], $params);
    }

    private function handleNotFound(string $uri): void
{
    http_response_code(404);

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Se for explicitamente requisição de API / Fetch
    if ($isAjax || (strpos($accept, 'application/json') !== false && strpos($accept, 'text/html') === false)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'erro',
            'message' => "Rota não encontrada: {$uri}"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Se for navegação no navegador, renderiza a View 404
    $viewPath = __DIR__ . '/../views/errors/404.php';
    if (file_exists($viewPath)) {
        require_once $viewPath;
    } else {
        echo "<h1>404 - Página não encontrada</h1>";
    }
    exit;
}

    public function getAllRoutes(): array
    {
        return $this->routes;
    }
}