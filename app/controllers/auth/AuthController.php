<?php

namespace app\controllers\auth;

use app\core\Controller;
use app\services\AuthService;
use app\services\MailService;
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
            // Toda a regra de login, status da conta e bloqueios estão agora no Service
            $usuario = $this->authService->autenticar($email, $senha);
            
            // Cria a sessão com segurança (regenerate_id)
            $this->authService->iniciarSessao($usuario);

            $tipoPerfil = $_SESSION['tipo_perfil'];
            $statusConta = $_SESSION['status_conta'];

            // Define a URL de redirecionamento
            $urlRedirect = '/home';
            if ($tipoPerfil === 'usuario') {
                $urlRedirect = '/onboarding';
            } elseif ($statusConta === 'pendente') {
                $urlRedirect = '/aguardando-aprovacao';
            } elseif ($tipoPerfil === 'administrador') {
                $urlRedirect = '/admin/dashboard';
            }

            $this->json(200, [
                'status' => 'sucesso', 
                'mensagem' => 'Login efetuado com sucesso!', 
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

        if ($senha !== $senha_confirmacao) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'As senhas não coincidem.']);
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.']);
        }

        // Em vez de inserir direto, mantemos no estado pendente de sessão
        $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $_SESSION['pendente_cadastro'] = [
            'email'       => $email,
            'senha'       => $senha, // Guardamos a senha crua na sessão temporária, será linkada pelo Service no final
            'tipo_perfil' => 'usuario',
            'codigo'      => $codigo,
            'expira_em'   => date('Y-m-d H:i:s', strtotime('+15 minutes'))
        ];
        $_SESSION['email_pendente_verificacao'] = $email;

        try {
            MailService::enviarCodigoVerificacao($email, $email, $codigo);
            $this->json(200, [
                'status' => 'sucesso', 
                'mensagem' => 'Verifique seu e-mail para continuar.', 
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
        $dadosPendentes = $_SESSION['pendente_cadastro'] ?? null;

        if (!$dadosPendentes || empty($codigoInformado)) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Sessão expirada ou código vazio. Faça o cadastro novamente.']);
        }

        // Verifica expiração e precisão
        if ($dadosPendentes['codigo'] !== $codigoInformado || strtotime($dadosPendentes['expira_em']) < time()) {
            // Anti-brute force
            $_SESSION['falhas_codigo'] = ($_SESSION['falhas_codigo'] ?? 0) + 1;
            if ($_SESSION['falhas_codigo'] >= 5) {
                unset($_SESSION['pendente_cadastro']);
                $this->json(429, ['status' => 'erro', 'mensagem' => 'Muitas tentativas falhas. O seu cadastro foi cancelado por segurança.']);
            }
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Código inválido ou expirado.']);
        }

        try {
            // O Service cuida da inserção, hash da senha e banco!
            $usuarioId = $this->authService->registrar(
                $dadosPendentes['email'], 
                $dadosPendentes['senha'], 
                $dadosPendentes['tipo_perfil']
            );

            unset($_SESSION['pendente_cadastro'], $_SESSION['email_pendente_verificacao'], $_SESSION['falhas_codigo']);

            // Criamos a sessão oficial
            $_SESSION['usuario_id']    = $usuarioId;
            $_SESSION['usuario_email'] = $dadosPendentes['email'];
            $_SESSION['tipo_perfil']   = $dadosPendentes['tipo_perfil'];

            $this->json(200, [
                'status' => 'sucesso', 
                'mensagem' => 'E-mail confirmado com sucesso!', 
                'redirect_url' => URL_BASE . '/onboarding'
            ]);

        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /**
     * O Método novo para Reenviar Código com Rate Limit!
     */
    public function reenviarCodigo()
    {
        $dadosPendentes = $_SESSION['pendente_cadastro'] ?? null;

        if (!$dadosPendentes) {
            $this->json(400, ['status' => 'erro', 'mensagem' => 'Nenhum cadastro pendente encontrado.']);
        }

        try {
            // Proteção contra múltiplos cliques
            $this->authService->validarRateLimitReenvioCodigo();

            // Gera novo código
            $novoCodigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['pendente_cadastro']['codigo'] = $novoCodigo;
            $_SESSION['pendente_cadastro']['expira_em'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            MailService::enviarCodigoVerificacao($dadosPendentes['email'], $dadosPendentes['email'], $novoCodigo);

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Um novo código foi enviado para seu e-mail.']);
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
        $this->redirect("/login");
    }

    public function telaVerificacao()
    {
        $this->view('auth/verificar_email', ['titulo' => 'Verificação de E-mail']);
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
            // Mensagem genérica para não permitir varredura de e-mails existentes (Blindagem)
            $this->json(200, [
                'status' => 'sucesso', 
                'mensagem' => 'Se o e-mail existir em nossa base, enviaremos um link de recuperação.', 
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
            $this->redirecionarComMensagem('erro', 'Link de recuperación inválido.', '/login');
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
                'status' => 'sucesso', 
                'mensagem' => 'Senha redefinida com sucesso! Redirecionando...', 
                'redirect_url' => URL_BASE . '/login'
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
}