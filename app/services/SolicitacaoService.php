<?php

namespace app\services;

use app\repositories\ProtetorRepository;
use Exception;

class SolicitacaoService
{
    private ProtetorRepository $protetorRepository;
    private NotificacaoService $notificacaoService;

    public function __construct(?ProtetorRepository $protetorRepository = null, ?NotificacaoService $notificacaoService = null)
    {
        $this->protetorRepository = $protetorRepository ?? new ProtetorRepository();
        $this->notificacaoService = $notificacaoService ?? new NotificacaoService();
    }

    // Usado por: SolicitacaoProtetorController::index (listagem de solicitações de protetor)
    public function listarSolicitacoes(string $status = 'pendentes', string $busca = ''): array
    {
        $statusPermitidos = ['pendentes', 'aprovados', 'recusados'];
        if (!in_array($status, $statusPermitidos, true)) {
            $status = 'pendentes';
        }

        $lista = $this->protetorRepository->listarSolicitacoes($status, trim($busca));
        return array_map([$this, 'adicionarStatusFormatado'], $lista);
    }

    // Usado por: SolicitacaoProtetorController (detalhes da solicitação)
    public function obterDetalhesSolicitacao(int $protetorId): ?array
    {
        if ($protetorId <= 0) {
            return null;
        }

        $detalhes = $this->protetorRepository->buscarDetalhesSolicitacao($protetorId);
        if (!$detalhes) {
            return null;
        }

        return $this->adicionarStatusFormatado($detalhes);
    }

    // Usado por: SolicitacaoProtetorController::aprovar
    public function aprovarSolicitacao(int $protetorId): bool
    {
        if ($protetorId <= 0) {
            return false;
        }

        $solicitacao = $this->protetorRepository->buscarDetalhesSolicitacao($protetorId);
        if (!$solicitacao) {
            return false;
        }

        $sucesso = $this->protetorRepository->aprovarSolicitacao($protetorId);

        if ($sucesso) {
            $nome = $solicitacao['nome_fantasia'] ?: $solicitacao['usuario_nome'];

            // RF 11 / RN 01 / RN 06: cadastro de ONG/Protetor aprovado avisa o usuário dentro do app.
            $this->notificacaoService->notificarCadastroAprovado((int) $solicitacao['usuario_id'], $nome, $protetorId);

            if (!empty($solicitacao['usuario_email'])) {
                try {
                    MailService::enviarNotificacaoAprovacao($solicitacao['usuario_email'], $nome);
                } catch (Exception $e) {
                    error_log("Erro ao enviar e-mail de aprovação: " . $e->getMessage());
                }
            }
        }

        return $sucesso;
    }

    // Usado por: SolicitacaoProtetorController::recusar
    public function recusarSolicitacao(int $protetorId, string $motivo = ''): bool
    {
        if ($protetorId <= 0) {
            return false;
        }

        $solicitacao = $this->protetorRepository->buscarDetalhesSolicitacao($protetorId);
        if (!$solicitacao) {
            return false;
        }

        $sucesso = $this->protetorRepository->recusarSolicitacao($protetorId);

        if ($sucesso) {
            $nome = $solicitacao['nome_fantasia'] ?: $solicitacao['usuario_nome'];

            // RF 11 / RN 01 / RN 06: cadastro de ONG/Protetor recusado avisa o usuário dentro do app.
            $this->notificacaoService->notificarCadastroRecusado((int) $solicitacao['usuario_id'], $nome, $motivo, $protetorId);

            if (!empty($solicitacao['usuario_email'])) {
                $_SESSION['motivo_recusa_protetor_' . $protetorId] = $motivo;
                try {
                    MailService::enviarNotificacaoRecusa($solicitacao['usuario_email'], $nome, $motivo);
                } catch (Exception $e) {
                    error_log("Erro ao enviar e-mail de recusa: " . $e->getMessage());
                }
            }
        }

        return $sucesso;
    }

    // Usado por: listarSolicitacoes(), obterDetalhesSolicitacao() (formatação do status exibido)
    private function adicionarStatusFormatado(array $registro): array
    {
        if (!empty($registro['deletado_em'])) {
            $registro['status'] = 'recusado';
        } elseif (!empty($registro['validado'])) {
            $registro['status'] = 'aprovado';
        } else {
            $registro['status'] = 'pendente';
        }

        return $registro;
    }
}