<?php

namespace app\repositories;

use PDO;

class RacaRepository
{
    public function buscarTodas(PDO $pdo): array
    {
        $sql = "SELECT raca_id, especie_id, nome FROM RACA ORDER BY nome ASC";
        $stmt = $pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorEspecie(PDO $pdo, int $especieId): array
    {
        $sql = "SELECT raca_id, especie_id, nome FROM RACA WHERE especie_id = :especie_id ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':especie_id', $especieId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}