<?php

namespace app\repositories;

use app\models\Racas;
use app\models\Especies;
use PDO;

class RacasRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listarTodas(string $status = 'todos'): array
    {
        $sql = "SELECT r.*, e.nome AS especie_nome 
            FROM RACA r 
            INNER JOIN ESPECIES e ON r.especie_id = e.especie_id";

        if ($status === 'ativos') {
            $sql .= " WHERE r.ativo = TRUE";
        } elseif ($status === 'inativos') {
            $sql .= " WHERE r.ativo = FALSE";
        }

        $sql .= " ORDER BY e.nome ASC, r.nome ASC";

        $stmt = $this->db->query($sql);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $racas = [];
        foreach ($dados as $d) {
            $r = new Racas();
            $r->setId((int)$d['raca_id']);
            $r->setNome($d['nome']);
            $r->setEspecieId((int)$d['especie_id']);
            $r->setAtivo((bool)$d['ativo']);

            $e = new Especies();
            $e->setId((int)$d['especie_id']);
            $e->setNome($d['especie_nome']);
            $r->setEspecie($e);

            $racas[] = $r;
        }

        return $racas;
    }

    public function buscarPorId(int $id): ?Racas
    {
        $sql = "SELECT r.*, e.nome AS especie_nome 
                FROM RACA r 
                INNER JOIN ESPECIES e ON r.especie_id = e.especie_id 
                WHERE r.raca_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$d) return null;

        $r = new Racas();
        $r->setId((int)$d['raca_id']);
        $r->setNome($d['nome']);
        $r->setEspecieId((int)$d['especie_id']);

        $e = new Especies();
        $e->setId((int)$d['especie_id']);
        $e->setNome($d['especie_nome']);
        $r->setEspecie($e);

        return $r;
    }

    public function existePorNomeEEspecie(string $nome, int $especieId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM RACA WHERE LOWER(nome) = LOWER(:nome) AND especie_id = :especie_id");
        $stmt->execute([
            ':nome' => $nome,
            ':especie_id' => $especieId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function cadastrar(Racas $raca): bool
    {
        $stmt = $this->db->prepare("INSERT INTO RACA (nome, especie_id) VALUES (:nome, :especie_id)");
        return $stmt->execute([
            ':nome' => $raca->getNome(),
            ':especie_id' => $raca->getEspecieId()
        ]);
    }

    public function atualizar(Racas $raca): bool
    {
        $stmt = $this->db->prepare("UPDATE RACA SET nome = :nome, especie_id = :especie_id WHERE raca_id = :id");
        return $stmt->execute([
            ':nome' => $raca->getNome(),
            ':especie_id' => $raca->getEspecieId(),
            ':id' => $raca->getId()
        ]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE RACA SET ativo = FALSE WHERE raca_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function reativar(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE RACA SET ativo = TRUE WHERE raca_id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
