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
        $erro = $_SESSION['erro_login'] ?? null;
        unset($_SESSION['erro_login']);

        require_once __DIR__ . '/../views/login.php'; 
    }

    public function processarLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['erro_login'] = "Por favor, preencha todos os campos.";
            header("Location: " . URL_BASE . "/login");
            exit;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user->senha)) {
            $_SESSION['erro_login'] = "E-mail ou senha incorretos.";
            header("Location: " . URL_BASE . "/login");
            exit;
        }

        $_SESSION['usuario_id'] = $user->usuario_id;
        $_SESSION['usuario_nome'] = $user->nome;
        $_SESSION['usuario_email'] = $user->email;
        $_SESSION['tipo_conta'] = $user->tipo_atual;

        if (empty($user->tipo_atual)) {
            header("Location: " . URL_BASE . "/onboarding");
        } else {
            header("Location: " . URL_BASE . "/home");
        }
        exit;
    }

    public function cadastro()
    {
        $erro = $_SESSION['erro_cadastro'] ?? null;
        unset($_SESSION['erro_cadastro']);

        require_once __DIR__ . '/../views/auth/cadastro.php';
    }

    public function processarCadastro()
    {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $senha_confirmacao = $_POST['senha_confirmacao'] ?? '';

        if (empty($nome) || empty($email) || empty($senha) || empty($senha_confirmacao)) {
            $_SESSION['erro_cadastro'] = "Todos os campos são obrigatórios.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro_cadastro'] = "Insira um formato de e-mail válido.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }

        $dominio = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($dominio, "MX")) {
            $_SESSION['erro_cadastro'] = "Este provedor de e-mail não existe ou é inválido.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }

        if ($senha !== $senha_confirmacao) {
            $_SESSION['erro_cadastro'] = "As senhas não coincidem.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }

        if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[\W_]/', $senha)) {
            $_SESSION['erro_cadastro'] = "A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->findByEmail($email)) {
            $_SESSION['erro_cadastro'] = "Este e-mail já está cadastrado em nosso sistema.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        
        $dadosNovoUsuario = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $hashSenha,
            'tipo_atual' => null
        ];

        $usuarioId = $usuarioModel->create($dadosNovoUsuario);

        if ($usuarioId) {
            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['tipo_conta'] = null;

            // Redireciona corretamente para o onboarding com o caminho completo
            header("Location: " . URL_BASE . "/onboarding");
            exit;
        } else {
            $_SESSION['erro_cadastro'] = "Ocorreu um erro interno ao criar sua conta. Tente novamente.";
            header("Location: " . URL_BASE . "/cadastro");
            exit;
        }
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
}