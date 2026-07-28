<?php
require_once __DIR__ . '/../vendor/autoload.php';
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

    // Rotas de submissão do formulário dados
    $router->post('/onboarding/salvar-tutor', 'OnboardingController@salvarTutor');
    $router->post('/onboarding/salvar-protetor', 'OnboardingController@salvarProtetor');

    //Tela espera para validação
    $router->get('/aguardando-aprovacao', 'OnboardingController@aguardandoAprovacao');

    // Rota para listar os usuários (admin)
    $router->get('/admin/gerenciar-usuarios', 'AdminUsuarioController@index');

    // Rotas para Edição (admin)
    $router->get('/admin/gerenciar-usuarios/editar/{id}', 'AdminUsuarioController@editar');
    $router->post('/admin/gerenciar-usuarios/atualizar/{id}', 'AdminUsuarioController@atualizar');

    // Rota para Exclusão (Inativação, apenas para admin)
    $router->get('/admin/gerenciar-usuarios/deletar/{id}', 'AdminUsuarioController@deletar');


// Rotas do feed
$router->get('/feed', 'FeedController@feed');

// Autenticação rotas
    $router->get('/login', 'AuthController@login');
    $router->post('/login', 'AuthController@processarLogin');
    $router->get('/cadastro', 'AuthController@cadastro');
    $router->post('/cadastro', 'AuthController@processarCadastro');

    $router->get('/logout', 'AuthController@logout');
    // Rotas de verificação de e-mail
    $router->get('/verificar-email', 'AuthController@telaVerificacao');
    $router->post('/verificar-email/validar', 'AuthController@processarVerificacao');
    $router->get('/reenviar-codigo', 'AuthController@reenviarCodigo');
    // Recuperação de Senha
    $router->get('/esqueci-senha', 'AuthController@esqueciSenha');
    $router->post('/esqueci-senha/processar', 'AuthController@processarEsqueciSenha');
    $router->get('/redefinir-senha', 'AuthController@redefinirSenha');
    $router->post('/redefinir-senha/processar', 'AuthController@processarRedefinirSenha');

$router->run();