<?php

namespace app\services;

use app\repositories\ProtetorRepository;
use app\database\ConnectionFactory;
use Exception;

class SolicitacaoService
{
    private ProtetorRepository $protetorRepository;

    public function __construct(?ProtetorRepository $protetorRepository = null)
    {
        $this->protetorRepository = $protetorRepository ?? new ProtetorRepository();
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

        $db = ConnectionFactory::getConnection();
        $db->beginTransaction();
        try {
            $lock = $db->prepare('SELECT * FROM PROTETOR WHERE protetor_id = ? FOR UPDATE');
            $lock->execute([$protetorId]);
            $atual = $lock->fetch(\PDO::FETCH_ASSOC);
            if (!$atual || !empty($atual['deletado_em']) || !empty($atual['validado'])) throw new Exception('Solicitação não está pendente.');
            $lock = $db->prepare('SELECT * FROM USUARIO WHERE usuario_id = ? FOR UPDATE');
            $lock->execute([$atual['usuario_id']]);
            $usuario = $lock->fetch(\PDO::FETCH_ASSOC);
            if (!$usuario || $usuario['status_conta'] !== 'ativo' || !empty($usuario['deletado_em'])) throw new Exception('Conta não habilitada.');
            PerfilPolicy::exigirPerfilComum($usuario);
            $sucesso = $this->protetorRepository->aprovarSolicitacao($protetorId);
            $tipo = $atual['tipo_documento'] === 'cnpj' ? 'ong' : 'protetor';
            $perfis = array_filter(explode(',', $usuario['perfis_ativos']), fn($p) => $p !== '' && $p !== 'usuario');
            $perfis[] = $tipo;
            $stmt = $db->prepare('UPDATE USUARIO SET perfis_ativos = ? WHERE usuario_id = ?');
            $stmt->execute([implode(',', array_unique($perfis)), $usuario['usuario_id']]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }

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

        if (trim($motivo) === '' || mb_strlen($motivo) > 2000) throw new Exception('Informe motivo de até 2000 caracteres.');
        $db = ConnectionFactory::getConnection();
        $coluna = $db->query("SHOW COLUMNS FROM PROTETOR LIKE 'motivo_recusa'")->fetch();
        if (!$coluna) throw new Exception('A recusa exige a migração pós-banca para persistir o motivo.');
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT validado, deletado_em FROM PROTETOR WHERE protetor_id = ? FOR UPDATE');
            $stmt->execute([$protetorId]);
            $atual = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$atual || !empty($atual['validado']) || !empty($atual['deletado_em'])) throw new Exception('Solicitação não está pendente.');
            $sucesso = $this->protetorRepository->recusarSolicitacao($protetorId, trim($motivo));
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }

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
