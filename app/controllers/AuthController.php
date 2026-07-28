<?php

namespace app\controllers;

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
            $this->redirecionarComMensagem('erro', 'Por favor, preencha todos os campos.', '/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirecionarComMensagem('erro', 'Informe um e-mail válido.', '/login');
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user->senha)) {
            $this->redirecionarComMensagem('erro', 'E-mail ou senha incorretos.', '/login');
        }

        // Pega as propriedades do model/array tratadas
        $tipoPerfil = $user->tipo_perfil ?? $user->tipo_atual ?? 'usuario';
        $statusConta = $user->status_conta ?? 'pendente';

        // 1. Bloqueio de contas inativas ou banidas
        if (in_array($statusConta, ['bloqueado', 'inativo', 'rejeitado'])) {
            $this->redirecionarComMensagem('erro', 'Sua conta está inativa ou bloqueada. Entre em contato com o suporte.', '/login');
        }

        // 2. Preenchimento das variáveis de SESSÃO
        $_SESSION['usuario_id']    = $user->usuario_id;
        $_SESSION['usuario_email'] = $user->email;
        $_SESSION['usuario_nome']  = $user->nome;
        $_SESSION['tipo_conta']    = $tipoPerfil;
        $_SESSION['status_conta']  = $statusConta;

        // 3. Redirecionamento por estado do usuário

        // Caso A: Usuário novo (não completou onboarding)
        if (empty($tipoPerfil) || $tipoPerfil === 'usuario') {
            $this->redirect('/onboarding');
        }

        // Caso B: ONG/Protetor ainda aguardando validação do Administrador
        if ($statusConta === 'pendente') {
            $this->redirect('/aguardando-aprovacao');
        }

        // Caso C: Admin logando
        if ($tipoPerfil === 'administrador') {
            $this->redirect('/admin/gerenciar-usuarios');
        }

        // Caso D: ONG ou Protetor recém-aprovado acessando a conta
        if (in_array($tipoPerfil, ['ong', 'protetor']) && $statusConta === 'ativo') {
            // Seta a sessão para disparar o modal de boas-vindas no footer
            $_SESSION['boas_vindas_nome'] = $user->nome;
            $_SESSION['boas_vindas_tipo'] = $tipoPerfil;

            $this->redirect('/home');
        }

        // Caso E: Adotante/Tutor ativo
        $this->redirect('/home');
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
            $this->redirecionarComMensagem('erro', 'Todos os campos são obrigatórios.', '/cadastro');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirecionarComMensagem('erro', 'Insira um formato de e-mail válido.', '/cadastro');
        }

        if ($senha !== $senha_confirmacao) {
            $this->redirecionarComMensagem('erro', 'As senhas não coincidem.', '/cadastro');
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->redirecionarComMensagem('erro', 'A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.', '/cadastro');
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->findByEmail($email)) {
            $this->redirecionarComMensagem('erro', 'Este e-mail já está cadastrado em nosso sistema.', '/cadastro');
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Salva os dados temporariamente na SESSÃO (sem inserir na tabela USUARIO ainda)
        $_SESSION['pendente_cadastro'] = [
            'email'       => $email,
            'senha'       => $hashSenha,
            'tipo_perfil' => $tipoPerfil,
            'codigo'      => $codigo,
            'expira_em'   => $expiraEm
        ];
        $_SESSION['email_pendente_verificacao'] = $email;

        // Dispara o e-mail via PHPMailer
        \app\services\MailService::enviarCodigoVerificacao($email, $email, $codigo);

        $this->redirect('/verificar-email');
        exit;
    }

    public function processarVerificacao()
    {
        $codigoInformado = trim($_POST['codigo'] ?? '');
        $dadosPendentes = $_SESSION['pendente_cadastro'] ?? null;

        if (!$dadosPendentes || empty($codigoInformado)) {
            $this->redirecionarComMensagem('erro', 'Sessão expirada ou código vazio.', '/cadastro');
        }

        // Valida se o código bate e se ainda está no prazo de 15 minutos
        if ($dadosPendentes['codigo'] !== $codigoInformado || strtotime($dadosPendentes['expira_em']) < time()) {
            $this->redirecionarComMensagem('erro', 'Código inválido ou expirado.', '/verificar-email');
        }

        $usuarioModel = new Usuario();
        $dadosNovoUsuario = [
            'email'       => $dadosPendentes['email'],
            'senha'       => $dadosPendentes['senha'],
            'tipo_perfil' => $dadosPendentes['tipo_perfil']
        ];

        $usuarioId = $usuarioModel->create($dadosNovoUsuario);

        if (!$usuarioId) {
            $this->redirecionarComMensagem('erro', 'Erro ao criar conta no banco de dados.', '/cadastro');
        }

        // Limpa os dados temporários da sessão e define o usuário como logado
        unset($_SESSION['pendente_cadastro']);
        unset($_SESSION['email_pendente_verificacao']);

        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_email'] = $dadosNovoUsuario['email'];
        $_SESSION['tipo_conta'] = $dadosNovoUsuario['tipo_perfil'];

        $this->redirecionarComMensagem('sucesso', 'E-mail confirmado com sucesso! Complete seu perfil.', '/onboarding');
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
        $this->view('auth/verificar_email', ['titulo' => 'Verificação de E-mail', 'descricao' => 'Confirme o código.']);
    }

   

    public function reenviarCodigo()
    {
        $dadosPendentes = $_SESSION['pendente_cadastro'] ?? null;
        $emailDestino = $_SESSION['email_pendente_verificacao'] ?? null;

        if (!$dadosPendentes || !$emailDestino) {
            $this->redirect('/cadastro');
            return;
        }

        try {
            // Gera um novo código e um novo tempo de expiração
            $novoCodigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $novoExpiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Atualiza os dados na sessão
            $_SESSION['pendente_cadastro']['codigo'] = $novoCodigo;
            $_SESSION['pendente_cadastro']['expira_em'] = $novoExpiraEm;

            // Dispara o e-mail novamente via MailService
            \app\services\MailService::enviarCodigoVerificacao($emailDestino, $emailDestino, $novoCodigo);

            $this->redirecionarComMensagem('sucesso', 'Um novo código foi enviado para o seu e-mail.', '/verificar-email');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao reenviar código. Tente novamente.', '/verificar-email');
        }
    }

    public function esqueciSenha()
    {
        $this->view('auth/esqueci_senha', [
            'titulo'    => 'Esqueci minha senha',
            'descricao' => 'Recupere o acesso à sua conta.'
        ]);
    }

    public function processarEsqueciSenha()
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirecionarComMensagem('erro', 'Informe um e-mail válido.', '/esqueci-senha');
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

        // Exibe mensagem de sucesso independente de o e-mail existir, para evitar vazamento de dados
        $this->redirecionarComMensagem('sucesso', 'Se o e-mail existir em nossa base, enviaremos um link de recuperação.', '/login');
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
            $this->redirecionarComMensagem('erro', 'Preencha todos os campos.', "/redefinir-senha?email=$email&codigo=$codigo");
        }

        if ($senha !== $senha_confirmacao) {
            $this->redirecionarComMensagem('erro', 'As senhas não coincidem.', "/redefinir-senha?email=$email&codigo=$codigo");
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->redirecionarComMensagem('erro', 'A senha deve ter pelo menos 8 caracteres, letras maiúsculas, minúsculas, números e caractere especial.', "/redefinir-senha?email=$email&codigo=$codigo");
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user) {
            $this->redirecionarComMensagem('erro', 'Usuário não encontrado.', '/login');
        }

        $pdo = \app\database\ConnectionFactory::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM CODIGO_VERIFICACAO WHERE usuario_id = ? AND codigo = ? AND usado = FALSE AND expira_em >= NOW() ORDER BY codigo_id DESC LIMIT 1");
        $stmt->execute([$user->usuario_id, $codigo]);
        $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$registro) {
            $this->redirecionarComMensagem('erro', 'O link de recuperação é inválido ou já expirou.', '/login');
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE USUARIO SET senha = ? WHERE usuario_id = ?")->execute([$hashSenha, $user->usuario_id]);
        $pdo->prepare("UPDATE CODIGO_VERIFICACAO SET usado = TRUE WHERE codigo_id = ?")->execute([$registro['codigo_id']]);

        $this->redirecionarComMensagem('sucesso', 'Senha redefinida com sucesso! Faça login para continuar.', '/login');
    }
}
