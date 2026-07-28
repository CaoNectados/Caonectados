<?php

namespace app\controllers\auth;

use app\core\Controller;
use app\models\Usuario;
use app\database\ConnectionFactory;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Função auxiliar para retornar respostas em JSON para o AJAX
     */
    private function responderJson(string $status, string $mensagem, ?string $redirectUrl = null)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status'       => $status,
            'mensagem'     => $mensagem,
            'redirect_url' => $redirectUrl
        ]);
        exit;
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
            $this->responderJson('erro', 'Por favor, preencha todos os campos.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJson('erro', 'Informe um e-mail válido.');
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user->senha)) {
            $this->responderJson('erro', 'E-mail ou senha incorretos.');
        }

        $tipoPerfil = $user->tipo_perfil ?? $user->tipo_atual ?? 'usuario';
        $statusConta = $user->status_conta ?? 'pendente';

        if (in_array($statusConta, ['bloqueado', 'inativo', 'rejeitado'])) {
            $this->responderJson('erro', 'Sua conta está inativa ou bloqueada. Entre em contato com o suporte.');
        }

        $_SESSION['usuario_id']    = $user->usuario_id;
        $_SESSION['usuario_email'] = $user->email;
        $_SESSION['usuario_nome']  = $user->nome;
        $_SESSION['tipo_conta']    = $tipoPerfil;
        $_SESSION['status_conta']  = $statusConta;

        // Define a URL de redirecionamento baseada no status/tipo
        $urlRedirect = '/home';

        if (empty($tipoPerfil) || $tipoPerfil === 'usuario') {
            $urlRedirect = '/onboarding';
        } elseif ($statusConta === 'pendente') {
            $urlRedirect = '/aguardando-aprovacao';
        } elseif ($tipoPerfil === 'administrador') {
            $urlRedirect = '/admin/dashboard';
        } elseif (in_array($tipoPerfil, ['ong', 'protetor']) && $statusConta === 'ativo') {
            $_SESSION['boas_vindas_nome'] = $user->nome;
            $_SESSION['boas_vindas_tipo'] = $tipoPerfil;
        }

        $this->responderJson('sucesso', 'Login efetuado com sucesso!', URL_BASE . $urlRedirect);
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
        $tipoPerfil = 'usuario';

        if (empty($email) || empty($senha) || empty($senha_confirmacao)) {
            $this->responderJson('erro', 'Todos os campos são obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJson('erro', 'Insira um formato de e-mail válido.');
        }

        if ($senha !== $senha_confirmacao) {
            $this->responderJson('erro', 'As senhas não coincidem.');
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->responderJson('erro', 'A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.');
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->findByEmail($email)) {
            $this->responderJson('erro', 'Este e-mail já está cadastrado em nosso sistema.');
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $_SESSION['pendente_cadastro'] = [
            'email'       => $email,
            'senha'       => $hashSenha,
            'tipo_perfil' => $tipoPerfil,
            'codigo'      => $codigo,
            'expira_em'   => $expiraEm
        ];
        $_SESSION['email_pendente_verificacao'] = $email;

        \app\services\MailService::enviarCodigoVerificacao($email, $email, $codigo);

        $this->responderJson('sucesso', 'Verifique seu e-mail para continuar.', URL_BASE . '/verificar-email');
    }

    public function processarVerificacao()
    {
        $codigoInformado = trim($_POST['codigo'] ?? '');
        $dadosPendentes = $_SESSION['pendente_cadastro'] ?? null;

        if (!$dadosPendentes || empty($codigoInformado)) {
            $this->responderJson('erro', 'Sessão expirada ou código vazio. Faça o cadastro novamente.');
        }

        if ($dadosPendentes['codigo'] !== $codigoInformado || strtotime($dadosPendentes['expira_em']) < time()) {
            $this->responderJson('erro', 'Código inválido ou expirado.');
        }

        $usuarioModel = new Usuario();
        $dadosNovoUsuario = [
            'email'       => $dadosPendentes['email'],
            'senha'       => $dadosPendentes['senha'],
            'tipo_perfil' => $dadosPendentes['tipo_perfil']
        ];

        $usuarioId = $usuarioModel->create($dadosNovoUsuario);

        if (!$usuarioId) {
            $this->responderJson('erro', 'Erro ao criar conta no banco de dados.');
        }

        unset($_SESSION['pendente_cadastro']);
        unset($_SESSION['email_pendente_verificacao']);

        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_email'] = $dadosNovoUsuario['email'];
        $_SESSION['tipo_conta'] = $dadosNovoUsuario['tipo_perfil'];

        $this->responderJson('sucesso', 'E-mail confirmado com sucesso!', URL_BASE . '/onboarding');
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
            $this->responderJson('erro', 'Informe um e-mail válido.');
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if ($user) {
            $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiraEm = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $pdo = \app\database\ConnectionFactory::getConnection();
            $stmt = $pdo->prepare("INSERT INTO CODIGO_VERIFICACAO (usuario_id, codigo, expira_em) VALUES (?, ?, ?)");
            $stmt->execute([$user->usuario_id, $codigo, $expiraEm]);

            \app\services\MailService::enviarEmailRecuperacao($email, $user->nome ?? 'Usuário', $codigo);
        }

        $this->responderJson('sucesso', 'Se o e-mail existir, enviaremos um link de recuperação.', URL_BASE . '/login');
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
            $this->responderJson('erro', 'Preencha todos os campos.');
        }

        if ($senha !== $senha_confirmacao) {
            $this->responderJson('erro', 'As senhas não coincidem.');
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->responderJson('erro', 'A senha deve ter pelo menos 8 caracteres, letras maiúsculas, minúsculas, números e caractere especial.');
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user) {
            $this->responderJson('erro', 'Usuário não encontrado.');
        }

        $pdo = \app\database\ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM CODIGO_VERIFICACAO WHERE usuario_id = ? AND codigo = ? AND usado = FALSE AND expira_em >= NOW() ORDER BY codigo_id DESC LIMIT 1");
        $stmt->execute([$user->usuario_id, $codigo]);
        $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$registro) {
            $this->responderJson('erro', 'O link de recuperação é inválido ou já expirou.');
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE USUARIO SET senha = ? WHERE usuario_id = ?")->execute([$hashSenha, $user->usuario_id]);
        $pdo->prepare("UPDATE CODIGO_VERIFICACAO SET usado = TRUE WHERE codigo_id = ?")->execute([$registro['codigo_id']]);

        $this->responderJson('sucesso', 'Senha redefinida com sucesso! Redirecionando...', URL_BASE . '/login');
    }
}