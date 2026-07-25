<?php

namespace app\repositories;

use app\models\Tutor;
use PDO;

class TutorRepository
{
    public function salvar(Tutor $tutor, PDO $pdo): bool
    {
        $sql = "INSERT INTO TUTORES (usuario_id, tipo_moradia, tamanho_interno_moradia, tamanho_externo_moradia, detalhes) 
                VALUES (:usuario_id, :tipo_moradia, :tamanho_interno_moradia, :tamanho_externo_moradia, :detalhes)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $tutor->getUsuarioId());
        $stmt->bindValue(':tipo_moradia', $tutor->getTipoMoradia());
        $stmt->bindValue(':tamanho_interno_moradia', $tutor->getTamanhoInternoMoradia());
        $stmt->bindValue(':tamanho_externo_moradia', $tutor->getTamanhoExternoMoradia());
        $stmt->bindValue(':detalhes', $tutor->getDetalhes());

        return $stmt->execute();
    }
}