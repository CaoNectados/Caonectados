<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 19 (RN 14/15). Uma ADVERTENCIA nasce sempre atrelada a uma DENUNCIA aprovada
 * (denuncia_id NOT NULL no schema) e é o que dá base pra uma CONTESTACAO (RF 17).
 */
class AdvertenciaRepository extends BaseRepository
{
    // Usado por: DenunciaService::aprovar() (RF 19 — sanção aplicada junto da aprovação)
    public function criar(int $usuarioId, int $denunciaId, string $perfilAfetado, string $pesoStatus, ?string $dataFim): int
    {
        $sql = "INSERT INTO ADVERTENCIA (usuario_id, denuncia_id, perfil_afetado, peso_status, data_fim, status)
                VALUES (:usuario_id, :denuncia_id, :perfil_afetado, :peso_status, :data_fim, 'ativa')";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':denuncia_id', $denunciaId, PDO::PARAM_INT);
        $stmt->bindValue(':perfil_afetado', $perfilAfetado, PDO::PARAM_STR);
        $stmt->bindValue(':peso_status', $pesoStatus, PDO::PARAM_STR);
        $stmt->bindValue(':data_fim', $dataFim, $dataFim === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: ContestacaoService (checagem de posse + dados pro parecer) e telas de detalhe
    public function buscarPorId(int $advertenciaId): ?array
    {
        $sql = "SELECT a.*, d.motivo AS denuncia_motivo, d.descricao AS denuncia_descricao
                FROM ADVERTENCIA a
                INNER JOIN DENUNCIA d ON d.denuncia_id = a.denuncia_id
                WHERE a.advertencia_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $advertenciaId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Usado por: ContestacaoController — só pode contestar penalidade própria e ativa (RF 17)
    public function listarAtivasPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT * FROM ADVERTENCIA WHERE usuario_id = :usuario_id AND status = 'ativa' ORDER BY criado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: ContestacaoService::aprovar() — RF 22, encerra a punição
    public function atualizarStatus(int $advertenciaId, string $status): bool
    {
        $sql = "UPDATE ADVERTENCIA SET status = :status WHERE advertencia_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $advertenciaId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
