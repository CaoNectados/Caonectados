<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\ContestacaoRepository;
use app\repositories\AdvertenciaRepository;
use app\repositories\UsuarioRepository;
use Exception;

/**
 * RF 17 (UC 07 — contestar) / RF 22 (UC 13 — moderar) / RN 16.
 */
class ContestacaoService
{
    private ContestacaoRepository $contestacaoRepo;
    private AdvertenciaRepository $advertenciaRepo;
    private UsuarioRepository $usuarioRepo;
    private UploadService $uploadService;
    private NotificacaoService $notificacaoService;

    public function __construct()
    {
        $this->contestacaoRepo = new ContestacaoRepository();
        $this->advertenciaRepo = new AdvertenciaRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->uploadService = new UploadService();
        $this->notificacaoService = new NotificacaoService();
    }

    // Usado por: ContestacaoController::criar() — advertências que o usuário logado pode contestar
    public function listarAdvertenciasContestaveis(int $usuarioId): array
    {
        return $this->advertenciaRepo->listarAtivasPorUsuario($usuarioId);
    }

    /**
     * RN 16: só a advertência ATIVA e do próprio usuário pode ser contestada, e só uma vez —
     * uma segunda tentativa pra mesma advertência é bloqueada (evita reenvio em loop enquanto
     * o admin não decide).
     */
    // Usado por: ContestacaoController::salvar() (RF 17 / UC 07)
    public function criar(int $advertenciaId, int $usuarioIdAtual, string $justificativa, $arquivoAnexo = null): int
    {
        $advertencia = $this->advertenciaRepo->buscarPorId($advertenciaId);
        if (!$advertencia) {
            throw new Exception('Advertência não encontrada.');
        }

        if ((int) $advertencia['usuario_id'] !== $usuarioIdAtual) {
            throw new Exception('Você não tem permissão para contestar esta advertência.');
        }

        if ($advertencia['status'] !== 'ativa') {
            throw new Exception('Esta advertência não está mais ativa.');
        }

        $jaContestada = array_filter(
            $this->contestacaoRepo->listarPorUsuario($usuarioIdAtual),
            fn(array $c) => (int) $c['advertencia_id'] === $advertenciaId
        );
        if (!empty($jaContestada)) {
            throw new Exception('Você já enviou uma contestação para esta advertência.');
        }

        $justificativa = trim($justificativa);
        if ($justificativa === '') {
            throw new Exception('Escreva a justificativa da contestação antes de enviar.');
        }

        $anexo = null;
        if (!empty($arquivoAnexo)) {
            $anexo = $this->uploadService->salvar($arquivoAnexo, 'contestacao');
        }

        $contestacaoId = $this->contestacaoRepo->criar($advertenciaId, $justificativa, $anexo);

        return $contestacaoId;
    }

    // Usado por: ContestacaoController::minhas() (UC 07.2)
    public function listarMinhas(int $usuarioId): array
    {
        return array_map(
            [ContestacaoRepository::class, 'montarComStatus'],
            $this->contestacaoRepo->listarPorUsuario($usuarioId)
        );
    }

    // Usado por: admin\ContestacaoController — aba pendentes (UC 13)
    public function listarPendentes(): array
    {
        return array_map([ContestacaoRepository::class, 'montarComStatus'], $this->contestacaoRepo->listarPendentes());
    }

    // Usado por: admin\ContestacaoController — aba decididas
    public function listarDecididas(): array
    {
        return array_map([ContestacaoRepository::class, 'montarComStatus'], $this->contestacaoRepo->listarDecididas());
    }

    // Usado por: admin\ContestacaoController — detalhe antes de decidir
    public function obterDetalhes(int $contestacaoId): array
    {
        $contestacao = $this->contestacaoRepo->buscarPorId($contestacaoId);
        if (!$contestacao) {
            throw new Exception('Contestação não encontrada.');
        }
        return ContestacaoRepository::montarComStatus($contestacao);
    }

    /**
     * UC 13. Ao aprovar: encerra a ADVERTENCIA (status = 'encerrada') e só restabelece
     * status_conta = 'ativo' se essa era a ÚLTIMA advertência ativa do usuário — se ele tiver
     * outra sanção ativa em paralelo (denúncia diferente), a conta continua bloqueada por
     * causa dela.
     */
    // Usado por: admin\ContestacaoController::decidir() (RF 22)
    public function decidir(int $contestacaoId, bool $aprovar, string $parecer): void
    {
        $contestacao = $this->obterDetalhes($contestacaoId);
        if ($contestacao['status'] !== 'pendente') {
            throw new Exception('Esta contestação já foi decidida.');
        }

        $parecer = trim($parecer);
        if ($parecer === '') {
            throw new Exception('Registre um parecer antes de decidir.');
        }

        $advertenciaId = (int) $contestacao['advertencia_id'];
        $usuarioId = (int) $contestacao['usuario_id'];

        $db = ConnectionFactory::getConnection();
        $db->beginTransaction();

        try {
            $this->contestacaoRepo->registrarParecer($contestacaoId, $parecer);

            if ($aprovar) {
                $this->advertenciaRepo->atualizarStatus($advertenciaId, 'encerrada');

                $outrasAtivas = array_filter(
                    $this->advertenciaRepo->listarAtivasPorUsuario($usuarioId),
                    fn(array $a) => (int) $a['advertencia_id'] !== $advertenciaId
                );
                if (empty($outrasAtivas)) {
                    $this->usuarioRepo->atualizarStatusConta($usuarioId, 'ativo');
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->notificacaoService->notificarContestacaoDecidida($usuarioId, $aprovar, $contestacaoId);
    }
}
