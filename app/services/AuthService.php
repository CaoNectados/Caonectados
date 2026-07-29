<?php

namespace app\services;

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

    public function autenticar(string $email, string $senha): Usuario
    {
        $usuario = $this->usuarioRepo->buscarPorEmail($email); // Removido o $pdo daqui

        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            throw new Exception("E-mail ou senha inválidos.");
        }

        $statusConta = strtolower((string)$usuario->getStatusConta());

        if (in_array($statusConta, ['bloqueado', 'inativo', 'rejeitado', 'bloqueada'])) {
            throw new Exception("Sua conta está inativa ou bloqueada. Entre em contato com o suporte.");
        }

        return $usuario;
    }

    public function registrar(string $email, string $senha, string $tipoPerfil = 'usuario'): int
    {
        if ($this->usuarioRepo->buscarPorEmail($email)) {
            throw new Exception("Este e-mail já está em uso.");
        }

        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setSenha(password_hash($senha, PASSWORD_BCRYPT));
        $usuario->setTipoPerfil($tipoPerfil);

        // Retorna o ID inserido
        return $this->usuarioRepo->salvarNovoUsuario($usuario);
    }

    /**
     * Processo de Recuperação de Senha (Esqueci a senha)
     */
    public function solicitarRecuperacaoSenha(string $email): void
    {
        $usuario = $this->usuarioRepo->buscarPorEmail($email);

        if ($usuario) {
            $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiraEm = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $this->usuarioRepo->salvarCodigoVerificacao($usuario->getUsuarioId(), $codigo, $expiraEm);

            MailService::enviarEmailRecuperacao($email, $usuario->getNome() ?? 'Usuário', $codigo);
        }
    }

    /**
     * Valida código e redefine senha
     */
    public function redefinirSenha(string $email, string $codigo, string $novaSenha): void
    {
        $usuario = $this->usuarioRepo->buscarPorEmail($email);

        if (!$usuario) {
            throw new Exception("Usuário não encontrado.");
        }

        $registro = $this->usuarioRepo->buscarCodigoValido($usuario->getUsuarioId(), $codigo);

        if (!$registro) {
            throw new Exception("O link de recuperação é inválido ou já expirou.");
        }

        $hashSenha = password_hash($novaSenha, PASSWORD_BCRYPT);
        $this->usuarioRepo->atualizarSenha($usuario->getUsuarioId(), $hashSenha);
        $this->usuarioRepo->marcarCodigoComoUsado($registro['codigo_id']);
    }

    /**
     * Implementa Rate Limit (Limite de Requisições) para reenvio de código
     */
    public function validarRateLimitReenvioCodigo(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $agora = time();
        $limiteSegundos = 60; // Aguardar 1 minuto entre envios
        $limiteTentativas = 3;

        // Inicializa controles na sessão
        $_SESSION['reenvio_count'] = $_SESSION['reenvio_count'] ?? 0;
        $_SESSION['last_reenvio'] = $_SESSION['last_reenvio'] ?? 0;

        // Se o cara pediu 3 vezes seguidas e ainda está na janela de cooldown
        if ($_SESSION['reenvio_count'] >= $limiteTentativas && ($agora - $_SESSION['last_reenvio']) < 300) {
            throw new Exception("Muitas tentativas. Aguarde 5 minutos antes de solicitar um novo código.");
        }

        if (($agora - $_SESSION['last_reenvio']) < $limiteSegundos) {
            throw new Exception("Aguarde um momento antes de solicitar um novo código.");
        }

        // Se passou do tempo de cooldown de 5 min (300s), reseta o contador
        if (($agora - $_SESSION['last_reenvio']) >= 300) {
            $_SESSION['reenvio_count'] = 0;
        }

        // Registra a nova tentativa
        $_SESSION['last_reenvio'] = $agora;
        $_SESSION['reenvio_count']++;
    }

    public function iniciarSessao(Usuario $usuario): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_regenerate_id(true); // OBRIGATÓRIO PARA PREVENÇÃO DE SESSION FIXATION
        
        $tipoPerfil = strtolower((string)($usuario->getTipoAtual() ?? 'usuario'));
        $statusConta = strtolower((string)($usuario->getStatusConta() ?? 'pendente'));

        $_SESSION['usuario_id']    = $usuario->getUsuarioId();
        $_SESSION['usuario_email'] = $usuario->getEmail();
        $_SESSION['usuario_nome']  = $usuario->getNome();
        $_SESSION['tipo_perfil']   = $tipoPerfil;
        $_SESSION['status_conta']  = $statusConta;
    }
}