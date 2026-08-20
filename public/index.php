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
// 1. ROTAS DE PÁGINAS PÚBLICAS
// ==========================================
$router->get('/', 'geral/HomeController@index');
$router->get('/home', 'geral/HomeController@index');


// ==========================================
// 2. ROTAS ONBOARDING
// ==========================================
$router->get('/onboarding', 'onboarding/OnBoardingController@index');
$router->get('/onboarding/adotante', 'onboarding/OnBoardingController@adotante');
$router->get('/onboarding/ong', 'onboarding/OnBoardingController@ong');
$router->get('/onboarding/protetor', 'onboarding/OnBoardingController@protetor');

// Requisição AJAX para espécies ativas
$router->get('/onboarding/especies-ativas', 'onboarding/OnBoardingController@especiesAtivas');

// Submissão de formulários do onboarding
$router->post('/onboarding/salvar-adotante', 'onboarding/OnBoardingController@salvarAdotante');
$router->post('/onboarding/salvar-protetor', 'onboarding/OnBoardingController@salvarProtetor');
$router->get('/aguardando-aprovacao', 'onboarding/OnBoardingController@aguardandoAprovacao');


// ==========================================
// 3. ROTAS GERAIS (PERFIL)
// ==========================================
// Feed temporariamente removido desta etapa do projeto (controller/view/repository
// mantidos no código para reativação futura — só a rota está desligada).
// $router->get('/feed', 'geral/FeedController@feed');

// Perfil
$router->get('/perfil', 'geral/PerfilController@index');
$router->get('/perfil/editar', 'geral/PerfilController@editar');
$router->post('/perfil/atualizar', 'geral/PerfilController@atualizar');
$router->post('/perfil/atualizar-foto', 'geral/PerfilController@atualizarFoto');

// Redefinição de Senha do Perfil (Logado)
$router->get('/perfil/redefinir-senha', 'geral/PerfilController@telaRedefinirSenha');
$router->post('/perfil/redefinir-senha/enviar-codigo', 'geral/PerfilController@enviarCodigoSenha');
$router->post('/perfil/redefinir-senha/confirmar', 'geral/PerfilController@confirmarNovaSenha');

// Troca de E-mail (Logado)
$router->get('/perfil/trocar-email', 'geral/PerfilController@telaTrocarEmail');
$router->post('/perfil/trocar-email/enviar-codigo', 'geral/PerfilController@enviarCodigoTrocaEmail');
$router->post('/perfil/trocar-email/confirmar', 'geral/PerfilController@confirmarTrocaEmail');

// Alternar Perfil
$router->post('/perfil/trocar', 'geral/PerfilController@alternar');


// ==========================================
// 4. ROTAS DE AUTENTICAÇÃO
// ==========================================
$router->get('/login', 'auth/AuthController@login');
$router->post('/login', 'auth/AuthController@processarLogin');
$router->get('/cadastro', 'auth/AuthController@cadastro');
$router->post('/cadastro', 'auth/AuthController@processarCadastro');
$router->get('/logout', 'auth/AuthController@logout');

// Verificação de e-mail
$router->get('/verificar-email', 'auth/AuthController@telaVerificacao');
$router->post('/verificar-email/validar', 'auth/AuthController@processarVerificacao');
$router->get('/reenviar-codigo', 'auth/AuthController@reenviarCodigo');

// Recuperação de Senha
$router->get('/esqueci-senha', 'auth/AuthController@esqueciSenha');
$router->post('/esqueci-senha/processar', 'auth/AuthController@processarEsqueciSenha');
$router->get('/redefinir-senha', 'auth/AuthController@redefinirSenha');
$router->post('/redefinir-senha/processar', 'auth/AuthController@processarRedefinirSenha');


// ==========================================
// 5. ROTAS DO ADMINISTRADOR
// ==========================================

// Dashboard
$router->get('/admin/dashboard', 'admin/DashboardController@index');

// Solicitações de Cadastro (ONGs e Protetores)
$router->get('/admin/solicitacoes', 'admin\SolicitacaoProtetorController@index');
$router->get('/admin/solicitacoes/detalhes', 'admin\SolicitacaoProtetorController@detalhes');
$router->post('/admin/solicitacoes/aprovar', 'admin\SolicitacaoProtetorController@aprovar');
$router->post('/admin/solicitacoes/rejeitar', 'admin\SolicitacaoProtetorController@rejeitar');

// Gerenciamento de Usuários (Admin)
$router->get('/admin/gerenciar-usuarios', 'admin/UsuarioController@index');
$router->get('/admin/usuarios/detalhes', 'admin/UsuarioController@detalhes');
$router->post('/admin/usuarios/alterar-status', 'admin/UsuarioController@alterarStatusUsuario');
$router->post('/admin/usuarios/alterar-status-perfil', 'admin/UsuarioController@alterarStatusPerfil');

// Espécies e Raças (Admin)
$router->get('/admin/gerenciar-especies-racas', 'admin/RacaController@gerenciarEspeciesRacas');


// ==========================================
// 6. ROTAS CRUD REGIÃO (ADMIN)
// ==========================================
$router->get('/admin/regiao', 'admin/RegiaoController@index');
$router->get('/admin/regiao/cadastrar', 'admin/RegiaoController@create');
$router->get('/admin/regiao/editar', 'admin/RegiaoController@edit');
$router->get('/admin/regiao/excluir', 'admin/RegiaoController@deleteView');
$router->post('/admin/regiao/salvar', 'admin/RegiaoController@store');
$router->post('/admin/regiao/atualizar', 'admin/RegiaoController@update');
$router->post('/admin/regiao/deletar', 'admin/RegiaoController@destroy');
$router->post('/admin/regiao/deletar-multiplos', 'admin/RegiaoController@deletarMultiplos');
$router->get('/admin/regiao/json', 'admin/RegiaoController@buscarJson');


// ==========================================
// 7. ROTAS CRUD ESPÉCIE (ADMIN)
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
// 8. ROTAS CRUD RAÇA (ADMIN)
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
$router->get('/admin/raca/sugestoes-json', 'admin/RacaController@sugestoesJson');

// Rota pública/compartilhada para AJAX de raças
$router->get('/raca/json', 'admin\RacaController@buscarJson');


// ==========================================
// 9. ROTAS CRUD ANIMAL
// ==========================================
$router->get('/animal', 'animal/AnimalController@index');
$router->get('/gerenciar-animais', 'animal/AnimalController@index');
$router->get('/animal/mostrar', 'animal/AnimalController@show');
$router->post('/animal', 'animal/AnimalController@store');
$router->post('/animal/editar', 'animal/AnimalController@update');
$router->post('/animal/status', 'animal/AnimalController@status');
$router->post('/animal/reativar', 'animal/AnimalController@reativar');
$router->get('/animal/cadastrar', 'animal/AnimalController@create');
$router->get('/animal/editar', 'animal/AnimalController@edit');
$router->get('/animal/excluir', 'animal/AnimalController@deleteView');
$router->post('/animal/excluir', 'animal/AnimalController@destroy');


// ==========================================
// 10. EXECUTAR ROTEADOR
// ==========================================
$router->run();