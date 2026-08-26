<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\DenunciaRepository;
use app\repositories\AdvertenciaRepository;
use app\repositories\UsuarioRepository;
use app\repositories\ProtetorRepository;
use app\repositories\AdotanteRepository;
use Exception;

/**
 * RF 18 (UC 05 — abrir denúncia) / RF 21 (UC 12 — moderar) / RF 19 (RN 14/15 — sanção). Aprovar
 * uma denúncia é o gatilho que cria a ADVERTENCIA e, opcionalmente, bloqueia a conta do
 * denunciado — tudo numa única transação.
 */
class DenunciaService
{
    private const MOTIVOS_PERMITIDOS = ['maus_tratos', 'abandono', 'fraude', 'assedio', 'outro'];
    private const PESOS_PERMITIDOS = ['leve', 'media', 'grave'];

    private DenunciaRepository $denunciaRepo;
    private AdvertenciaRepository $advertenciaRepo;
    private UsuarioRepository $usuarioRepo;
    private ProtetorRepository $protetorRepo;
    private AdotanteRepository $adotanteRepo;
    private NotificacaoService $notificacaoService;

    public function __construct()
    {
        $this->denunciaRepo = new DenunciaRepository();
        $this->advertenciaRepo = new AdvertenciaRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->protetorRepo = new ProtetorRepository();
        $this->adotanteRepo = new AdotanteRepository();
        $this->notificacaoService = new NotificacaoService();
    }

    // Usado por: DenunciaController::criar() (RF 18 / UC 05)
    public function criar(int $denuncianteId, int $denunciadoId, string $motivo, string $descricao, ?int $solicitacaoId, ?int $chatId): int
    {
        if ($denuncianteId === $denunciadoId) {
            throw new Exception('Você não pode denunciar a si mesmo.');
        }

        if (!in_array($motivo, self::MOTIVOS_PERMITIDOS, true)) {
            throw new Exception('Selecione um motivo válido.');
        }

        $descricao = trim($descricao);
        if ($descricao === '') {
            throw new Exception('Descreva o que aconteceu antes de enviar.');
        }

        if (!$this->usuarioRepo->buscarPorId($denunciadoId)) {
            throw new Exception('Usuário denunciado não encontrado.');
        }

        $perfilDenunciado = $this->resolverPerfilDenunciado($denunciadoId);

        $denunciaId = $this->denunciaRepo->criar($denuncianteId, $denunciadoId, $perfilDenunciado, $motivo, $descricao, $solicitacaoId, $chatId);

        return $denunciaId;
    }

    // Usado por: DenunciaController::minhas() (UC 05.2)
    public function listarMinhas(int $usuarioId): array
    {
        return $this->denunciaRepo->listarPorDenunciante($usuarioId);
    }

    // Usado por: admin\DenunciaController — abas do painel (UC 12)
    public function listarParaModeracao(string $aba): array
    {
        return match ($aba) {
            'em_analise' => $this->denunciaRepo->listarPorStatus('em_analise'),
            'resolvidas' => array_merge(
                $this->denunciaRepo->listarPorStatus('aprovada'),
                $this->denunciaRepo->listarPorStatus('reprovada'),
                $this->denunciaRepo->listarPorStatus('arquivada')
            ),
            default => $this->denunciaRepo->listarPorStatus('aberta'),
        };
    }

    // Usado por: admin\DenunciaController — detalhe da denúncia antes de decidir
    public function obterDetalhes(int $denunciaId): array
    {
        $denuncia = $this->denunciaRepo->buscarPorId($denunciaId);
        if (!$denuncia) {
            throw new Exception('Denúncia não encontrada.');
        }
        return $denuncia;
    }

    // Usado por: admin\DenunciaController::colocarEmAnalise() (UC 12)
    public function colocarEmAnalise(int $denunciaId): void
    {
        $denuncia = $this->obterDetalhes($denunciaId);
        if ($denuncia['status_denuncia'] !== 'aberta') {
            throw new Exception('Só é possível colocar em análise denúncias abertas.');
        }

        $this->denunciaRepo->atualizarStatus($denunciaId, 'em_analise', 'colocar_em_analise');
        $this->notificacaoService->notificarDenunciaStatusAlterado((int) $denuncia['denunciante_id'], 'em_analise', $denunciaId);
    }

    // Usado por: admin\DenunciaController::reprovar() (UC 12 — reprovar/arquivar)
    public function reprovar(int $denunciaId): void
    {
        $denuncia = $this->obterDetalhes($denunciaId);
        if (!in_array($denuncia['status_denuncia'], ['aberta', 'em_analise'], true)) {
            throw new Exception('Esta denúncia já foi finalizada.');
        }

        $this->denunciaRepo->atualizarStatus($denunciaId, 'reprovada', 'reprovar');
        $this->notificacaoService->notificarDenunciaStatusAlterado((int) $denuncia['denunciante_id'], 'reprovada', $denunciaId);
    }

