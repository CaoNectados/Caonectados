<?php
namespace app\controllers\testes {
    class RotaController {
        public function mostrar(string $id): void { echo 'rota:'.$id; }
        public function inicio(): void { echo 'inicio'; }
    }
}
namespace {
    require __DIR__.'/../vendor/autoload.php';
    $base=$argv[1]??'';
    define('URL_BASE','http://localhost'.$base);
    $_SERVER['SCRIPT_NAME']=$base.'/public/index.php';
    $_SERVER['REQUEST_URI']=$base.'/rota/42?origem=teste';
    $_SERVER['REQUEST_METHOD']='POST';
    $router=new \app\core\Router();
    $router->post('/rota/{id}','testes/RotaController@mostrar');
    ob_start();$router->run();$saida=ob_get_clean();
    if($saida!=='rota:42')throw new \Exception('Falha de normalização');
    echo 'OK POST com query, base '.($base?:'/').' e SCRIPT_NAME interno com public'.PHP_EOL;
}
