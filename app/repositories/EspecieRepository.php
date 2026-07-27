<?php

namespace app\repositories;

use PDO;

class EspecieRepository
{
    public function buscarTodas(PDO $pdo): array
    {
        $sql = "SELECT especie_id, nome FROM ESPECIE ORDER BY nome ASC";
        $stmt = $pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}