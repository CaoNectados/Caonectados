<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Rede;
use PDO;

class RedeRepository extends BaseRepository
{
    // Usado por: PerfilController::exibirPerfil()
    public function buscarPorProtetorId(int $protetorId): array
    {
        $sql = "SELECT tipo_rede, link_rede FROM REDE WHERE protetor_id = :protetor_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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