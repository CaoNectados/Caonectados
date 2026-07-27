<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\models\Usuario;
use app\repositories\UsuarioRepository;
use Exception;
use PDO;

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

        $statusConta = strtolower((string)$usuario->getStatusConta());

        // Bloqueio de contas restritas
        if (in_array($statusConta, ['bloqueado', 'inativo', 'rejeitado', 'bloqueada'])) {
            throw new Exception("Sua conta está inativa ou bloqueada. Entre em contato com o suporte.");
        }

        return $usuario;
    }

    public function iniciarSessao(Usuario $usuario): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $tipoPerfil = strtolower((string)($usuario->getTipoAtual() ?? 'usuario'));
        $statusConta = strtolower((string)($usuario->getStatusConta() ?? 'pendente'));

        // 1. Define os dados de autenticação na SESSÃO
        $_SESSION['usuario_id']    = $usuario->getUsuarioId();
        $_SESSION['usuario_email'] = $usuario->getEmail();
        $_SESSION['usuario_nome']  = $usuario->getNome();
        $_SESSION['tipo_conta']    = $tipoPerfil;
        $_SESSION['status_conta']  = $statusConta;

        $_SESSION['usuario_logado'] = (object) [
            'usuario_id' => $usuario->getUsuarioId(),
            'email'      => $usuario->getEmail(),
            'tipo_atual' => $tipoPerfil
        ];

        // 2. VERIFICAÇÃO DA NOTIFICAÇÃO DE APROVAÇÃO (ONG / PROTETOR)
        if (in_array($tipoPerfil, ['ong', 'protetor']) && $statusConta === 'ativo') {
            $pdo = ConnectionFactory::getConnection();

            // Busca notificação do sistema não lida para o usuário
            $sql = "SELECT notificacao_id FROM NOTIFICACAO 
                    WHERE usuario_id = :id 
                      AND tipo_notificacao = 'sistema' 
                      AND lida = FALSE 
                    LIMIT 1";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $usuario->getUsuarioId()]);
            $notificacao = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se for o primeiro login após a aprovação, ativa as variáveis da modal
            if ($notificacao) {
                $_SESSION['boas_vindas_nome'] = $usuario->getNome();
                $_SESSION['boas_vindas_tipo'] = $tipoPerfil;

                // Marca como LIDA para nunca mais aparecer nos logins futuros
                $sqlMark = "UPDATE NOTIFICACAO SET lida = TRUE WHERE notificacao_id = :notif_id";
                $stmtMark = $pdo->prepare($sqlMark);
                $stmtMark->execute(['notif_id' => $notificacao['notificacao_id']]);
            }
        }
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