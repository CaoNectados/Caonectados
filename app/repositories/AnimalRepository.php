<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Animal;
use PDO;
use PDOStatement;

class AnimalRepository extends BaseRepository
{
    // Usado por: AnimalService::buscarPorId() e AnimalController (detalhes/edição)
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
            rc.nome AS raca_nome,
            fa.caminho_foto AS foto_principal
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
        WHERE a.animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapAnimal($row);
    }

    // Usado por: AnimalService::listarComFiltros() e AnimalController::index()
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
            rc.nome AS raca_nome,
            fa.caminho_foto AS foto_principal
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
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

    // Usado por: AnimalService::cadastrarAnimal() e AnimalController::cadastrar()
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

    // Usado por: AnimalService::editarAnimal() e AnimalController::editar()
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

        return $stmt->execute();
    }

    // Usado por: AnimalService::atualizarStatus() e AnimalController
    public function alterarStatus(int $id, string $status): bool
    {
        $sql = "UPDATE ANIMAL SET status = :status, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Usado por: AnimalService::desativarAnimal() e AnimalController
    public function excluirLogico(int $id): bool
    {
        $sql = "UPDATE ANIMAL SET status = 'desativado', deletado_em = CURRENT_TIMESTAMP, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Usado por: AnimalService::reativarAnimal() e AnimalController::reativar()
    public function reativarAnimal(int $id): bool
    {
        $sql = "UPDATE ANIMAL SET status = 'disponivel', deletado_em = NULL, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Substitui a foto principal do animal (modelo de foto única).
     */
    // Usado por: AnimalService::salvarFoto()
    public function salvarFotoPrincipal(int $animalId, string $caminhoFoto): void
    {
        $stmtDelete = $this->db->prepare("DELETE FROM FOTO_ANIMAL WHERE animal_id = :animal_id AND foto_principal = 1");
        $stmtDelete->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmtDelete->execute();

        $stmtInsert = $this->db->prepare(
            "INSERT INTO FOTO_ANIMAL (animal_id, caminho_foto, foto_principal) VALUES (:animal_id, :caminho_foto, 1)"
        );
        $stmtInsert->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmtInsert->bindValue(':caminho_foto', $caminhoFoto, PDO::PARAM_STR);
        $stmtInsert->execute();
    }

    /**
     * Adiciona mais uma foto ao animal, mantendo as já existentes (modelo 1:N de verdade,
     * diferente de salvarFotoPrincipal() que substitui a única foto_principal).
     */
    // Usado por: AnimalService::salvarFotosAdicionais()
    public function salvarFotoAdicional(int $animalId, string $caminhoFoto): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO FOTO_ANIMAL (animal_id, caminho_foto, foto_principal) VALUES (:animal_id, :caminho_foto, 0)"
        );
        $stmt->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmt->bindValue(':caminho_foto', $caminhoFoto, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: AnimalService::listarFotos(), FeedRepository (carrossel do card no feed)
    public function buscarFotosPorAnimal(int $animalId): array
    {
        $sql = "SELECT foto_id, animal_id, caminho_foto, foto_principal
                FROM FOTO_ANIMAL
                WHERE animal_id = :animal_id
                ORDER BY foto_principal DESC, foto_id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: AnimalService::removerFoto() — retorna a linha antes de apagar, pra quem
    // chamou poder validar posse (animal_id) e apagar o arquivo físico correspondente.
    public function buscarFotoPorId(int $fotoId): ?array
    {
        $sql = "SELECT foto_id, animal_id, caminho_foto, foto_principal FROM FOTO_ANIMAL WHERE foto_id = :foto_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':foto_id', $fotoId, PDO::PARAM_INT);
        $stmt->execute();

        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ?: null;
    }

    // Usado por: AnimalService::removerFoto()
    public function removerFotoPorId(int $fotoId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM FOTO_ANIMAL WHERE foto_id = :foto_id");
        $stmt->bindValue(':foto_id', $fotoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Usado por: AnimalService::definirFotoPrincipal() — zera a principal antiga e promove a nova,
    // nas duas UPDATEs de uma única transação implícita (o service já roda dentro de uma).
    public function definirFotoPrincipal(int $animalId, int $fotoId): bool
    {
        $stmtLimpa = $this->db->prepare("UPDATE FOTO_ANIMAL SET foto_principal = 0 WHERE animal_id = :animal_id");
        $stmtLimpa->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmtLimpa->execute();

        $stmtPromove = $this->db->prepare("UPDATE FOTO_ANIMAL SET foto_principal = 1 WHERE foto_id = :foto_id AND animal_id = :animal_id");
        $stmtPromove->bindValue(':foto_id', $fotoId, PDO::PARAM_INT);
        $stmtPromove->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmtPromove->execute();

        return $stmtPromove->rowCount() > 0;
    }

    /**
     * Apaga todas as fotos do animal no banco e devolve os caminhos físicos pra quem chamou
     * fazer o unlink() — usado na desativação do animal (limpeza de arquivos órfãos).
     */
    // Usado por: AnimalService::desativarAnimal()
    public function removerTodasFotos(int $animalId): array
    {
        $caminhos = array_column($this->buscarFotosPorAnimal($animalId), 'caminho_foto');

        $stmt = $this->db->prepare("DELETE FROM FOTO_ANIMAL WHERE animal_id = :animal_id");
        $stmt->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmt->execute();

        return $caminhos;
    }

    // Usado por: AnimalRepository::cadastrarAnimal() e editarAnimal() (uso interno)
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

    // Usado por: AnimalRepository::buscarPorId() e listarComFiltros() (uso interno)
    private function mapAnimal(array $row): Animal
    {
        $animal = new Animal();
        $animal->setAnimalId((int) $row['animal_id']);
        $animal->setProtetorId((int) $row['protetor_id']);
        $animal->setRacaId((int) $row['raca_id']);
        $animal->setRacaNome($row['raca_nome'] ?? null);
        $animal->setFotoPrincipal($row['foto_principal'] ?? null);
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

        return $animal;
    }
}
