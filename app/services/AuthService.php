<?php
namespace app\services;

use app\models\Usuario;
use app\repositories\UsuarioRepository;
use app\repositories\ProtetorRepository;
use Exception;

class AuthService
{
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    // Usado por: AuthController::login
    public function autenticar(string $email, string $senha): Usuario
    {
        $usuario = $this->usuarioRepo->buscarPorEmail($email);

        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            throw new Exception("E-mail ou senha inválidos.");
        }

        $statusConta = strtolower((string)$usuario->getStatusConta());
        if (in_array($statusConta, ['inativo', 'bloqueado', 'rejeitado', 'bloqueada', 'desativado'], true)) {
            throw new Exception("Esta conta foi desativada pelo administrador. Entre em contato com o suporte.");
        }

        if ($usuario->getDeletadoEm() !== null) {
            throw new Exception("Esta conta foi excluída e não pode ser acessada.");
        }

        return $usuario;
    }

    // Usado por: AuthController::cadastro
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
        $usuario->setStatusConta('ativo');

        return $this->usuarioRepo->salvarNovoUsuario($usuario);
    }

    // Usado por: AuthController (recuperação de senha)
    public function solicitarRecuperacaoSenha(string $email): void
    {
        $usuario = $this->usuarioRepo->buscarPorEmail($email);

        if ($usuario) {
            $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiraEm = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $this->usuarioRepo->salvarCodigoVerificacao($usuario->getUsuarioId(), $codigo, $expiraEm);

            $enviado = MailService::enviarCodigoVerificacao($usuario->getEmail(), $usuario->getNome(), $codigo, 'redefinir_senha');
            if (!$enviado) {
                throw new Exception("Não foi possível enviar o e-mail de recuperação. Tente novamente mais tarde.");
            }
        }
    }

    // Usado por: AuthController (redefinição de senha)
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

    // Usado por: AuthController (reenvio de código de verificação)
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

    // Usado por: AuthController (login, cadastro e verificação de código)
    public function iniciarSessao(Usuario $usuario): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $protetorRepo = new ProtetorRepository();
        $protetor = $protetorRepo->buscarPorUsuarioId($usuario->getUsuarioId());

        if ($protetor) {
            $isValid = is_array($protetor) 
                ? ($protetor['validado'] ?? false) 
                : (method_exists($protetor, 'getValidado') ? $protetor->getValidado() : false);

            $_SESSION['validado'] = (bool)$isValid;
            $_SESSION['protetor_id'] = is_array($protetor) ? (int)$protetor['protetor_id'] : $protetor->getProtetorId();
        } else {
            $_SESSION['validado'] = ($usuario->getTipoAtual() === 'administrador');
            $_SESSION['protetor_id'] = null;
        }

        session_regenerate_id(true);

        $tipoAtual = strtolower((string)($usuario->getTipoAtual() ?? 'usuario'));
        $perfisAtivosStr = strtolower((string)($usuario->getPerfisAtivos() ?? 'usuario'));
        $statusConta = strtolower((string)($usuario->getStatusConta() ?? 'ativo'));
        $nomeUsuario = $usuario->getNome() ?? explode('@', $usuario->getEmail())[0];
        $perfisLista = array_filter(array_map('trim', explode(',', $perfisAtivosStr)));

        $_SESSION['usuario_id']    = $usuario->getUsuarioId();
        $_SESSION['usuario_email'] = $usuario->getEmail();
        $_SESSION['usuario_nome']  = $nomeUsuario;
        $_SESSION['tipo_perfil']   = $tipoAtual;
        $_SESSION['perfis_ativos'] = $perfisLista;
        $_SESSION['status_conta']  = $statusConta;

        $_SESSION['usuario'] = [
            'id'       => $usuario->getUsuarioId(),
            'nome'     => $nomeUsuario,
            'email'    => $usuario->getEmail(),
            'is_admin' => ($tipoAtual === 'administrador')
        ];

        $perfisFormatados = [];
        foreach ($perfisLista as $papel) {
            if (!empty($papel)) {
                $perfisFormatados[] = [
                    'id'   => $usuario->getUsuarioId(),
                    'tipo' => $papel
                ];
            }
        }

        $_SESSION['perfis'] = $perfisFormatados;
        $_SESSION['perfil_ativo'] = [
            'id'   => $usuario->getUsuarioId(),
            'tipo' => $tipoAtual
        ];
    }

    // Usado por: (não referenciado atualmente)
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset(
            $_SESSION['usuario'], 
            $_SESSION['perfis'], 
            $_SESSION['perfil_ativo'],
            $_SESSION['usuario_id'],
            $_SESSION['usuario_email'],
            $_SESSION['usuario_nome'],
            $_SESSION['tipo_perfil'],
            $_SESSION['perfis_ativos'],
            $_SESSION['status_conta'],
            $_SESSION['validado'],
            $_SESSION['protetor_id']
        );
        session_destroy();
    }
}