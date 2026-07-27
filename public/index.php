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

// --- ESPÉCIES ---
$router->get('/especies', 'EspeciesController@index');
$router->get('/especies/cadastrar', 'EspeciesController@create');
$router->post('/especies/salvar', 'EspeciesController@store');
$router->get('/especies/editar', 'EspeciesController@edit');
$router->post('/especies/atualizar', 'EspeciesController@update');
$router->get('/especies/excluir', 'EspeciesController@deleteView');
$router->post('/especies/deletar', 'EspeciesController@destroy');
$router->get('/especies/reativar', 'EspeciesController@reativar');

// --- RAÇAS ---
$router->get('/racas', 'RacasController@index');
$router->get('/racas/cadastrar', 'RacasController@create');
$router->post('/racas/salvar', 'RacasController@store');
$router->get('/racas/editar', 'RacasController@edit');
$router->post('/racas/atualizar', 'RacasController@update');
$router->get('/racas/excluir', 'RacasController@deleteView');
$router->post('/racas/deletar', 'RacasController@destroy');
$router->post('/racas/importar', 'RacasController@importar');
$router->get('/racas/reativar', 'RacasController@reativar');

$router->run();
