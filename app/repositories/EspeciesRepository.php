<?php

namespace app\repositories;

use app\models\Especies;
use PDO;

class EspeciesRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listarTodas(string $status = 'todos'): array
    {
        $sql = "SELECT * FROM ESPECIES";

        if ($status === 'ativos') {
            $sql .= " WHERE ativo = TRUE";
        } elseif ($status === 'inativos') {
            $sql .= " WHERE ativo = FALSE";
        }

        $sql .= " ORDER BY nome ASC";

        $stmt = $this->db->query($sql);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $especies = [];
        foreach ($dados as $d) {
            $e = new Especies();
            $e->setId((int)$d['especie_id']);
            $e->setNome($d['nome']);
            $e->setAtivo((bool)$d['ativo']);
            $especies[] = $e;
        }

        return $especies;
    }

    public function buscarPorId(int $id): ?Especies
    {
        $stmt = $this->db->prepare("SELECT * FROM ESPECIES WHERE especie_id = :id");
        $stmt->execute([':id' => $id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$d) return null;

        $e = new Especies();
        $e->setId((int)$d['especie_id']);
        $e->setNome($d['nome']);
        return $e;
    }

    public function buscarOuCriarPorNome(string $nome): Especies
    {
        $stmt = $this->db->prepare("SELECT * FROM ESPECIES WHERE LOWER(nome) = LOWER(:nome)");
        $stmt->execute([':nome' => $nome]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            $especie = new Especies();
            $especie->setId((int)$dados['especie_id']);
            $especie->setNome($dados['nome']);
            return $especie;
        }

        $stmtInsert = $this->db->prepare("INSERT INTO ESPECIES (nome) VALUES (:nome)");
        $stmtInsert->execute([':nome' => $nome]);

        $especie = new Especies();
        $especie->setId((int)$this->db->lastInsertId());
        $especie->setNome($nome);
        return $especie;
    }

    public function cadastrar(Especies $especie): bool
    {
        $stmt = $this->db->prepare("INSERT INTO ESPECIES (nome) VALUES (:nome)");
        return $stmt->execute([':nome' => $especie->getNome()]);
    }

    public function atualizar(Especies $especie): bool
    {
        $stmt = $this->db->prepare("UPDATE ESPECIES SET nome = :nome WHERE especie_id = :id");
        return $stmt->execute([
            ':nome' => $especie->getNome(),
            ':id' => $especie->getId()
        ]);
    }


    public function excluir(int $id): bool
    {
        try {
            // Inicia a transação para garantir que ambas as tabelas sejam atualizadas juntas
            $this->db->beginTransaction();


            $stmtEspecie = $this->db->prepare("UPDATE ESPECIES SET ativo = FALSE WHERE especie_id = :id");
            $stmtEspecie->execute([':id' => $id]);


            $stmtRacas = $this->db->prepare("UPDATE RACA SET ativo = FALSE WHERE especie_id = :id");
            $stmtRacas->execute([':id' => $id]);

            return $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function reativar(int $id): bool
    {
        try {
            $this->db->beginTransaction();


            $stmtEspecie = $this->db->prepare("UPDATE ESPECIES SET ativo = TRUE WHERE especie_id = :id");
            $stmtEspecie->execute([':id' => $id]);

            $stmtRacas = $this->db->prepare("UPDATE RACA SET ativo = TRUE WHERE especie_id = :id");
            $stmtRacas->execute([':id' => $id]);

            return $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
