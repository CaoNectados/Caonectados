<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/config.php';

use app\core\Router;

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');



$router->run();