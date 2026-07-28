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

    public function buscarPorUsuarioId(int $usuarioId, PDO $pdo): ?array
    {
        $sql = "SELECT * FROM TUTOR WHERE usuario_id = :usuario_id AND deletado_em IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados ?: null;
    }

    public function atualizarTutor(int $usuarioId, string $tipoMorada, ?string $fotoPerfil, PDO $pdo): bool
    {
        if ($fotoPerfil) {
            $sql = "UPDATE TUTOR SET tipo_morada = :tipo_morada, foto_perfil = :foto_perfil WHERE usuario_id = :usuario_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':foto_perfil', $fotoPerfil);
        } else {
            $sql = "UPDATE TUTOR SET tipo_morada = :tipo_morada WHERE usuario_id = :usuario_id";
            $stmt = $pdo->prepare($sql);
        }
        $stmt->bindValue(':tipo_morada', $tipoMorada);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}