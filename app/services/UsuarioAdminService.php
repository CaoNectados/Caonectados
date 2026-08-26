<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\models\Usuario;
use app\repositories\UsuarioRepository;
use app\repositories\AdotanteRepository;
use app\repositories\ProtetorRepository;
use app\services\ValidationService;
use Exception;

class UsuarioAdminService
{
    private UsuarioRepository $usuarioRepo;
    private AdotanteRepository $adotanteRepo;
    private ProtetorRepository $protetorRepo;
    private NotificacaoService $notificacaoService;
    private DenunciaService $denunciaService;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->adotanteRepo = new AdotanteRepository();
        $this->protetorRepo = new ProtetorRepository();
        $this->notificacaoService = new NotificacaoService();
        $this->denunciaService = new DenunciaService();
    }

    // Usado por: UsuarioController::index
    public function listarUsuarios(array $filtros): array
    {
        $busca = trim($filtros['busca'] ?? '');
        $status = trim($filtros['status'] ?? '');
        $perfil = trim($filtros['perfil'] ?? '');
        $pagina = max(1, (int)($filtros['pagina'] ?? 1));
        $porPagina = 10;

        $usuarios = $this->usuarioRepo->listarUsuariosAdmin($busca, $status, $perfil, $pagina, $porPagina);
        $total = $this->usuarioRepo->contarUsuariosAdmin($busca, $status, $perfil);

        return [
            'usuarios'     => $usuarios,
            'total'        => $total,
            'paginaAtual'  => $pagina,
            'totalPaginas' => ceil($total / $porPagina)
        ];
    }

    // Usado por: UsuarioController (detalhes do usuário)
    public function obterDetalhesUsuario(int $usuarioId): array
    {
        $usuario = $this->usuarioRepo->buscarPorId($usuarioId);
        if (!$usuario) {
            throw new Exception("Usuário não encontrado.");
        }

        $perfisAtivos = array_map('trim', explode(',', strtolower($usuario['perfis_ativos'] ?? '')));
        $perfis = [];

        // 1. Perfil Adotante
        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        if ($adotante) {
            $perfis[] = [
                'tipo'   => 'adotante',
                'nome'   => 'Adotante',
                'ativo'  => in_array('adotante', $perfisAtivos, true),
                'info'   => 'Cadastrado para adoção'
            ];
        }

        // 2. Perfil ONG / Protetor
        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
        if ($protetor) {
            $tipo = ($protetor['tipo_documento'] === 'cnpj') ? 'ong' : 'protetor';
            $perfis[] = [
                'tipo'   => $tipo,
                'nome'   => ($tipo === 'ong') ? 'ONG' : 'Protetor Independente',
                'ativo'  => in_array($tipo, $perfisAtivos, true),
                'info'   => $protetor['nome_fantasia'] ?? ''
            ];
        }

        // 3. Perfil Administrador
        if ($usuario['tipo_atual'] === 'administrador' || in_array('administrador', $perfisAtivos, true)) {
            $perfis[] = [
                'tipo'   => 'administrador',
                'nome'   => 'Administrador',
                'ativo'  => in_array('administrador', $perfisAtivos, true),
                'info'   => 'Acesso Administrativo'
            ];
        }

        return [
            'usuario' => $usuario,
            'perfis'  => $perfis
        ];
    }

    // Usado por: UsuarioController (ativar/desativar conta de usuário)
    public function alterarStatusUsuario(int $usuarioId, string $acao, int $adminLogadoId): string
    {
        if ($usuarioId === $adminLogadoId && $acao === 'desativar') {
            throw new Exception("Você não pode desativar sua própria conta de administrador.");
        }

        $usuario = $this->usuarioRepo->buscarPorId($usuarioId);
        if (!$usuario) {
            throw new Exception("Usuário não encontrado.");
        }

        $novoStatus = ($acao === 'desativar') ? 'inativo' : 'ativo';

        if ($usuario['status_conta'] === $novoStatus) {
            throw new Exception("O usuário já se encontra com o status " . strtoupper($novoStatus) . ".");
        }

        // Proteção: não permitir desativar o último admin ativo
        if ($novoStatus === 'inativo' && ($usuario['tipo_atual'] === 'administrador' || str_contains($usuario['perfis_ativos'], 'administrador'))) {
            if ($this->usuarioRepo->contarAdminsAtivos() <= 1) {
                throw new Exception("Não é possível desativar este usuário pois ele é o único administrador ativo do sistema.");
            }
        }

        $this->usuarioRepo->atualizarStatusConta($usuarioId, $novoStatus);

        // RF 11 / RN 06: avisa o próprio usuário afetado pelo bloqueio/reativação.
        $this->notificacaoService->notificarStatusContaAlterado($usuarioId, $novoStatus === 'inativo');

        return $novoStatus === 'ativo'
            ? "Usuário reativado com sucesso!"
            : "Usuário desativado com sucesso! O acesso global à plataforma foi bloqueado.";
    }

    // Usado por: UsuarioController (ativar/desativar perfil específico do usuário)
    public function alterarStatusPerfil(int $usuarioId, string $tipoPerfil, string $acao, int $adminLogadoId): string
    {
        $usuario = $this->usuarioRepo->buscarPorId($usuarioId);
        if (!$usuario) {
            throw new Exception("Usuário não encontrado.");
        }

        $tipoPerfil = strtolower(trim($tipoPerfil));
        $perfisAtivos = array_filter(array_map('trim', explode(',', strtolower($usuario['perfis_ativos'] ?? ''))));

        if ($acao === 'desativar') {
            if ($tipoPerfil === 'administrador' && $usuarioId === $adminLogadoId) {
                throw new Exception("Você não pode desativar seu próprio perfil de administrador.");
            }

            if ($tipoPerfil === 'administrador' && $this->usuarioRepo->contarAdminsAtivos() <= 1) {
                throw new Exception("Este é o único perfil de administrador ativo no sistema e não pode ser desativado.");
            }

            if (!in_array($tipoPerfil, $perfisAtivos, true)) {
                throw new Exception("Este perfil já está desativado.");
            }

            $perfisAtivos = array_diff($perfisAtivos, [$tipoPerfil]);

            // Se o perfil desativado era o perfil atual em uso, aponta para outro ativo ou 'usuario'
            $novoTipoAtual = $usuario['tipo_atual'];
            if ($novoTipoAtual === $tipoPerfil) {
                $novoTipoAtual = !empty($perfisAtivos) ? reset($perfisAtivos) : 'usuario';
            }

            $perfisStr = implode(',', $perfisAtivos);
            $this->usuarioRepo->atualizarPerfisAtivos($usuarioId, $perfisStr, $novoTipoAtual);

            // RF 11 / RN 06: avisa o usuário afetado pelo bloqueio do perfil específico.
            $this->notificacaoService->notificarStatusPerfilAlterado($usuarioId, $tipoPerfil, true);

            return "Perfil " . strtoupper($tipoPerfil) . " desativado com sucesso!";
        } else {
            // Ação: Reativar
            if (in_array($tipoPerfil, $perfisAtivos, true)) {
                throw new Exception("Este perfil já se encontra ativo.");
            }

            $perfisAtivos[] = $tipoPerfil;
            $perfisStr = implode(',', array_unique($perfisAtivos));

            $this->usuarioRepo->atualizarPerfisAtivos($usuarioId, $perfisStr, $usuario['tipo_atual']);

            $this->notificacaoService->notificarStatusPerfilAlterado($usuarioId, $tipoPerfil, false);

            return "Perfil " . strtoupper($tipoPerfil) . " reativado com sucesso!";
        }
    }

    /**
     * RN 15: admin classifica um Protetor/ONG como inadimplente em caso de irregularidades
     * recorrentes. Reaproveita DenunciaService::aplicarSancaoDireta() — mesma "cola"
     * denúncia+advertência+bloqueio da moderação de denúncias, só que disparada direto pelo
     * admin (denunciante = o próprio admin logado), garantindo que RN 16 (direito de
     * contestação) valha igual pra esse bloqueio.
     */
    // Usado por: UsuarioController::classificarInadimplente()
    public function classificarProtetorInadimplente(int $usuarioId, string $motivo, int $adminLogadoId): string
    {
        $usuario = $this->usuarioRepo->buscarPorId($usuarioId);
        if (!$usuario) {
            throw new Exception("Usuário não encontrado.");
        }

        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
        if (!$protetor) {
            throw new Exception("Este usuário não possui perfil de Protetor/ONG.");
        }

        $motivo = trim($motivo);
        if ($motivo === '') {
            throw new Exception("Descreva a irregularidade antes de confirmar.");
        }

        $perfilAfetado = ($protetor['tipo_documento'] ?? '') === 'cnpj' ? 'ong' : 'protetor';

        $this->denunciaService->aplicarSancaoDireta(
            $adminLogadoId,
            $usuarioId,
            $perfilAfetado,
            'outro',
            "Classificado como inadimplente pela administração: {$motivo}",
            'grave',
            true
        );

        return "Perfil classificado como inadimplente e bloqueado com sucesso!";
    }

    /**
     * Cria uma nova conta de Administrador já ativa, sem passar pelo cadastro/onboarding
     * público. Único ponto de criação de administrador do sistema — só é alcançável através
     * de UsuarioController, cujo construtor já exige autenticacaoRequired(['administrador']),
     * garantindo a regra de que um admin só pode ser criado por outro admin já logado.
     */
    // Usado por: UsuarioController::criarAdministrador()
    public function criarAdministrador(string $nome, string $email, string $senha, string $confirmarSenha): string
    {
        ValidationService::validarNome($nome);
        ValidationService::validarEmail($email);

        if ($senha !== $confirmarSenha) {
            throw new Exception("As senhas não coincidem.");
        }
        ValidationService::validarForcaSenha($senha);

        if ($this->usuarioRepo->buscarPorEmail($email)) {
            throw new Exception("Este e-mail já está em uso.");
        }

        $usuario = new Usuario();
        $usuario->setNome(trim($nome));
        $usuario->setEmail($email);
        $usuario->setSenha(password_hash($senha, PASSWORD_BCRYPT));
        $usuario->setTipoAtual('administrador');
        $usuario->setPerfisAtivos('administrador');
        $usuario->setStatusConta('ativo');

        $this->usuarioRepo->salvarNovoUsuario($usuario);

        return "Administrador criado com sucesso!";
    }
}
