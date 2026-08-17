<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\SolicitacoesAdocao;
use PDO;

class SolicitacoesAdocaoRepository extends BaseRepository
{
    public function salvar(SolicitacoesAdocao $solicitacao): int
    {
        $sql = "INSERT INTO SOLICITACAO_ADOCAO (adotante_id, animal_id, status_solicitacao, justificativa_recusa) 
                VALUES (:adotante_id, :animal_id, :status_solicitacao, :justificativa_recusa)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':adotante_id', $solicitacao->getAdotanteId(), PDO::PARAM_INT);
        $stmt->bindValue(':animal_id', $solicitacao->getAnimalId(), PDO::PARAM_INT);
        $stmt->bindValue(':status_solicitacao', $solicitacao->getStatusSolicitacao() ?? 'pendente', PDO::PARAM_STR);
        $stmt->bindValue(':justificativa_recusa', $solicitacao->getJustificativaRecusa(), PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?SolicitacoesAdocao
    {
        $sql = "SELECT * FROM SOLICITACAO_ADOCAO WHERE solicitacao_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapSolicitacao($row);
    }

    public function listarTodos(): array
    {
        $sql = "SELECT * FROM SOLICITACAO_ADOCAO ORDER BY data_solicitacao DESC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapSolicitacao($row), $rows);
    }

    public function atualizar(SolicitacoesAdocao $solicitacao): bool
    {
        $sql = "UPDATE SOLICITACAO_ADOCAO 
                SET status_solicitacao = :status_solicitacao, 
                    justificativa_recusa = :justificativa_recusa,
                    data_finalizacao = :data_finalizacao
                WHERE solicitacao_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status_solicitacao', $solicitacao->getStatusSolicitacao(), PDO::PARAM_STR);
        $stmt->bindValue(':justificativa_recusa', $solicitacao->getJustificativaRecusa(), PDO::PARAM_STR);
        $stmt->bindValue(':data_finalizacao', $solicitacao->getDataFinalizacao(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $solicitacao->getSolicitacaoId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deletar(int $id): bool
    {
        $sql = "DELETE FROM SOLICITACAO_ADOCAO WHERE solicitacao_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function mapSolicitacao(array $row): SolicitacoesAdocao
    {
        $solicitacao = new SolicitacoesAdocao();
        $solicitacao->setSolicitacaoId((int) $row['solicitacao_id']);
        $solicitacao->setAdotanteId((int) $row['adotante_id']);
        $solicitacao->setAnimalId((int) $row['animal_id']);
        $solicitacao->setStatusSolicitacao($row['status_solicitacao']);
        $solicitacao->setDataSolicitacao($row['data_solicitacao']);
        $solicitacao->setJustificativaRecusa($row['justificativa_recusa']);
        $solicitacao->setDataFinalizacao($row['data_finalizacao']);

        return $solicitacao;
    }
}
