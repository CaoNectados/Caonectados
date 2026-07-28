<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Regiao;
use PDO;
use PDOStatement;

class RegiaoRepository extends BaseRepository
{
    public function cadastrarRegiao(Regiao $regiao): int
    {
        $sql = "INSERT INTO REGIOES (nome_regiao) VALUES (:nome_regiao)"; // Mantido REGIOES se a tabela for plural, mas o ideal é alterar para REGIAO no banco. Assumindo REGIAO conforme seu script SQL.
        $sql = "INSERT INTO REGIAO (nome_regiao) VALUES (:nome_regiao)";

        $stmt = $this->db->prepare($sql);
        $this->bindRegiaoValues($stmt, $regiao);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?Regiao
    {
        $sql = "SELECT regiao_id, nome_regiao FROM REGIOES WHERE regiao_id = :regiao_id";
        $sql = "SELECT regiao_id, nome_regiao FROM REGIAO WHERE regiao_id = :regiao_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':regiao_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapRegiao($row);
    }

    public function buscarPorNome(string $nomeRegiao, ?int $ignorarId = null): ?Regiao
    {
        $sql = "SELECT regiao_id, nome_regiao FROM REGIAO WHERE LOWER(nome_regiao) = LOWER(:nome_regiao)";
        
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
        $sql = "SELECT regiao_id, nome_regiao FROM REGIAO ORDER BY nome_regiao ASC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $row): Regiao {
            return $this->mapRegiao($row);
        }, $rows);
    }

    public function editarRegiao(Regiao $regiao): bool
    {
        $sql = "UPDATE REGIAO SET nome_regiao = :nome_regiao WHERE regiao_id = :regiao_id";

        $stmt = $this->db->prepare($sql);
        $this->bindRegiaoValues($stmt, $regiao);
        $stmt->bindValue(':regiao_id', $regiao->getRegiaoId(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function excluirRegiao(int $id): bool
    {
        $sql = "DELETE FROM REGIOO WHERE regiao_id = :regiao_id";
        $sql = "DELETE FROM REGIAO WHERE regiao_id = :regiao_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':regiao_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function bindRegiaoValues(PDOStatement $stmt, Regiao $regiao): void
    {
        $stmt->bindValue(':nome_regiao', trim($regiao->getNomeRegiao()), PDO::PARAM_STR);
    }

    private function mapRegiao(array $row): Regiao
    {
        $regiao = new Regiao();
        $regiao->setRegiaoId((int) $row['regiao_id']);
        $regiao->setNomeRegiao($row['nome_regiao']);

        return $regiao;
    }
}