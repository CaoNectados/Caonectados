<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Raca;
use app\models\Especie;
use PDO;

class RacaRepository extends BaseRepository
{
    public function buscarPorEspecie(int $especieId): array
    {
        $sql = "SELECT raca_id, especie_id, nome, ativo 
                FROM RACA 
                WHERE especie_id = :especie_id 
                  AND ativo = TRUE 
                ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':especie_id', $especieId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodas(): array
    {
        $sql = "SELECT raca_id, especie_id, nome, ativo 
                FROM RACA 
                WHERE ativo = TRUE 
                ORDER BY nome ASC";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodas(string $status = 'todos'): array
    {
        $sql = "SELECT r.*, e.nome AS especie_nome 
                FROM RACA r 
                INNER JOIN ESPECIE e ON r.especie_id = e.especie_id";

        if ($status === 'ativos') {
            $sql .= " WHERE r.ativo = TRUE";
        } elseif ($status === 'inativos') {
            $sql .= " WHERE r.ativo = FALSE";
        }

        $sql .= " ORDER BY e.nome ASC, r.nome ASC";

        $stmt = $this->db->query($sql);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapRaca($row), $dados);
    }

    public function buscarPorId(int $id): ?Raca
    {
        $sql = "SELECT * FROM RACA WHERE raca_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapRaca($row);
    }

    public function existePorNomeEEspecie(string $nome, int $especieId): bool
    {
        $sql = "SELECT COUNT(*) FROM RACA WHERE LOWER(nome) = LOWER(:nome) AND especie_id = :especie_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':especie_id', $especieId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function cadastrar(Raca $raca): bool
    {
        $sql = "INSERT INTO RACA (nome, especie_id) VALUES (:nome, :especie_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $raca->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':especie_id', $raca->getEspecieId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function atualizar(Raca $raca): bool
    {
        $sql = "UPDATE RACA SET nome = :nome, especie_id = :especie_id WHERE raca_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $raca->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':especie_id', $raca->getEspecieId(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $raca->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $sql = "UPDATE RACA SET ativo = FALSE WHERE raca_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function reativar(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Ativa a raça
            $sql = "UPDATE RACA SET ativo = TRUE WHERE raca_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Ativa obrigatoriamente a espécie vinculada caso esteja inativa
            $sqlEspecie = "UPDATE ESPECIE e 
                           INNER JOIN RACA r ON e.especie_id = r.especie_id 
                           SET e.ativo = TRUE 
                           WHERE r.raca_id = :id";
            $stmtEsp = $this->db->prepare($sqlEspecie);
            $stmtEsp->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtEsp->execute();

            return $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    private function mapRaca(array $row): Raca
    {
        $raca = new Raca();
        $raca->setId((int) $row['raca_id']);
        $raca->setNome($row['nome']);
        $raca->setEspecieId((int) $row['especie_id']);
        $raca->setAtivo((bool) $row['ativo']);

        if (!empty($row['especie_nome'])) {
            $especie = new Especie();
            $especie->setId((int) $row['especie_id']);
            $especie->setNome($row['especie_nome']);
            $raca->setEspecie($especie);
        }

        return $raca;
    }
}