<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Regiao;
use PDO;

class RegiaoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function listar(): array
    {
        $sql = "
            SELECT regiao_id, nome_regiao
            FROM REGIAO
            ORDER BY nome_regiao
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $regiao = new Regiao();

            $regiao->setRegiaoId($row['regiao_id']);
            $regiao->setNomeRegiao($row['nome_regiao']);

            $lista[] = $regiao;
        }

        return $lista;
    }
}