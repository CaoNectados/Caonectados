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
        $usuario = $this->usuarioRepo->buscarPorEmail($email);

        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            throw new Exception("E-mail ou senha inválidos.");
        }

        $statusConta = strtolower((string)$usuario->getStatusConta());

        if (in_array($statusConta, ['bloqueado', 'inativo', 'rejeitado', 'bloqueada'], true)) {
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
        $usuario->setTipoAtual($tipoPerfil);
        $usuario->setPerfisAtivos($tipoPerfil);

        return $this->usuarioRepo->salvarNovoUsuario($usuario);
    }

    public function solicitarRecuperacaoSenha(string $email): void
    {
        $usuario = $this->usuarioRepo->buscarPorEmail($email);

        if ($usuario) {
            $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiraEm = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $this->usuarioRepo->salvarCodigoVerificacao($usuario->getUsuarioId(), $codigo, $expiraEm);

            // CORREÇÃO: Captura o resultado do envio de e-mail e lança exceção se falhar
            $enviado = MailService::enviarCodigoVerificacao($usuario->getEmail(), $usuario->getNome(), $codigo, 'redefinir_senha');
            if (!$enviado) {
                throw new Exception("Não foi possível enviar o e-mail de recuperação. Tente novamente mais tarde.");
            }
        }
    }

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

    public function validarRateLimitReenvioCodigo(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $agora = time();
        $limiteSegundos = 60;
        $limiteTentativas = 3;

        $_SESSION['reenvio_count'] = $_SESSION['reenvio_count'] ?? 0;
        $_SESSION['last_reenvio'] = $_SESSION['last_reenvio'] ?? 0;

        if ($_SESSION['reenvio_count'] >= $limiteTentativas && ($agora - $_SESSION['last_reenvio']) < 300) {
            throw new Exception("Muitas tentativas. Aguarde 5 minutos antes de solicitar um novo código.");
        }

        if (($agora - $_SESSION['last_reenvio']) < $limiteSegundos) {
            throw new Exception("Aguarde um momento antes de solicitar um novo código.");
        }

        if (($agora - $_SESSION['last_reenvio']) >= 300) {
            $_SESSION['reenvio_count'] = 0;
        }

        $_SESSION['last_reenvio'] = $agora;
        $_SESSION['reenvio_count']++;
    }

    public function iniciarSessao(Usuario $usuario): void
    {
       if (in_array($usuario->getTipoAtual(), ['ong', 'protetor'])) {
            $protetorRepo = new \app\repositories\ProtetorRepository();
            $protetor = $protetorRepo->buscarPorUsuarioId($usuario->getUsuarioId());
            
            if ($protetor) {
                // Suporta array associativo ou objeto
                $isValid = is_array($protetor) 
                    ? ($protetor['validado'] ?? false) 
                    : (method_exists($protetor, 'getValidado') ? $protetor->getValidado() : false);

                $_SESSION['validado'] = (bool)$isValid;
            } else {
                $_SESSION['validado'] = false;
            }
        } else {
            $_SESSION['validado'] = true;
        }

        session_regenerate_id(true);

        $tipoAtual = strtolower((string)($usuario->getTipoAtual() ?? 'usuario'));
        $perfisAtivosStr = strtolower((string)($usuario->getPerfisAtivos() ?? 'usuario'));
        $statusConta = strtolower((string)($usuario->getStatusConta() ?? 'pendente'));
        $nomeUsuario = $usuario->getNome() ?? explode('@', $usuario->getEmail())[0];

        $_SESSION['usuario_id']    = $usuario->getUsuarioId();
        $_SESSION['usuario_email'] = $usuario->getEmail();
        $_SESSION['usuario_nome']  = $nomeUsuario;

        // Mantemos 'tipo_perfil' por compatibilidade com suas views antigas (header/menu)
        $_SESSION['tipo_perfil']   = $tipoAtual;

        // Nova variável contendo um ARRAY com todos os papéis que o usuário já assumiu (Ex: ['usuario', 'tutor', 'protetor'])
        $_SESSION['perfis_ativos'] = array_map('trim', explode(',', $perfisAtivosStr));

        $_SESSION['status_conta']  = $statusConta;
    }
}
