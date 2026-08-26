<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * Trilha de auditoria (RF 08/RF 09): uma linha por MUDANÇA de status de uma
 * SOLICITACAO_ADOCAO, não uma cópia do estado atual.
 */
class HistoricoSolicitacaoRepository extends BaseRepository
{
    // Usado por: SolicitacaoAdocaoService — toda transição de status (criação inclusive,
    // com status_antigo = null)
    public function registrar(int $solicitacaoId, int $usuarioResponsavelId, ?string $statusAntigo, string $statusNovo): void
    {
        $sql = "INSERT INTO HISTORICO_SOLICITACAO (solicitacao_id, usuario_responsavel_id, status_antigo, status_novo)
                VALUES (:solicitacao_id, :usuario_responsavel_id, :status_antigo, :status_novo)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':solicitacao_id', $solicitacaoId, PDO::PARAM_INT);
        $stmt->bindValue(':usuario_responsavel_id', $usuarioResponsavelId, PDO::PARAM_INT);
        $stmt->bindValue(':status_antigo', $statusAntigo, $statusAntigo === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':status_novo', $statusNovo, PDO::PARAM_STR);
        $stmt->execute();
    }
}
