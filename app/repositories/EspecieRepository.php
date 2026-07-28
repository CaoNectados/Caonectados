<?php

namespace app\repositories;

use app\models\Especie;
use PDO;

class EspecieRepository
{
    private ?PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db;
    }

    public function listarTodas(string $status = 'todos'): array
    {
        $sql = "SELECT * FROM ESPECIE";

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
            $e = new Especie();
            $e->setId((int)$d['especie_id']);
            $e->setNome($d['nome']);
            $e->setAtivo((bool)$d['ativo']);
            $especies[] = $e;
        }

        return $especies;
    }

    public function buscarPorId(int $id): ?Especie
    {
        $stmt = $this->db->prepare("SELECT * FROM ESPECIE WHERE especie_id = :id");
        $stmt->execute([':id' => $id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$d) return null;

        $e = new Especie();
        $e->setId((int)$d['especie_id']);
        $e->setNome($d['nome']);
        $e->setAtivo((bool)$d['ativo']);
        return $e;
    }

    public function buscarOuCriarPorNome(string $nome): Especie
    {
        $stmt = $this->db->prepare("SELECT * FROM ESPECIE WHERE LOWER(nome) = LOWER(:nome)");
        $stmt->execute([':nome' => $nome]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            $especie = new Especie();
            $especie->setId((int)$dados['especie_id']);
            $especie->setNome($dados['nome']);
            $especie->setAtivo((bool)$dados['ativo']);
            return $especie;
        }

        $stmtInsert = $this->db->prepare("INSERT INTO ESPECIE (nome) VALUES (:nome)");
        $stmtInsert->execute([':nome' => $nome]);

        $especie = new Especie();
        $especie->setId((int)$this->db->lastInsertId());
        $especie->setNome($nome);
        $especie->setAtivo(true);
        return $especie;
    }

    public function cadastrar(Especie $especie): bool
    {
        $stmt = $this->db->prepare("INSERT INTO ESPECIE (nome) VALUES (:nome)");
        return $stmt->execute([':nome' => $especie->getNome()]);
    }

    public function atualizar(Especie $especie): bool
    {
        $stmt = $this->db->prepare("UPDATE ESPECIE SET nome = :nome WHERE especie_id = :id");
        return $stmt->execute([
            ':nome' => $especie->getNome(),
            ':id' => $especie->getId()
        ]);
    }

    public function excluir(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtEspecie = $this->db->prepare("UPDATE ESPECIE SET ativo = FALSE WHERE especie_id = :id");
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

            $stmtEspecie = $this->db->prepare("UPDATE ESPECIE SET ativo = TRUE WHERE especie_id = :id");
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

    public function buscarTodas(?PDO $pdo = null): array
    {
        $conexao = $pdo ?? $this->db;
        $stmt = $conexao->query("SELECT especie_id, nome FROM ESPECIE WHERE ativo = TRUE ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}