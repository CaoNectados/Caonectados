<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\SolicitacaoAdocaoRepository;
use app\repositories\HistoricoSolicitacaoRepository;
use app\repositories\ChatRepository;
use app\repositories\AnimalRepository;
use app\repositories\UsuarioRepository;
use app\repositories\ProtetorRepository;
use Exception;

/**
 * RF 08 (UC 15 — Adotante) / RF 09 (UC 18 — Protetor/ONG). Regras de negócio do fluxo de
 * adoção: RN 02 (limite diário de petiscos), RN 07 (justificativa obrigatória na recusa +
 * animal vira 'adotado' na aprovação), RN 08 (cancelamento em massa dos concorrentes), RN 09
 * (abertura de chat), RN 11 (maioridade) e RN 06 (notificação pra parte contrária em toda
 * mudança de status).
 */
class SolicitacaoAdocaoService
{
    private const LIMITE_PETISCOS_DIARIOS = 10;

    private const MOTIVO_DEVOLUCAO = 'abandono';
    private const PESO_DEVOLUCAO = 'media';

    private SolicitacaoAdocaoRepository $solicitacaoRepo;
    private HistoricoSolicitacaoRepository $historicoRepo;
    private NotificacaoService $notificacaoService;
    private DenunciaService $denunciaService;
    private ChatRepository $chatRepo;
    private AnimalRepository $animalRepo;
    private UsuarioRepository $usuarioRepo;
    private ProtetorRepository $protetorRepo;

    public function __construct()
    {
        $this->solicitacaoRepo = new SolicitacaoAdocaoRepository();
        $this->historicoRepo = new HistoricoSolicitacaoRepository();
        $this->notificacaoService = new NotificacaoService();
        $this->denunciaService = new DenunciaService();
        $this->chatRepo = new ChatRepository();
        $this->animalRepo = new AnimalRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->protetorRepo = new ProtetorRepository();
    }

    // Usado por: SolicitacaoAdocaoController::criar() — RF 08 / UC 15, botão "Dar Petisco!" do Feed
    public function solicitarAdocao(int $adotanteId, int $usuarioId, int $animalId): int
    {
        $usuario = $this->usuarioRepo->buscarPorId($usuarioId);
        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }
        // RN 11: menor de 18 não pode solicitar adoção.
        ValidationService::validarMaioridade((string) ($usuario['dt_nasc'] ?? ''));

        // RN 02: no máximo 10 manifestações de interesse por dia. Contado a partir das
        // próprias solicitações (data_solicitacao) em vez de um contador decrescente à parte —
        // não exige job de reset à meia-noite, o "hoje" vem direto do relógio do banco.
        if ($this->solicitacaoRepo->contarSolicitacoesHoje($adotanteId) >= self::LIMITE_PETISCOS_DIARIOS) {
            throw new Exception('Você já usou todos os seus ' . self::LIMITE_PETISCOS_DIARIOS . ' petiscos de hoje. Volte amanhã para demonstrar mais interesse!');
        }

        $animal = $this->animalRepo->buscarPorId($animalId);
        if (!$animal || $animal->getStatus() !== 'disponivel') {
            throw new Exception('Este animal não está mais disponível para adoção.');
        }

        // RN 17 (mesma regra do Feed/Pesquisa): não deixa duplicar solicitação ativa pro mesmo animal.
        if ($this->solicitacaoRepo->existeSolicitacaoAtivaPara($adotanteId, $animalId)) {
            throw new Exception('Você já demonstrou interesse neste animal.');
        }

        $solicitacaoId = $this->solicitacaoRepo->criar($adotanteId, $animalId);
        $this->historicoRepo->registrar($solicitacaoId, $usuarioId, null, 'pendente');

        $protetorUsuarioId = $this->protetorRepo->buscarUsuarioIdPorProtetorId($animal->getProtetorId());
        if ($protetorUsuarioId) {
            $nomeAdotante = $usuario['nome'] ?? 'Um adotante';
            $this->notificacaoService->notificarNovaSolicitacao($protetorUsuarioId, $nomeAdotante, $animal->getNome(), $solicitacaoId);
        }

