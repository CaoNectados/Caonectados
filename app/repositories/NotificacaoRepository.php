<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 11 (UC 06) / RN 06: toda alteração relevante de estado gera uma notificação pro usuário
 * afetado. tipo_notificacao é o ENUM da tabela: solicitacao, mensagem, denuncia, contestacao,
 * advertencia, sistema.
 */
class NotificacaoRepository extends BaseRepository
{
    // Usado por: NotificacaoService — toda notificação disparada no sistema
    public function criar(int $usuarioId, string $texto, ?int $referenciaId = null, string $tipo = 'sistema'): int
    {
        $sql = "INSERT INTO NOTIFICACAO (usuario_id, tipo_notificacao, txt_notificacao, referencia_id)
                VALUES (:usuario_id, :tipo, :texto, :referencia_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':texto', $texto, PDO::PARAM_STR);
        $stmt->bindValue(':referencia_id', $referenciaId, $referenciaId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: NotificacaoService::obterParaMarcarComoLida() — checagem de posse antes de
    // marcar como lida ou redirecionar (só o dono da notificação pode fazer as duas coisas)
    public function buscarPorId(int $notificacaoId): ?array
    {
        $sql = "SELECT notificacao_id, usuario_id, referencia_id, tipo_notificacao, lida, txt_notificacao, criado_em
                FROM NOTIFICACAO WHERE notificacao_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $notificacaoId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Usado por: NotificacaoService::listarPagina() — tela /notificacoes (UC 06) e "carregar mais"
    public function listarPorUsuario(int $usuarioId, int $limite, int $offset): array
    {
        $sql = "SELECT notificacao_id, usuario_id, referencia_id, tipo_notificacao, lida, txt_notificacao, criado_em
                FROM NOTIFICACAO
                WHERE usuario_id = :usuario_id
                ORDER BY criado_em DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: header.php — badge do sininho na navbar
    public function contarNaoLidas(int $usuarioId): int
    {
        $sql = "SELECT COUNT(*) FROM NOTIFICACAO WHERE usuario_id = :usuario_id AND lida = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // Usado por: NotificacaoService::abrir() — clique numa notificação (UC 06)
    public function marcarComoLida(int $notificacaoId): bool
    {
        $sql = "UPDATE NOTIFICACAO SET lida = 1 WHERE notificacao_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $notificacaoId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
