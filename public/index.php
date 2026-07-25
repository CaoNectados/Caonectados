<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/config.php';

use app\core\Router;

$router = new Router();

// Home rotas
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// RF 1 - Manter usuário rotas
$router->get('/onboarding', 'OnboardingController@index');
$router->get('/onboarding/adotante', 'OnboardingController@adotante');
$router->post('/onboarding/adotante/salvar', 'OnboardingController@storeAdotante');
$router->get('/onboarding/ong', 'OnboardingController@ong');
$router->post('/onboarding/ong/salvar', 'OnboardingController@storeOng');

// Autenticação rotas
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@processarLogin');
$router->get('/cadastro', 'AuthController@cadastro');
$router->post('/cadastro', 'AuthController@processarCadastro');
$router->get('/logout', 'AuthController@logout');

$router->run();