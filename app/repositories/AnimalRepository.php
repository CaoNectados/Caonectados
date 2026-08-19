<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Animal;
use PDO;
use PDOStatement;

class AnimalRepository extends BaseRepository
{
    public function cadastrarAnimal(Animal $animal): int
    {
        $sql = "INSERT INTO ANIMAL (
            protetor_id,
            raca_id,
            nome,
            dt_nasc,
            sexo,
            porte,
            status,
            descricao,
            vacinado,
            castrado,
            comportamento,
            historico_saude,
            atualizado_em
        ) VALUES (
            :protetor_id,
            :raca_id,
            :nome,
            :dt_nasc,
            :sexo,
            :porte,
            :status,
            :descricao,
            :vacinado,
            :castrado,
            :comportamento,
            :historico_saude,
            CURRENT_TIMESTAMP
        )";

        $stmt = $this->db->prepare($sql);
        $this->bindAnimalValues($stmt, $animal);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?Animal
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        WHERE a.animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapAnimal($row);
    }

    public function listarComFiltros(string $tipoPerfil, int $protetorId, string $status = 'todos'): array
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        WHERE 1=1";

        $params = [];

        if ($tipoPerfil !== 'administrador') {
            $sql .= " AND a.protetor_id = :protetor_id";
            $params[':protetor_id'] = $protetorId;
        }

        if ($status !== 'todos' && !empty($status)) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY a.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row) => $this->mapAnimal($row), $rows);
    }

    public function listarAnimal(): array
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        ORDER BY a.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapAnimal($row), $rows);
    }

    public function editarAnimal(Animal $animal): bool
    {
        $sql = "UPDATE ANIMAL SET
            protetor_id = :protetor_id,
            raca_id = :raca_id,
            nome = :nome,
            dt_nasc = :dt_nasc,
            sexo = :sexo,
            porte = :porte,
            status = :status,
            descricao = :descricao,
            vacinado = :vacinado,
            castrado = :castrado,
            comportamento = :comportamento,
            historico_saude = :historico_saude,
            atualizado_em = CURRENT_TIMESTAMP
        WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $this->bindAnimalValues($stmt, $animal);
        $stmt->bindValue(':animal_id', $animal->getAnimalId(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() >= 0;
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $sql = "UPDATE ANIMAL SET status = :status, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function excluirLogico(int $id): bool
    {
        $sql = "UPDATE ANIMAL SET status = 'desativado', deletado_em = CURRENT_TIMESTAMP, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function reativarAnimal(int $id): bool
    {
        $sql = "UPDATE ANIMAL SET status = 'disponivel', deletado_em = NULL, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function buscarPorProtetor(int $protetorId): array
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        WHERE a.protetor_id = :protetor_id
        ORDER BY a.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapAnimal($row), $rows);
    }

    private function bindAnimalValues(PDOStatement $stmt, Animal $animal): void
    {
        $stmt->bindValue(':protetor_id', $animal->getProtetorId(), PDO::PARAM_INT);
        $stmt->bindValue(':raca_id', $animal->getRacaId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $animal->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':dt_nasc', $animal->getDtNasc(), $animal->getDtNasc() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':sexo', $animal->getSexo(), PDO::PARAM_STR);
        $stmt->bindValue(':porte', $animal->getPorte(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $animal->getStatus(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $animal->getDescricao(), $animal->getDescricao() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':vacinado', $animal->isVacinado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':castrado', $animal->isCastrado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':comportamento', $animal->getComportamento(), $animal->getComportamento() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':historico_saude', $animal->getHistoricoSaude(), $animal->getHistoricoSaude() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    private function mapAnimal(array $row): Animal
    {
        $animal = new Animal();
        $animal->setAnimalId((int) $row['animal_id']);
        $animal->setProtetorId((int) $row['protetor_id']);
        $animal->setRacaId((int) $row['raca_id']);
        $animal->setRacaNome($row['raca_nome'] ?? null);
        $animal->setNome($row['nome']);
        $animal->setDtNasc($row['dt_nasc']);
        $animal->setSexo($row['sexo']);
        $animal->setPorte($row['porte']);
        $animal->setStatus($row['status']);
        $animal->setDescricao($row['descricao']);
        $animal->setVacinado((bool) $row['vacinado']);
        $animal->setCastrado((bool) $row['castrado']);
        $animal->setComportamento($row['comportamento']);
        $animal->setHistoricoSaude($row['historico_saude']);
        $animal->setCriadoEm($row['criado_em']);
        $animal->setDeletadoEm($row['deletado_em']);
        $animal->setAtualizadoEm($row['atualizado_em']);
        
        if (isset($row['raca_nome'])) {
            $animal->setRacaNome($row['raca_nome']);
        }

        return $animal;
    }
}
