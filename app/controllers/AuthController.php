<?php

namespace app\controllers;

use app\core\Controller;
use app\models\Usuario;

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

        $_SESSION['usuario_id'] = $user->usuario_id;
        $_SESSION['usuario_email'] = $user->email;
        $_SESSION['tipo_conta'] = $user->tipo_perfil ?? $user->tipo_atual ?? null;

        if (empty($user->tipo_perfil) || $user->tipo_perfil === 'usuario') {
            $this->view('onboarding/selecionar_perfil', [
                'titulo'    => 'Selecionar Perfil',
                'descricao' => 'Escolha o tipo de perfil que deseja criar.',
            ]);
        } else {
            $this->view('home/index', [
                'titulo'    => 'Home',
                'descricao' => 'Plataforma de adoção de animais da tríplice fronteira. '
                             . 'Conectamos pets que precisam de um lar com humanos dispostos a dar muito amor.',
            ]);
        }
        exit;
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

        if (empty($email) || empty($senha) || empty($senha_confirmacao) || empty($tipoPerfil)) {
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
        
        $dadosNovoUsuario = [
            'email'       => $email,
            'senha'       => $hashSenha,
            'tipo_perfil' => $tipoPerfil ?: 'usuario'
        ];

        $usuarioId = $usuarioModel->create($dadosNovoUsuario);

        if ($usuarioId) {
            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['tipo_conta'] = null;

            $this->view('onboarding/selecionar_perfil', [
                'titulo'    => 'Selecionar Perfil',
                'descricao' => 'Escolha o tipo de perfil que deseja criar.',
            ]);
            exit;
        }

        $this->redirecionarComMensagem('erro', 'Ocorreu um erro interno ao criar sua conta. Tente novamente.', '/cadastro');
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        $this->redirect("/login");
    }
}