        return $solicitacaoId;
    }

    // Usado por: SolicitacaoAdocaoController::minhas() — RF 08 / UC 15
    public function listarMinhasSolicitacoes(int $adotanteId): array
    {
        return $this->solicitacaoRepo->listarPorAdotante($adotanteId);
    }

    // Usado por: SolicitacaoAdocaoController::cancelar() — RF 08 / UC 15
    public function cancelarSolicitacao(int $solicitacaoId, int $adotanteUsuarioIdAtual): void
    {
        $solicitacao = $this->obterOuFalhar($solicitacaoId);

        if ((int) $solicitacao['adotante_usuario_id'] !== $adotanteUsuarioIdAtual) {
            throw new Exception('Você não tem permissão para cancelar esta solicitação.');
        }

        $statusAtual = $solicitacao['status_solicitacao'];
        if (!in_array($statusAtual, ['pendente', 'em_analise'], true)) {
            throw new Exception('Só é possível cancelar solicitações pendentes ou em análise.');
        }

        $this->solicitacaoRepo->atualizarStatus($solicitacaoId, 'cancelada', null, true);
        $this->historicoRepo->registrar($solicitacaoId, $adotanteUsuarioIdAtual, $statusAtual, 'cancelada');

        $protetorUsuarioId = $this->protetorRepo->buscarUsuarioIdPorProtetorId((int) $solicitacao['protetor_id']);
        if ($protetorUsuarioId) {
            $this->notificacaoService->notificarSolicitacaoCanceladaPeloAdotante($protetorUsuarioId, $solicitacao['animal_nome'], $solicitacaoId);
        }
    }

    // Usado por: SolicitacaoAdocaoController::painel() — RF 09 / UC 18
    public function listarRecebidas(int $protetorId, string $aba): array
    {
        return $this->solicitacaoRepo->listarRecebidasPorProtetor($protetorId, $aba);
    }

    // Usado por: SolicitacaoAdocaoController::detalhes() — RF 09 / UC 18
    public function obterDetalhesParaProtetor(int $solicitacaoId, int $protetorIdAtual): array
    {
        $solicitacao = $this->obterOuFalhar($solicitacaoId);
        $this->validarPosseProtetor($solicitacao, $protetorIdAtual);

        return $solicitacao;
    }

    // Usado por: SolicitacaoAdocaoController::colocarEmAnalise() — UC 18.3
    public function colocarEmAnalise(int $solicitacaoId, int $protetorIdAtual, int $usuarioResponsavelId): void
    {
        $solicitacao = $this->obterOuFalhar($solicitacaoId);
        $this->validarPosseProtetor($solicitacao, $protetorIdAtual);

        if ($solicitacao['status_solicitacao'] !== 'pendente') {
            throw new Exception('Só é possível colocar em análise solicitações pendentes.');
        }

        $this->solicitacaoRepo->atualizarStatus($solicitacaoId, 'em_analise');
        $this->historicoRepo->registrar($solicitacaoId, $usuarioResponsavelId, 'pendente', 'em_analise');

        $this->notificacaoService->notificarSolicitacaoEmAnalise((int) $solicitacao['adotante_usuario_id'], $solicitacao['animal_nome'], $solicitacaoId);
    }

    // Usado por: SolicitacaoAdocaoController::recusar() — UC 18.1 / RN 07 (justificativa obrigatória)
    public function recusar(int $solicitacaoId, int $protetorIdAtual, int $usuarioResponsavelId, string $justificativa): void
    {
        $justificativa = trim($justificativa);
        if ($justificativa === '') {
            throw new Exception('É obrigatório informar o motivo da recusa.');
        }

        $solicitacao = $this->obterOuFalhar($solicitacaoId);
        $this->validarPosseProtetor($solicitacao, $protetorIdAtual);

        $statusAtual = $solicitacao['status_solicitacao'];
        if (!in_array($statusAtual, ['pendente', 'em_analise'], true)) {
            throw new Exception('Esta solicitação já foi finalizada.');
        }

        $this->solicitacaoRepo->atualizarStatus($solicitacaoId, 'reprovada', $justificativa, true);
        $this->historicoRepo->registrar($solicitacaoId, $usuarioResponsavelId, $statusAtual, 'reprovada');

        $this->notificacaoService->notificarSolicitacaoRecusada((int) $solicitacao['adotante_usuario_id'], $solicitacao['animal_nome'], $justificativa, $solicitacaoId);
    }

    /**
     * UC 18.2 / "Dar a Patinha!". Transação única: aprova esta solicitação, marca o animal como
     * adotado (RN 07), cancela em massa as demais pendentes/em_analise do mesmo animal (RN 08,
     * notificando cada adotante afetado) e abre o chat da solicitação aprovada (RN 09).
     */
    // Usado por: SolicitacaoAdocaoController::aprovar()
    public function aprovar(int $solicitacaoId, int $protetorIdAtual, int $usuarioResponsavelId): void
    {
        $solicitacao = $this->obterOuFalhar($solicitacaoId);
        $this->validarPosseProtetor($solicitacao, $protetorIdAtual);

        $statusAtual = $solicitacao['status_solicitacao'];
        if (!in_array($statusAtual, ['pendente', 'em_analise'], true)) {
            throw new Exception('Esta solicitação já foi finalizada.');
        }

        $animalId = (int) $solicitacao['animal_id'];
        $outrasPendentes = $this->solicitacaoRepo->listarOutrasPendentesParaAnimal($animalId, $solicitacaoId);

        $db = ConnectionFactory::getConnection();
        $db->beginTransaction();

        try {
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, 'aprovada', null, true);
            $this->historicoRepo->registrar($solicitacaoId, $usuarioResponsavelId, $statusAtual, 'aprovada');

            $this->animalRepo->alterarStatus($animalId, 'adotado');

            foreach ($outrasPendentes as $outra) {
                $outraId = (int) $outra['solicitacao_id'];
                $this->solicitacaoRepo->atualizarStatus($outraId, 'cancelada', null, true);
                $this->historicoRepo->registrar($outraId, $usuarioResponsavelId, $outra['status_solicitacao'], 'cancelada');
                $this->notificacaoService->notificarSolicitacaoCanceladaAutomaticamente((int) $outra['adotante_usuario_id'], $solicitacao['animal_nome'], $outraId);
            }

            $this->chatRepo->criarParaSolicitacao($solicitacaoId);

            $this->notificacaoService->notificarSolicitacaoAprovada((int) $solicitacao['adotante_usuario_id'], $solicitacao['animal_nome'], $solicitacaoId);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * RN 12/14 (devolução gera inadimplência automática no Adotante — reflexo de RF 16) e
     * RN 10 (chat encerrado quando a adoção é desfeita). O animal volta a existir como
     * 'disponivel' (RN 13 já cobre esse valor, sem precisar de um status novo tipo
     * "devolvido"); a SOLICITACAO_ADOCAO permanece 'aprovada' — a DENUNCIA criada por
     * DenunciaService::aplicarSancaoDireta(), com solicitacao_id vinculado (RN 21), é o
     * registro de auditoria de que essa adoção específica foi desfeita depois.
     */
    // Usado por: SolicitacaoAdocaoController::devolver()
    public function registrarDevolucao(int $solicitacaoId, int $protetorIdAtual, int $usuarioResponsavelId): void
    {
        $solicitacao = $this->obterOuFalhar($solicitacaoId);
        $this->validarPosseProtetor($solicitacao, $protetorIdAtual);

        if ($solicitacao['status_solicitacao'] !== 'aprovada') {
            throw new Exception('Só é possível registrar devolução de uma adoção aprovada.');
        }

        $animalId = (int) $solicitacao['animal_id'];
        $adotanteUsuarioId = (int) $solicitacao['adotante_usuario_id'];

        $db = ConnectionFactory::getConnection();
        $db->beginTransaction();

        try {
            $this->animalRepo->alterarStatus($animalId, 'disponivel');

            $chat = $this->chatRepo->buscarPorSolicitacaoId($solicitacaoId);
            if ($chat && $chat['status'] === 'ativo') {
                $this->chatRepo->atualizarStatus((int) $chat['chat_id'], 'encerrado');
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Fora da transação do animal/chat: aplicarSancaoDireta() já gerencia sua própria
        // transação (DENUNCIA + ADVERTENCIA + bloqueio), igual DenunciaService::aprovar().
        $this->denunciaService->aplicarSancaoDireta(
            $usuarioResponsavelId,
            $adotanteUsuarioId,
            'adotante',
            self::MOTIVO_DEVOLUCAO,
            "Devolução do animal {$solicitacao['animal_nome']} (solicitação #{$solicitacaoId}) registrada pelo protetor.",
            self::PESO_DEVOLUCAO,
            true,
            $solicitacaoId
        );
    }

    // Usado por: cancelarSolicitacao(), colocarEmAnalise(), recusar(), aprovar(), obterDetalhesParaProtetor()
    private function obterOuFalhar(int $solicitacaoId): array
    {
        $solicitacao = $this->solicitacaoRepo->buscarDetalhado($solicitacaoId);
        if (!$solicitacao) {
            throw new Exception('Solicitação não encontrada.');
        }
        return $solicitacao;
    }

    // Usado por: colocarEmAnalise(), recusar(), aprovar(), obterDetalhesParaProtetor() — só o
    // protetor dono do animal (mesmo padrão de posse já usado em AnimalController) pode triar
    private function validarPosseProtetor(array $solicitacao, int $protetorIdAtual): void
    {
        if ((int) $solicitacao['protetor_id'] !== $protetorIdAtual) {
            throw new Exception('Você não tem permissão para gerenciar esta solicitação.');
        }
    }
}
