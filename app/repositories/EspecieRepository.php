<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Especie;
use PDO;

class EspecieRepository extends BaseRepository
{
    // Usado por: EspecieService::listarTodas() e EspecieController
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

        return array_map(fn(array $row) => $this->mapEspecie($row), $dados);
    }

    // Usado por: PerfilController::exibirPerfil()
    public function listarAtivas(): array
    {
        $sql = "SELECT especie_id, nome, ativo
                FROM ESPECIE
                WHERE ativo = 1
                ORDER BY nome ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: EspecieController::index() e OnBoardingController
    public function buscarTodas(): array
    {
        $sql = "SELECT especie_id, nome FROM ESPECIE WHERE ativo = TRUE ORDER BY nome ASC";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: AnimalController::cadastrarForm()
    public function buscarAtivas(): array
    {
        $sql = "SELECT especie_id, nome FROM ESPECIE WHERE ativo = 1 ORDER BY nome ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: EspecieService::buscarPorId() e EspecieController
    public function buscarPorId(int $especieId): ?Especie
    {
        $sql = "SELECT * FROM ESPECIE WHERE especie_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $especieId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res ? $this->mapEspecie($res) : null;
    }

    // Usado por: RacaService::importarSelecionadas()
    public function buscarOuCriarPorNome(string $nome): Especie
    {
        $sql = "SELECT * FROM ESPECIE WHERE LOWER(nome) = LOWER(:nome)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            return $this->mapEspecie($dados);
        }

        $stmtInsert = $this->db->prepare("INSERT INTO ESPECIE (nome) VALUES (:nome)");
        $stmtInsert->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmtInsert->execute();

        $especie = new Especie();
        $especie->setId((int) $this->db->lastInsertId());
        $especie->setNome($nome);
        $especie->setAtivo(true);

        return $especie;
    }

    // Usado por: EspecieService::cadastrar() e EspecieController::store()
    public function cadastrar(Especie $especie): bool
    {
        $sql = "INSERT INTO ESPECIE (nome) VALUES (:nome)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $especie->getNome(), PDO::PARAM_STR);

        return $stmt->execute();
    }

    // Usado por: EspecieService::atualizar() e EspecieController::update()
    public function atualizar(Especie $especie): bool
    {
        $sql = "UPDATE ESPECIE SET nome = :nome WHERE especie_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $especie->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $especie->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Usado por: EspecieService::excluir() e EspecieController::destroy()
    public function excluir(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtEspecie = $this->db->prepare("UPDATE ESPECIE SET ativo = FALSE WHERE especie_id = :id");
            $stmtEspecie->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtEspecie->execute();

            $stmtRacas = $this->db->prepare("UPDATE RACA SET ativo = FALSE WHERE especie_id = :id");
            $stmtRacas->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtRacas->execute();

            return $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    // Usado por: EspecieService::reativar() e EspecieController
    public function reativar(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtEspecie = $this->db->prepare("UPDATE ESPECIE SET ativo = TRUE WHERE especie_id = :id");
            $stmtEspecie->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtEspecie->execute();

            $stmtRacas = $this->db->prepare("UPDATE RACA SET ativo = TRUE WHERE especie_id = :id");
            $stmtRacas->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtRacas->execute();

            return $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    // Usado por: EspecieRepository::listarTodas(), buscarPorId() e buscarOuCriarPorNome() (uso interno)
    private function mapEspecie(array $row): Especie
    {
        $especie = new Especie();
        $especie->setId((int) $row['especie_id']);
        $especie->setNome($row['nome']);
        $especie->setAtivo((bool) $row['ativo']);

        return $especie;
    }
}
