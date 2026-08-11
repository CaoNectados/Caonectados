<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/config.php';

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

    $isDev = defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true;
    
    $mensagem = $isDev 
        ? $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine()
        : "Ocorreu um erro interno no servidor. Tente novamente mais tarde.";

    if (strpos($accept, 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $mensagem], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo "<h1>Ops! Algo deu errado.</h1><p>" . htmlspecialchars($mensagem) . "</p>";
    exit;
});

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
$router->get('/onboarding', 'onboarding/OnBoardingController@index');
$router->get('/onboarding/tutor', 'onboarding/OnBoardingController@tutor');
$router->get('/onboarding/ong', 'onboarding/OnBoardingController@ong');
$router->get('/onboarding/protetor', 'onboarding/OnBoardingController@protetor');

// Rotas de submissão do formulário dados
$router->post('/onboarding/salvar-tutor', 'onboarding/OnBoardingController@salvarTutor');
$router->post('/onboarding/salvar-protetor', 'onboarding/OnBoardingController@salvarProtetor');
$router->get('/onboarding/aguardando-aprovacao', 'onboarding/OnBoardingController@aguardandoAprovacao');
$router->get('/aguardando-aprovacao', 'onboarding/OnBoardingController@aguardandoAprovacao');// ==========================================
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

// ROTAS DE REDEFINIÇÃO DE SENHA DO PERFIL (LOGADO)
$router->get('/perfil/redefinir-senha', 'geral/PerfilController@telaRedefinirSenha');
$router->post('/perfil/redefinir-senha/enviar-codigo', 'geral/PerfilController@enviarCodigoSenha');
$router->post('/perfil/redefinir-senha/confirmar', 'geral/PerfilController@confirmarNovaSenha');

// ROTAS DE TROCA DE E-MAIL (LOGADO)
$router->get('/perfil/trocar-email', 'geral/PerfilController@telaTrocarEmail');
$router->post('/perfil/trocar-email/enviar-codigo', 'geral/PerfilController@enviarCodigoTrocaEmail');
$router->post('/perfil/trocar-email/confirmar', 'geral/PerfilController@confirmarTrocaEmail');

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

//Raças e especies
$router->get('/admin/gerenciar-especies-racas', 'admin/RacaController@gerenciarEspeciesRacas');


// ==========================================
// ROTAS CRUD REGIÃO (ADMIN)
// ==========================================
$router->get('/admin/regiao', 'admin/RegiaoController@index');
$router->get('/admin/regiao/cadastrar', 'admin/RegiaoController@create');
$router->get('/admin/regiao/editar', 'admin/RegiaoController@edit');
$router->get('/admin/regiao/excluir', 'admin/RegiaoController@deleteView');

$router->post('/admin/regiao/salvar', 'admin/RegiaoController@store');
$router->post('/admin/regiao/atualizar', 'admin/RegiaoController@update');
$router->post('/admin/regiao/deletar', 'admin/RegiaoController@destroy');

$router->get('/admin/regiao/json', 'admin/RegiaoController@buscarJson');

// ==========================================
// ROTAS CRUD ESPECIE (ADMIN)
// ==========================================
$router->get('/admin/especie', 'admin/EspecieController@index');
$router->get('/admin/especie/cadastrar', 'admin/EspecieController@create');
$router->get('/admin/especie/editar', 'admin/EspecieController@edit');
$router->get('/admin/especie/excluir', 'admin/EspecieController@deleteView');
$router->get('/admin/especie/reativar', 'admin/EspecieController@reativar');

$router->post('/admin/especie/salvar', 'admin/EspecieController@store');
$router->post('/admin/especie/atualizar', 'admin/EspecieController@update');
$router->post('/admin/especie/deletar', 'admin/EspecieController@destroy');

$router->get('/admin/especie/json', 'admin/EspecieController@buscarJson');

// ==========================================
// ROTAS CRUD RACA (ADMIN)
// ==========================================
$router->get('/admin/raca', 'admin/RacaController@index');
$router->get('/admin/raca/cadastrar', 'admin/RacaController@create');
$router->get('/admin/raca/editar', 'admin/RacaController@edit');
$router->get('/admin/raca/excluir', 'admin/RacaController@deleteView');
$router->get('/admin/raca/reativar', 'admin/RacaController@reativar');

$router->post('/admin/raca/salvar', 'admin/RacaController@store');
$router->post('/admin/raca/atualizar', 'admin/RacaController@update');
$router->post('/admin/raca/deletar', 'admin/RacaController@destroy');
$router->post('/admin/raca/importar', 'admin/RacaController@importar');

$router->get('/admin/raca/json', 'admin/RacaController@buscarJson');

// ==========================================
// GERENCIAMENTO DE USUÁRIOS (ADMIN)
// ==========================================
$router->get('/admin/gerenciar-usuarios', 'admin/UsuarioController@index');
$router->get('/admin/usuarios/detalhes', 'admin/UsuarioController@detalhes');
$router->post('/admin/usuarios/alterar-status', 'admin/UsuarioController@alterarStatusUsuario');
$router->post('/admin/usuarios/alterar-status-perfil', 'admin/UsuarioController@alterarStatusPerfil');
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

