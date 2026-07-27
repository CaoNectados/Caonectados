<?php

namespace app\repositories;

use app\models\Tutor;
use PDO;

class TutorRepository
{
    public function salvar(Tutor $tutor, PDO $pdo): int
    {
        $sql = "INSERT INTO TUTOR (usuario_id, tipo_morada, foto_perfil, descricao, tamanho_interno_morada, detalhes) 
                VALUES (:usuario_id, :tipo_morada, :foto_perfil, :descricao, :tamanho_interno_morada, :detalhes)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $tutor->getUsuarioId(), PDO::PARAM_INT);
        $stmt->bindValue(':tipo_morada', $tutor->getTipoMorada());
        $stmt->bindValue(':foto_perfil', $tutor->getFotoPerfil());
        $stmt->bindValue(':descricao', $tutor->getDescricao());
        $stmt->bindValue(':tamanho_interno_morada', $tutor->getTamanhoInternoMoradia());
        $stmt->bindValue(':detalhes', $tutor->getDetalhes());

        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }
}