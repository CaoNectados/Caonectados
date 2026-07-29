<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Tutor;
use PDO;

class TutorRepository extends BaseRepository
{
    public function salvar(Tutor $tutor): int
    {
        $sql = "INSERT INTO TUTOR (usuario_id, tipo_morada, foto_perfil, descricao, tamanho_interno_morada, detalhes) 
                VALUES (:usuario_id, :tipo_morada, :foto_perfil, :descricao, :tamanho_interno_morada, :detalhes)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $tutor->getUsuarioId(), PDO::PARAM_INT);
        $stmt->bindValue(':tipo_morada', $tutor->getTipoMorada(), PDO::PARAM_STR);
        $stmt->bindValue(':foto_perfil', $tutor->getFotoPerfil(), $tutor->getFotoPerfil() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $tutor->getDescricao(), $tutor->getDescricao() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':tamanho_interno_morada', $tutor->getTamanhoInternoMoradia(), $tutor->getTamanhoInternoMoradia() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':detalhes', $tutor->getDetalhes(), $tutor->getDetalhes() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorUsuarioId(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM TUTOR WHERE usuario_id = :usuario_id AND deletado_em IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados ?: null;
    }

    public function atualizarTutor(int $usuarioId, string $tipoMorada, ?string $fotoPerfil): bool
    {
        if ($fotoPerfil !== null) {
            $sql = "UPDATE TUTOR SET tipo_morada = :tipo_morada, foto_perfil = :foto_perfil WHERE usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':foto_perfil', $fotoPerfil, PDO::PARAM_STR);
        } else {
            $sql = "UPDATE TUTOR SET tipo_morada = :tipo_morada WHERE usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->bindValue(':tipo_morada', $tipoMorada, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}