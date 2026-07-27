<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/config.php';

use app\core\Router;

$router = new Router();

// Home rotas
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');
// RF 1 - Manter usuário rotas

    // Rotas do Onboarding
    $router->get('/onboarding', 'OnboardingController@index');
    $router->get('/onboarding/tutor', 'OnboardingController@tutor');
    $router->get('/onboarding/ong', 'OnboardingController@ong');
    $router->get('/onboarding/protetor', 'OnboardingController@protetor');

    // Rotas de submissão do formulário
    $router->post('/onboarding/salvar-tutor', 'OnboardingController@salvarTutor');
    $router->post('/onboarding/salvar-protetor', 'OnboardingController@salvarProtetor');
    //Tela espera para validação
    $router->get('/aguardando-aprovacao', 'OnboardingController@aguardandoAprovacao');



// Autenticação rotas
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@processarLogin');
$router->get('/cadastro', 'AuthController@cadastro');
$router->post('/cadastro', 'AuthController@processarCadastro');
$router->get('/logout', 'AuthController@logout');

$router->run();