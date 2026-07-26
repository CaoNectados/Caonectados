<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Regioes;
use PDO;
use PDOStatement;

class RegioesRepository extends BaseRepository
{
    public function cadastrarRegiao(Regioes $regiao): int
    {
        $sql = "INSERT INTO REGIOES (nome_regiao) VALUES (:nome_regiao)";

        $stmt = $this->db->prepare($sql);
        $this->bindRegiaoValues($stmt, $regiao);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?Regioes
    {
        $sql = "SELECT regiao_id, nome_regiao FROM REGIOES WHERE regiao_id = :regiao_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':regiao_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapRegiao($row);
    }

    public function buscarPorNome(string $nomeRegiao, ?int $ignorarId = null): ?Regioes
    {
        $sql = "SELECT regiao_id, nome_regiao FROM REGIOES WHERE LOWER(nome_regiao) = LOWER(:nome_regiao)";
        
        if ($ignorarId !== null) {
            $sql .= " AND regiao_id != :ignorar_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome_regiao', trim($nomeRegiao), PDO::PARAM_STR);
        
        if ($ignorarId !== null) {
            $stmt->bindValue(':ignorar_id', $ignorarId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapRegiao($row);
    }

    public function listarRegioes(): array
    {
        $sql = "SELECT regiao_id, nome_regiao FROM REGIOES ORDER BY nome_regiao ASC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $row): Regioes {
            return $this->mapRegiao($row);
        }, $rows);
    }

    public function editarRegiao(Regioes $regiao): bool
    {
        $sql = "UPDATE REGIOES SET nome_regiao = :nome_regiao WHERE regiao_id = :regiao_id";

        $stmt = $this->db->prepare($sql);
        $this->bindRegiaoValues($stmt, $regiao);
        $stmt->bindValue(':regiao_id', $regiao->getRegiaoId(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function excluirRegiao(int $id): bool
    {
        $sql = "DELETE FROM REGIOES WHERE regiao_id = :regiao_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':regiao_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function bindRegiaoValues(PDOStatement $stmt, Regioes $regiao): void
    {
        $stmt->bindValue(':nome_regiao', trim($regiao->getNomeRegiao()), PDO::PARAM_STR);
    }

    private function mapRegiao(array $row): Regioes
    {
        $regiao = new Regioes();
        $regiao->setRegiaoId((int) $row['regiao_id']);
        $regiao->setNomeRegiao($row['nome_regiao']);

        return $regiao;
    }
}