<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 17 (UC 07 — contestar) / RF 22 (UC 13 — moderar). A tabela CONTESTACAO não tem uma coluna
 * de status própria — o estado é derivado: parecer_admin NULL = pendente; preenchido +
 * ADVERTENCIA.status = 'encerrada' = aprovada; preenchido + advertência continua 'ativa' =
 * reprovada. Ver montarComStatus().
 */
class ContestacaoRepository extends BaseRepository
{
    private const SELECT_BASE = "
        SELECT
            c.contestacao_id, c.advertencia_id, c.justificativa, c.anexo, c.parecer_admin, c.data_hora,
            a.usuario_id, a.status AS advertencia_status, a.peso_status, a.perfil_afetado,
            u.nome AS usuario_nome, u.email AS usuario_email
        FROM CONTESTACAO c
        INNER JOIN ADVERTENCIA a ON a.advertencia_id = c.advertencia_id
        INNER JOIN USUARIO u ON u.usuario_id = a.usuario_id
    ";

    // Usado por: ContestacaoService::criar() (RF 17 / UC 07)
    public function criar(int $advertenciaId, string $justificativa, ?string $anexo): int
    {
        $sql = "INSERT INTO CONTESTACAO (advertencia_id, justificativa, anexo) VALUES (:advertencia_id, :justificativa, :anexo)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':advertencia_id', $advertenciaId, PDO::PARAM_INT);
        $stmt->bindValue(':justificativa', $justificativa, PDO::PARAM_STR);
        $stmt->bindValue(':anexo', $anexo, $anexo === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: ContestacaoService (moderação e checagem de posse)
    public function buscarPorId(int $contestacaoId): ?array
    {
        $sql = self::SELECT_BASE . " WHERE c.contestacao_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $contestacaoId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Usado por: ContestacaoController (geral) — acompanhamento (UC 07.2)
    public function listarPorUsuario(int $usuarioId): array
    {
        $sql = self::SELECT_BASE . " WHERE a.usuario_id = :usuario_id ORDER BY c.data_hora DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: ContestacaoController (admin) — pendentes = ainda sem parecer (UC 13)
    public function listarPendentes(): array
    {
        $sql = self::SELECT_BASE . " WHERE c.parecer_admin IS NULL ORDER BY c.data_hora ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: ContestacaoController (admin) — já decididas (qualquer parecer preenchido)
    public function listarDecididas(int $limite = 50): array
    {
        $sql = self::SELECT_BASE . " WHERE c.parecer_admin IS NOT NULL ORDER BY c.data_hora DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: ContestacaoService::decidir() (RF 22)
    public function registrarParecer(int $contestacaoId, string $parecerAdmin): bool
    {
        $sql = "UPDATE CONTESTACAO SET parecer_admin = :parecer WHERE contestacao_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':parecer', $parecerAdmin, PDO::PARAM_STR);
        $stmt->bindValue(':id', $contestacaoId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Usado por: ContestacaoService — deriva o status exibido a partir dos dados relacionados
    public static function montarComStatus(array $contestacao): array
    {
        if ($contestacao['parecer_admin'] === null) {
            $contestacao['status'] = 'pendente';
        } elseif ($contestacao['advertencia_status'] === 'encerrada') {
            $contestacao['status'] = 'aprovada';
        } else {
            $contestacao['status'] = 'reprovada';
        }

        return $contestacao;
    }
}
