<?php

namespace app\repositories;

use app\models\Protetor;
use PDO;

class ProtetorRepository
{
    public function salvar(Protetor $protetor, PDO $pdo): int
    {
        $sql = "INSERT INTO PROTETORES (usuario_id, validado, codigo_documento, tipo_documento, nome_fantasia) 
                VALUES (:usuario_id, :validado, :codigo_documento, :tipo_documento, :nome_fantasia)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $protetor->getUsuarioId());
        $stmt->bindValue(':validado', $protetor->isValidado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_documento', $protetor->getCodigoDocumento());
        $stmt->bindValue(':tipo_documento', $protetor->getTipoDocumento());
        $stmt->bindValue(':nome_fantasia', $protetor->getNomeFantasia());

        $stmt->execute();
        return (int)$pdo->lastInsertId();
    }

    public function salvarPagina(int $protetorId, string $chavePix, PDO $pdo): bool
    {
        $sql = "INSERT INTO PAGINAS (protetor_id, descricao) VALUES (:protetor_id, :descricao)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId);
        $stmt->bindValue(':descricao', "Chave PIX: " . $chavePix);
        return $stmt->execute();
    }

    public function salvarRedeSocial(int $protetorId, string $link, string $tipo, PDO $pdo): bool
    {
        $sql = "INSERT INTO REDES (protetor_id, link_rede, tipo_rede) VALUES (:protetor_id, :link_rede, :tipo_rede)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId);
        $stmt->bindValue(':link_rede', $link);
        $stmt->bindValue(':tipo_rede', $tipo);
        return $stmt->execute();
    }
}