    /**
     * UC 12 — aprovar. Transação única: aprova a denúncia, cria a ADVERTENCIA (RF 19) e,
     * se $bloquearConta, aplica a sanção de fato (status_conta = 'bloqueado' — RN 14/15),
     * que passa a ser barrada em tempo real pelas ações críticas (RF 16, ver
     * Controller::exigirContaNaoBloqueada()).
     */
    // Usado por: admin\DenunciaController::aprovar()
    public function aprovar(int $denunciaId, string $pesoStatus, bool $bloquearConta, ?string $dataFim): void
    {
        $denuncia = $this->obterDetalhes($denunciaId);
        if (!in_array($denuncia['status_denuncia'], ['aberta', 'em_analise'], true)) {
            throw new Exception('Esta denúncia já foi finalizada.');
        }

        if (!in_array($pesoStatus, self::PESOS_PERMITIDOS, true)) {
            throw new Exception('Selecione um peso de advertência válido.');
        }

        $denunciadoId = (int) $denuncia['denunciado_id'];

        $db = ConnectionFactory::getConnection();
        $db->beginTransaction();

        try {
            $this->denunciaRepo->atualizarStatus($denunciaId, 'aprovada', 'aprovar');
            $advertenciaId = $this->advertenciaRepo->criar($denunciadoId, $denunciaId, $denuncia['perfil_denunciado'], $pesoStatus, $dataFim);

            if ($bloquearConta) {
                $this->usuarioRepo->atualizarStatusConta($denunciadoId, 'bloqueado');
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->notificacaoService->notificarDenunciaStatusAlterado((int) $denuncia['denunciante_id'], 'aprovada', $denunciaId);
        $this->notificacaoService->notificarAdvertenciaAplicada($denunciadoId, $pesoStatus, $advertenciaId, $bloquearConta);
    }

    /**
     * Sanção que nasce já decidida, sem passar pela fila normal de moderação — usada por
     * fluxos automáticos/administrativos que não são "alguém denunciou alguém" no sentido
     * usual: devolução de animal (RN 12/14, SolicitacaoAdocaoService::registrarDevolucao()) e
     * admin classificando um protetor como inadimplente (RN 15, UsuarioAdminService).
     *
     * Cria a DENUNCIA já 'aprovada' de propósito: reaproveita 100% da mesma tabela/fluxo de
     * ADVERTENCIA + bloqueio + notificação de aprovar(), então RN 16 (direito de contestação)
     * funciona igual pra esses bloqueios também — nenhum caminho de bloqueio no sistema fica
     * de fora do fluxo de contestação por não ter uma ADVERTENCIA associada.
     */
    // Usado por: SolicitacaoAdocaoService::registrarDevolucao() e UsuarioAdminService::classificarProtetorInadimplente()
    public function aplicarSancaoDireta(int $denuncianteId, int $denunciadoId, string $perfilDenunciado, string $motivo, string $descricao, string $pesoStatus, bool $bloquearConta, ?int $solicitacaoId = null): int
    {
        if (!in_array($motivo, self::MOTIVOS_PERMITIDOS, true)) {
            throw new Exception('Motivo inválido para a sanção.');
        }
        if (!in_array($pesoStatus, self::PESOS_PERMITIDOS, true)) {
            throw new Exception('Peso de advertência inválido.');
        }

        $db = ConnectionFactory::getConnection();
        $db->beginTransaction();

        try {
            $denunciaId = $this->denunciaRepo->criar($denuncianteId, $denunciadoId, $perfilDenunciado, $motivo, $descricao, $solicitacaoId, null);
            $this->denunciaRepo->atualizarStatus($denunciaId, 'aprovada', 'aprovar');

            $advertenciaId = $this->advertenciaRepo->criar($denunciadoId, $denunciaId, $perfilDenunciado, $pesoStatus, null);

            if ($bloquearConta) {
                $this->usuarioRepo->atualizarStatusConta($denunciadoId, 'bloqueado');
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->notificacaoService->notificarAdvertenciaAplicada($denunciadoId, $pesoStatus, $advertenciaId, $bloquearConta);

        return $advertenciaId;
    }

    // Usado por: criar() — infere o perfil do denunciado a partir dos cadastros existentes
    private function resolverPerfilDenunciado(int $usuarioId): string
    {
        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
        if ($protetor) {
            return ($protetor['tipo_documento'] ?? '') === 'cnpj' ? 'ong' : 'protetor';
        }

        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        if ($adotante) {
            return 'adotante';
        }

        return 'usuario';
    }
}
