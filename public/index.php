<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/config.php';

use app\core\Router;

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

$router->get('/animais', 'AnimaisController@index');
$router->get('/animais/mostrar', 'AnimaisController@show');
$router->post('/animais', 'AnimaisController@store');
$router->post('/animais/editar', 'AnimaisController@update');
$router->post('/animais/status', 'AnimaisController@status');
$router->post('/animais/reativar', 'AnimaisController@reativar');
$router->post('/animais/excluir', 'AnimaisController@destroy');


$router->get('/regioes', 'RegioesController@index');
$router->get('/regioes/cadastrar', 'RegioesController@create');
$router->get('/regioes/editar', 'RegioesController@edit');
$router->get('/regioes/excluir', 'RegioesController@deleteView');

$router->post('/regioes/salvar', 'RegioesController@store');
$router->post('/regioes/atualizar', 'RegioesController@update');
$router->post('/regioes/deletar', 'RegioesController@destroy');

$router->run();