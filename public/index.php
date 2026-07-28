<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/config.php';

use app\core\Router;

$router = new Router();


// ==========================================
// ROTAS DE PÁGINAS PÚBLICAS
// ==========================================
    $router->get('/', 'geral/HomeController@index');
    $router->get('/home', 'geral/HomeController@index');


// ==========================================
// ROTAS ONBOARDING
// ==========================================
    $router->get('/onboarding', 'onboarding/OnboardingController@index');
    $router->get('/onboarding/tutor', 'onboarding/OnboardingController@tutor');
    $router->get('/onboarding/ong', 'onboarding/OnboardingController@ong');
    $router->get('/onboarding/protetor', 'onboarding/OnboardingController@protetor');

    // Rotas de submissão do formulário dados
    $router->post('/onboarding/salvar-tutor', 'onboarding/OnboardingController@salvarTutor');
    $router->post('/onboarding/salvar-protetor', 'onboarding/OnboardingController@salvarProtetor');
    $router->post('/onboarding/aguardando-aprovacao', 'onboarding/OnboardingController@aguardandoAprovacao');



// ==========================================
// ROTAS PROTETOR, ONG, TUTOR E ADMIN
// ==========================================
$router->get('/feed', 'geral/FeedController@feed');

    // ==========================================
    // ROTAS DE PERFIL
    $router->get('/perfil', 'geral/PerfilController@perfil');
    $router->get('/perfil', 'geral/PerfilController@index');
    $router->get('/perfil/editar', 'geral/PerfilController@editar');
    $router->post('/perfil/atualizar', 'geral/PerfilController@atualizar');
    $router->get('/perfil/editar-foto', 'geral/PerfilController@editarFoto');
    $router->post('/perfil/atualizar-foto', 'geral/PerfilController@atualizarFoto');
// ==========================================
// ROTAS DE AUTENTICAÇÃO
// ==========================================
    $router->get('/login', 'auth/AuthController@login');
    $router->post('/login', 'auth/AuthController@processarLogin');
    $router->get('/cadastro', 'auth/AuthController@cadastro');
    $router->post('/cadastro', 'auth/AuthController@processarCadastro');

    $router->get('/logout', 'auth/AuthController@logout');
    // Rotas de verificação de e-mail
    $router->get('/verificar-email', 'auth/AuthController@telaVerificacao');
    $router->post('/verificar-email/validar', 'auth/AuthController@processarVerificacao');
    $router->get('/reenviar-codigo', 'auth/AuthController@reenviarCodigo');
    // Recuperação de Senha
    $router->get('/esqueci-senha', 'auth/AuthController@esqueciSenha');
    $router->post('/esqueci-senha/processar', 'auth/AuthController@processarEsqueciSenha');
    $router->get('/redefinir-senha', 'auth/AuthController@redefinirSenha');
    $router->post('/redefinir-senha/processar', 'AuthController@processarRedefinirSenha');


// ==========================================
// ROTAS DO ADMINISTRADOR 
// ==========================================

    // Dashboard
    $router->get('/admin/dashboard', 'admin/DashboardController@index');
    
    // Solicitações de Cadastro
    $router->get('/admin/validacao-cadastros', 'admin/SolicitacaoController@index');
    
    // Gerenciamento de Usuários
    $router->get('/admin/gerenciar-usuarios', 'admin/UsuarioController@index');


$router->run();