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
$router->post('/redefinir-senha/processar', 'auth/AuthController@processarRedefinirSenha');


// ==========================================
// ROTAS DO ADMINISTRADOR 
// ==========================================

// Dashboard
$router->get('/admin/dashboard', 'admin/DashboardController@index');

// Solicitações de Cadastro
$router->get('/admin/validacao-cadastros', 'admin/SolicitacaoController@index');

// Gerenciamento de Usuários
$router->get('/admin/gerenciar-usuarios', 'admin/UsuarioController@index');


// ==========================================
// ROTAS CRUD REGIÃO (ADMIN)
// ==========================================
$router->get('/admin/regioes', 'admin/RegiaoController@index');
$router->get('/admin/regioes/cadastrar', 'admin/RegiaoController@create');
$router->get('/admin/regioes/editar', 'admin/RegiaoController@edit');
$router->get('/admin/regioes/excluir', 'admin/RegiaoController@deleteView');

$router->post('/admin/regioes/salvar', 'admin/RegiaoController@store');
$router->post('/admin/regioes/atualizar', 'admin/RegiaoController@update');
$router->post('/admin/regioes/deletar', 'admin/RegiaoController@destroy');

$router->get('/admin/regioes/json', 'admin/RegiaoController@buscarJson');

// ==========================================
// ROTAS CRUD ESPECIE (ADMIN)
// ==========================================
$router->get('/admin/especies', 'admin/EspecieController@index');
$router->get('/admin/especies/cadastrar', 'admin/EspecieController@create');
$router->get('/admin/especies/editar', 'admin/EspecieController@edit');
$router->get('/admin/especies/excluir', 'admin/EspecieController@deleteView');
$router->get('/admin/especies/reativar', 'admin/EspecieController@reativar');

$router->post('/admin/especies/salvar', 'admin/EspecieController@store');
$router->post('/admin/especies/atualizar', 'admin/EspecieController@update');
$router->post('/admin/especies/deletar', 'admin/EspecieController@destroy');

$router->get('/admin/especies/json', 'admin/EspecieController@buscarJson');

// ==========================================
// ROTAS CRUD RACA (ADMIN)
// ==========================================
$router->get('/admin/racas', 'admin/RacaController@index');
$router->get('/admin/racas/cadastrar', 'admin/RacaController@create');
$router->get('/admin/racas/editar', 'admin/RacaController@edit');
$router->get('/admin/racas/excluir', 'admin/RacaController@deleteView');
$router->get('/admin/racas/reativar', 'admin/RacaController@reativar');

$router->post('/admin/racas/salvar', 'admin/RacaController@store');
$router->post('/admin/racas/atualizar', 'admin/RacaController@update');
$router->post('/admin/racas/deletar', 'admin/RacaController@destroy');
$router->post('/admin/racas/importar', 'admin/RacaController@importar');

$router->get('/admin/racas/json', 'admin/RacaController@buscarJson');

// ==========================================
// ROTAS CRUD ANIMAL
// ==========================================
$router->get('/animal', 'animal/AnimalController@index');
$router->get('/animal/mostrar', 'animal/AnimalController@show');
$router->post('/animal', 'animal/AnimalController@store');
$router->post('/animal/editar', 'animal/AnimalController@update');
$router->post('/animal/status', 'animal/AnimalController@status');
$router->post('/animal/reativar', 'animal/AnimalController@reativar');
$router->post('/animal/excluir', 'animal/AnimalController@destroy');

$router->run();