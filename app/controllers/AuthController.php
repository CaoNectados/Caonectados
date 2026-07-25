<?php

namespace app\controllers;

use app\models\Usuario;

class AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function processarLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $this->redirecionarComErro('erro_login', 'Por favor, preencha todos os campos.', '/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirecionarComErro('erro_login', 'Informe um e-mail válido.', '/login');
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user->senha)) {
            $this->redirecionarComErro('erro_login', 'E-mail ou senha incorretos.', '/login');
        }

        $_SESSION['usuario_id'] = $user->usuario_id;
        $_SESSION['usuario_nome'] = $user->nome;
        $_SESSION['usuario_email'] = $user->email;
        $_SESSION['tipo_conta'] = $user->tipo_perfil ?? $user->tipo_atual ?? null;

        if (empty($user->tipo_perfil) || $user->tipo_perfil === 'usuario') {
            header("Location: " . URL_BASE . "/onboarding");
        } else {
            header("Location: " . URL_BASE . "/home");
        }
        exit;
    }

    public function cadastro()
    {
        require_once __DIR__ . '/../views/auth/cadastro.php';
    }

    public function processarCadastro()
    {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $senha_confirmacao = $_POST['senha_confirmacao'] ?? '';
        $tipoPerfil ='usuario';

        if (empty($nome) || empty($email) || empty($senha) || empty($senha_confirmacao) || empty($tipoPerfil)) {
            $this->redirecionarComErro('erro_cadastro', 'Todos os campos são obrigatórios.', '/cadastro');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirecionarComErro('erro_cadastro', 'Insira um formato de e-mail válido.', '/cadastro');
        }

        if ($senha !== $senha_confirmacao) {
            $this->redirecionarComErro('erro_cadastro', 'As senhas não coincidem.', '/cadastro');
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $this->redirecionarComErro('erro_cadastro', 'A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.', '/cadastro');
        }

    

        $usuarioModel = new Usuario();

        if ($usuarioModel->findByEmail($email)) {
            $this->redirecionarComErro('erro_cadastro', 'Este e-mail já está cadastrado em nosso sistema.', '/cadastro');
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        
        $dadosNovoUsuario = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $hashSenha,
            'tipo_perfil' => $tipoPerfil ?: 'usuario'
        ];

        $usuarioId = $usuarioModel->create($dadosNovoUsuario);

        if ($usuarioId) {
            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['tipo_conta'] = null;

            header("Location: " . URL_BASE . "/onboarding");
            exit;
        }

        $this->redirecionarComErro('erro_cadastro', 'Ocorreu um erro interno ao criar sua conta. Tente novamente.', '/cadastro');
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: " . URL_BASE . "/login");
        exit;
    }

    private function redirecionarComErro(string $chave, string $mensagem, string $rota): void
    {
        $_SESSION[$chave] = $mensagem;
        header('Location: ' . URL_BASE . $rota);
        exit;
    }
}