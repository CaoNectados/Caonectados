<?php

namespace app\services;

use app\repositories\ProtetorRepository;
use Exception;

class ProtetorService
{
    private ProtetorRepository $protetorRepository;

    public function __construct(ProtetorRepository $protetorRepository)
    {
        $this->protetorRepository = $protetorRepository;
    }

    public function listarSolicitacoes(string $status = 'pendentes', string $busca = ''): array
    {
        $statusPermitidos = ['pendentes', 'aprovados', 'recusados'];
        if (!in_array($status, $statusPermitidos, true)) {
            $status = 'pendentes';
        }

        return $this->protetorRepository->listarSolicitacoes($status, trim($busca));
    }

    public function obterDetalhesSolicitacao(int $protetorId): ?array
    {
        if ($protetorId <= 0) {
            return null;
        }

        return $this->protetorRepository->buscarDetalhesSolicitacao($protetorId);
    }

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

        if ($sucesso && !empty($solicitacao['usuario_email'])) {
            try {
                MailService::enviarNotificacaoAprovacao(
                    $solicitacao['usuario_email'],
                    $solicitacao['nome_fantasia'] ?: $solicitacao['usuario_nome']
                );
            } catch (Exception $e) {
                error_log("Erro ao enviar e-mail de aprovação: " . $e->getMessage());
            }
        }

        return $sucesso;
    }

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

        if ($sucesso && !empty($solicitacao['usuario_email'])) {
            try {
                MailService::enviarNotificacaoRecusa(
                    $solicitacao['usuario_email'],
                    $solicitacao['nome_fantasia'] ?: $solicitacao['usuario_nome'],
                    $motivo
                );
            } catch (Exception $e) {
                error_log("Erro ao enviar e-mail de recusa: " . $e->getMessage());
            }
        }

        return $sucesso;
    }
}