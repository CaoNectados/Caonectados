<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 18 (UC 05 — abrir) / RF 21 (UC 12 — moderar). RN 21: denúncia pode ser avulsa ou
 * associada opcionalmente a uma solicitação de adoção ou a um chat.
 */
class DenunciaRepository extends BaseRepository
{
    private const SELECT_BASE = "
        SELECT
            d.denuncia_id, d.denunciante_id, d.denunciado_id, d.perfil_denunciado,
            d.solicitacao_id, d.chat_id, d.motivo, d.descricao, d.status_denuncia,
            d.decisao_admin, d.criado_em,
            denunciante.nome AS denunciante_nome,
            denunciado.nome AS denunciado_nome, denunciado.email AS denunciado_email
        FROM DENUNCIA d
        INNER JOIN USUARIO denunciante ON denunciante.usuario_id = d.denunciante_id
        INNER JOIN USUARIO denunciado ON denunciado.usuario_id = d.denunciado_id
    ";

    // Usado por: DashboardController::index() — card "Denúncias em Aberto"
    public function contarAbertas(): int
    {
        $sql = "SELECT COUNT(*) FROM DENUNCIA WHERE status_denuncia IN ('aberta', 'em_analise')";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    // Usado por: DenunciaService::criar() (RF 18 / UC 05)
    public function criar(int $denuncianteId, int $denunciadoId, string $perfilDenunciado, string $motivo, string $descricao, ?int $solicitacaoId, ?int $chatId): int
    {
        $sql = "INSERT INTO DENUNCIA (denunciante_id, denunciado_id, perfil_denunciado, motivo, descricao, solicitacao_id, chat_id)
                VALUES (:denunciante_id, :denunciado_id, :perfil_denunciado, :motivo, :descricao, :solicitacao_id, :chat_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':denunciante_id', $denuncianteId, PDO::PARAM_INT);
        $stmt->bindValue(':denunciado_id', $denunciadoId, PDO::PARAM_INT);
        $stmt->bindValue(':perfil_denunciado', $perfilDenunciado, PDO::PARAM_STR);
        $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
        $stmt->bindValue(':solicitacao_id', $solicitacaoId, $solicitacaoId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':chat_id', $chatId, $chatId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: DenunciaService (moderação e checagem de posse) e ContestacaoService
    public function buscarPorId(int $denunciaId): ?array
    {
        $sql = self::SELECT_BASE . " WHERE d.denuncia_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $denunciaId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Usado por: DenunciaController (geral) — aba "Minhas Denúncias" (UC 05.2)
    public function listarPorDenunciante(int $denuncianteId): array
    {
        $sql = self::SELECT_BASE . " WHERE d.denunciante_id = :id ORDER BY d.criado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $denuncianteId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: DenunciaController (admin) — painel de moderação (UC 12), abas por status
    public function listarPorStatus(string $status, int $limite = 50): array
    {
        $sql = self::SELECT_BASE . " WHERE d.status_denuncia = :status ORDER BY d.criado_em DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: DenunciaController (admin) — listagem básica antiga (dashboard e afins)
    public function listarAbertas(int $limite = 50): array
    {
        $sql = self::SELECT_BASE . " WHERE d.status_denuncia IN ('aberta', 'em_analise') ORDER BY d.criado_em DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: DenunciaService — toda transição de status_denuncia (RF 21 / UC 12)
    public function atualizarStatus(int $denunciaId, string $status, string $decisaoAdmin): bool
    {
        $sql = "UPDATE DENUNCIA SET status_denuncia = :status, decisao_admin = :decisao WHERE denuncia_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':decisao', $decisaoAdmin, PDO::PARAM_STR);
        $stmt->bindValue(':id', $denunciaId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
