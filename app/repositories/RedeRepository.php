<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Rede;
use PDO;

class RedeRepository extends BaseRepository
{
    // Usado por: PerfilController, OnBoardingService e PaginaController (rede_id incluído
    // pra permitir remover um link específico na gestão da página — os outros consumidores
    // desse método só liam tipo_rede/link_rede, então a coluna extra não quebra nada)
    public function buscarPorProtetorId(int $protetorId): array
    {
        $sql = "SELECT rede_id, tipo_rede, link_rede FROM REDE WHERE protetor_id = :protetor_id ORDER BY rede_id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Usado por: PaginaController::removerRede() — devolve a linha antes de apagar, pra quem
    // chamou confirmar que ela pertence ao protetor certo antes de excluir (defesa contra
    // IDOR: outra ONG passando um rede_id que não é dela).
    public function buscarPorId(int $redeId): ?array
    {
        $sql = "SELECT rede_id, protetor_id, tipo_rede, link_rede FROM REDE WHERE rede_id = :rede_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':rede_id', $redeId, PDO::PARAM_INT);
        $stmt->execute();

        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ?: null;
    }

    // Usado por: PaginaController::removerRede()
    public function removerPorId(int $redeId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM REDE WHERE rede_id = :rede_id");
        $stmt->bindValue(':rede_id', $redeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Usado por: (não referenciado atualmente)
    public function salvar(Rede $rede): int
    {
        $sql = "INSERT INTO REDE (protetor_id, link_rede, tipo_rede) VALUES (:protetor_id, :link_rede, :tipo_rede)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $rede->getProtetorId(), PDO::PARAM_INT);
        $stmt->bindValue(':link_rede', $rede->getLinkRede(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_rede', $rede->getTipoRede(), PDO::PARAM_STR);

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    // Usado por: OnBoardingService::processarOng() e PerfilService::atualizarPerfil() (substitui todas as redes do protetor)
    public function sincronizarRedes(int $protetorId, ?string $instagram, ?string $facebook): void
    {
        $sqlDelete = "DELETE FROM REDE WHERE protetor_id = :protetor_id";
        $stmtDel = $this->db->prepare($sqlDelete);
        $stmtDel->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmtDel->execute();

        $sqlInsert = "INSERT INTO REDE (protetor_id, link_rede, tipo_rede) VALUES (:protetor_id, :link_rede, :tipo_rede)";
        $stmtIns = $this->db->prepare($sqlInsert);

        if (!empty($instagram)) {
            $stmtIns->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
            $stmtIns->bindValue(':link_rede', trim($instagram), PDO::PARAM_STR);
            $stmtIns->bindValue(':tipo_rede', 'instagram', PDO::PARAM_STR);
            $stmtIns->execute();
        }

        if (!empty($facebook)) {
            $stmtIns->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
            $stmtIns->bindValue(':link_rede', trim($facebook), PDO::PARAM_STR);
            $stmtIns->bindValue(':tipo_rede', 'facebook', PDO::PARAM_STR);
            $stmtIns->execute();
        }
    }
}