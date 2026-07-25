<?php

namespace app\core;

use Exception;

class Router
{
    private array $routes = [];

    public function get($route, $action){
        $this->routes[] = [
            'method' => 'get',
            'route' => $route,
            'action' => $action
        ];
    }

    public function post($route, $action){
        $this->routes[] = [
            'method' => 'post',
            'route' => $route,
            'action' => $action
        ];
    }

    public function run()
    {
        // Extrai apenas o caminho, removendo a query string (ex: ?simular_perfil=protetor)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Define o caminho base onde o projeto está rodando (pasta public)
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('\\', '/', $basePath);
        
        if ($basePath === '/') {
            $basePath = '';
        }

        // Remove a pasta base da URI para termos apenas a rota final
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // --- CORREÇÃO DA BARRA DUPLA AQUI ---
        // Se vier //login da URL, ele transforma em /login limpo
        $uri = preg_replace('#/+#', '/', $uri);

        // Se a URI ficar vazia após remover o basePath, ou for apenas uma barra, a rota é '/'
        if (empty($uri) || $uri === '/') {
            $uri = '/';
        } else {
            // Para outras rotas (ex: /cadastro/), removemos a barra final para padronizar
            $uri = rtrim($uri, '/');
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {
            $registeredRoute = $route['route'];
            
            // Padroniza a rota registrada da mesma forma
            if ($registeredRoute !== '/') {
                $registeredRoute = rtrim($registeredRoute, '/');
            }

            if ($registeredRoute === $uri && $route['method'] === $method) {
                return $this->dispatch($route);
            }
        }

        http_response_code(404);
        exit('Rota não encontrada. Rota solicitada: ' . htmlspecialchars($uri));
    }

    public function dispatch($route){

        list($controller, $method) = explode('@', $route['action']);

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

    public function getAllRoutes(){
        return $this->routes;
    }
}