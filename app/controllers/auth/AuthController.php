<?php

namespace app\controllers\auth;

use app\core\Controller;
use app\services\AuthService;
use app\services\MailService;
use app\repositories\UsuarioRepository;
use Exception;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->authService = new AuthService();
    }

    public function login()
    {
        $this->view('auth/login', [
            'titulo'    => 'Login',
            'descricao' => 'Acesse sua conta no CãoNectados.'
        ]);
    }

    public function processarLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Por favor, preencha todos os campos.']);
        }

        try {
            $usuario = $this->authService->autenticar($email, $senha);

            // SE FOR ADMIN: Inicia processo 2FA (reaproveita a tela de verificação)
            if ($usuario->getTipoAtual() === 'administrador') {
                $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                $usuarioRepo = new UsuarioRepository();
                $usuarioRepo->salvarCodigoVerificacao($usuario->getUsuarioId(), $codigo, $expiraEm);

                // Define as sessões para a validação do 2FA
                $_SESSION['admin_2fa'] = [
                    'usuario_id' => $usuario->getUsuarioId(),
                    'email'      => $usuario->getEmail()
                ];
                // Variável usada na view verificar_email.php
                $_SESSION['email_pendente_verificacao'] = $usuario->getEmail();

                // Envio com o contexto de 2FA do Admin
                MailService::enviarCodigoVerificacao($usuario->getEmail(), $usuario->getNome() ?? 'Admin', $codigo, 'login_admin');

                $this->json(200, [
                    'status'       => 'sucesso', 
                    'mensagem'     => 'Código de segurança enviado para seu e-mail administrativo.', 
                    'redirect_url' => URL_BASE . '/verificar-email'
                ]);
            }

            // SE NÃO FOR ADMIN: Inicia a sessão normalmente
           // SE NÃO FOR ADMIN: Inicia a sessão normalmente
            $this->authService->iniciarSessao($usuario);

            $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
            $validado = $_SESSION['validado'] ?? false; // Puxou da tabela protetor via AuthService

            $urlRedirect = '/home';
            if ($tipoPerfil === 'usuario') {
                $urlRedirect = '/onboarding';
            } elseif (in_array($tipoPerfil, ['ong', 'protetor']) && $validado === false) {
                // Se é ONG/Protetor e ainda não foi validado (0) pelo admin, vai para a espera
                $urlRedirect = '/aguardando-aprovacao';
            } else {
                // Adotantes e Protetores validados (1) vão para o perfil.
                // TODO: trocar para '/feed' quando o Feed voltar a ser implementado.
                $urlRedirect = '/perfil';
            }

            $this->json(200, [
                'status'       => 'sucesso', 
                'mensagem'     => 'Login efetuado com sucesso!', 
                'redirect_url' => URL_BASE . $urlRedirect
            ]);

        } catch (Exception $e) {
            $this->json(401, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function cadastro()
    {
        $this->view('auth/cadastro', [
            'titulo'    => 'Cadastre-se',
            'descricao' => 'Crie sua conta no CãoNectados para adotar ou cadastrar pets.'
        ]);
    }

    public function processarCadastro()
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $senha_confirmacao = $_POST['senha_confirmacao'] ?? '';

        if (empty($email) || empty($senha) || empty($senha_confirmacao)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Insira um formato de e-mail válido.']);
        }

        // ==========================================
        // TRAVA PRÉVIA: VERIFICA SE O EMAIL JÁ EXISTE
        // ==========================================
        $usuarioRepo = new UsuarioRepository();
        if ($usuarioRepo->buscarPorEmail($email) !== null) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Este e-mail já está cadastrado em nossa plataforma.']);
        }

        if ($senha !== $senha_confirmacao) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'As senhas não coincidem.']);
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.']);
        }

        $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $_SESSION['pendente_cadastro'] = [
            'email'       => $email,
            'senha'       => $senha,
            'tipo_perfil' => 'usuario',
            'codigo'      => $codigo,
            'expira_em'   => date('Y-m-d H:i:s', strtotime('+15 minutes'))
        ];
        $_SESSION['email_pendente_verificacao'] = $email;

        try {
            MailService::enviarCodigoVerificacao($email, 'Usuário', $codigo, 'cadastro');

            $this->json(200, [
                'status'       => 'sucesso', 
                'mensagem'     => 'Verifique seu e-mail para continuar.', 
                'redirect_url' => URL_BASE . '/verificar-email'
            ]);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => 'Erro ao enviar e-mail.']);
        }
    }

   public function processarVerificacao()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $codigoInformado = trim($_POST['codigo'] ?? $input['codigo'] ?? '');

        if (empty($codigoInformado)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Código de verificação vazio.']);
        }

        try {
            // ==========================================
            // CONTEXTO: 2FA DE ADMINISTRADOR
            // ==========================================
            if (isset($_SESSION['admin_2fa'])) {
                $dadosPendente = $_SESSION['admin_2fa'];
                $usuarioRepo = new UsuarioRepository();
                
                $registro = $usuarioRepo->buscarCodigoValido($dadosPendente['usuario_id'], $codigoInformado);

                if (!$registro) {
                    $this->json(400, ['status' => 'erro', 'mensagem' => 'Código inválido ou expirado.']);
                    return;
                }

                $usuarioRepo->marcarCodigoComoUsado((int)$registro['codigo_id']);
                
                $usuarioObj = $usuarioRepo->buscarPorEmail($dadosPendente['email']);
                if ($usuarioObj) {
                    $this->authService->iniciarSessao($usuarioObj);
                }

                unset($_SESSION['admin_2fa'], $_SESSION['email_pendente_verificacao']);

                $this->json(200, [
                    'status'       => 'sucesso', 
                    'mensagem'     => 'Acesso administrativo confirmado!', 
                    'redirect_url' => URL_BASE . '/admin/dashboard'
                ]);
                return;
            }

            // ==========================================
            // CONTEXTO: CADASTRO DE NOVO USUÁRIO
            // ==========================================
            if (isset($_SESSION['pendente_cadastro'])) {
                $dadosPendentes = $_SESSION['pendente_cadastro'];

                if ($dadosPendentes['codigo'] !== $codigoInformado || strtotime($dadosPendentes['expira_em']) < time()) {
                    $_SESSION['falhas_codigo'] = ($_SESSION['falhas_codigo'] ?? 0) + 1;
                    if ($_SESSION['falhas_codigo'] >= 5) {
                        unset($_SESSION['pendente_cadastro']);
                        $this->json(429, ['status' => 'erro', 'mensagem' => 'Muitas tentativas falhas. O seu cadastro foi cancelado por segurança.']);
                    }
                    $this->json(400, ['status' => 'erro', 'mensagem' => 'Código inválido ou expirado.']);
                    return;
                }

                $this->authService->registrar(
                    $dadosPendentes['email'], 
                    $dadosPendentes['senha'], 
                    $dadosPendentes['tipo_perfil']
                );

                unset($_SESSION['pendente_cadastro'], $_SESSION['email_pendente_verificacao'], $_SESSION['falhas_codigo']);

                $usuarioRepo = new UsuarioRepository();
                $usuarioObj = $usuarioRepo->buscarPorEmail($dadosPendentes['email']);
                
                if ($usuarioObj) {
                    $this->authService->iniciarSessao($usuarioObj);
                }

                $this->json(200, [
                    'status'       => 'sucesso', 
                    'mensagem'     => 'E-mail confirmado com sucesso!', 
                    'redirect_url' => URL_BASE . '/onboarding'
                ]);
                return;
            }

            $this->json(400, ['status' => 'erro', 'mensagem' => 'Sessão expirada. Faça login ou cadastro novamente.']);

        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function reenviarCodigo()
    {
        try {
            $this->authService->validarRateLimitReenvioCodigo();

            // REENVIO PARA ADMIN 2FA
            if (isset($_SESSION['admin_2fa'])) {
                $email = $_SESSION['admin_2fa']['email'];
                $usuarioId = $_SESSION['admin_2fa']['usuario_id'];
                
                $novoCodigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $usuarioRepo = new UsuarioRepository();
                $usuarioRepo->salvarCodigoVerificacao($usuarioId, $novoCodigo, $expiraEm);

                MailService::enviarCodigoVerificacao($email, 'Administrador', $novoCodigo, 'login_admin');

                $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Um novo código foi enviado para seu e-mail.']);
            } 
            // REENVIO PARA CADASTRO NORMAL
            elseif (isset($_SESSION['pendente_cadastro'])) {
                $novoCodigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['pendente_cadastro']['codigo'] = $novoCodigo;
                $_SESSION['pendente_cadastro']['expira_em'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                $email = $_SESSION['pendente_cadastro']['email'];
                MailService::enviarCodigoVerificacao($email, 'Usuário', $novoCodigo, 'cadastro');

                $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Um novo código foi enviado para seu e-mail.']);
            } 
            else {
                $this->json(400, ['status' => 'erro', 'mensagem' => 'Nenhum processo pendente encontrado para reenvio.']);
            }

        } catch (Exception $e) {
            $this->json(429, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        $this->redirecionarComMensagem('sucesso', 'Sessão encerrada com sucesso.', '/login');
    }

    public function telaVerificacao()
    {
        $this->view('auth/verificar_email', ['titulo' => 'Verificação de Segurança']);
    }

    public function esqueciSenha()
    {
        $this->view('auth/esqueci_senha', ['titulo' => 'Esqueci minha senha']);
    }

    public function processarEsqueciSenha()
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Informe um e-mail válido.']);
        }

        try {
            $this->authService->solicitarRecuperacaoSenha($email);
            $this->json(200, [
                'status'       => 'sucesso', 
                'mensagem'     => 'Se o e-mail existir em nossa base, enviaremos um link de recuperação.', 
                'redirect_url' => URL_BASE . '/login'
            ]);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => 'Ocorreu um erro interno. Tente novamente mais tarde.']);
        }
    }

    public function redefinirSenha()
    {
        $email = $_GET['email'] ?? '';
        $codigo = $_GET['codigo'] ?? '';

        if (empty($email) || empty($codigo)) {
            $this->redirecionarComMensagem('erro', 'Link de recuperação inválido.', '/login');
        }

        $this->view('auth/redefinir_senha', [
            'titulo' => 'Criar Nova Senha',
            'email'  => $email,
            'codigo' => $codigo
        ]);
    }

    public function processarRedefinirSenha()
    {
        $email = trim($_POST['email'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $senha_confirmacao = $_POST['senha_confirmacao'] ?? '';

        if (empty($email) || empty($codigo) || empty($senha) || empty($senha_confirmacao)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Preencha todos os campos.']);
        }

        if ($senha !== $senha_confirmacao) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'As senhas não coincidem.']);
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'A senha deve conter 8 caracteres, maiúsculas, minúsculas, números e um caractere especial.']);
        }

        try {
            $this->authService->redefinirSenha($email, $codigo, $senha);
            $this->json(200, [
                'status'       => 'sucesso', 
                'mensagem'     => 'Senha redefinida com sucesso! Redirecionando...', 
                'redirect_url' => URL_BASE . '/login'
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
}