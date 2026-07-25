<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\models\Usuario;
use app\repositories\UsuarioRepository;
use Exception;

class AuthService
{
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function registrar(string $email, string $senha): Usuario
    {
        $pdo = ConnectionFactory::getConnection();
        
        if ($this->usuarioRepo->buscarPorEmail($email, $pdo)) {
            throw new Exception("Este e-mail já está em uso.");
        }

        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setSenha(password_hash($senha, PASSWORD_BCRYPT));

        $id = $this->usuarioRepo->salvarNovoUsuario($usuario, $pdo);
        $usuario->setUsuarioId($id);

        return $usuario;
    }

    public function autenticar(string $email, string $senha): Usuario
    {
        $pdo = ConnectionFactory::getConnection();
        $usuario = $this->usuarioRepo->buscarPorEmail($email, $pdo);

        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            throw new Exception("E-mail ou senha inválidos.");
        }

        if ($usuario->getStatusConta() === 'BLOQUEADA') {
            throw new Exception("Sua conta está bloqueada. Entre em contato com o suporte.");
        }

        return $usuario;
    }

    public function iniciarSessao(Usuario $usuario): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Armazena o objeto ou os dados essenciais na sessão
        $_SESSION['usuario_logado'] = (object) [
            'usuario_id' => $usuario->getUsuarioId(),
            'email' => $usuario->getEmail(),
            'tipo_atual' => $usuario->getTipoAtual()
        ];
    }

    public function encerrarSessao(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }
}