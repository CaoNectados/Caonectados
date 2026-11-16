<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\AnimalAdminRepository;
use DomainException;
use PDO;
use Throwable;

class AnimalAdminService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? ConnectionFactory::getConnection();
    }

    public function listar(string $busca, string $status, int $pagina): array
    {
        if (!in_array($status, ['todos', 'disponivel', 'em_analise', 'adotado', 'desativado'], true)) {
            throw new DomainException('Selecione um status válido.');
        }
        if (mb_strlen($busca) > 150 || $pagina < 1) throw new DomainException('Filtro inválido.');
        return (new AnimalAdminRepository($this->db))->listar(trim($busca), $status, $pagina);
    }

    public function alterarDisponibilidade(int $animalId, int $adminId, bool $reativar): void
    {
        if ($animalId < 1) throw new DomainException('Animal inválido.');
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT usuario_id FROM USUARIO WHERE usuario_id = ?
                AND status_conta = 'ativo' AND deletado_em IS NULL
                AND (tipo_atual = 'administrador' OR FIND_IN_SET('administrador', perfis_ativos)) FOR UPDATE");
            $stmt->execute([$adminId]);
            if (!$stmt->fetch()) throw new DomainException('Ação exclusiva de administrador ativo.');

            $stmt = $this->db->prepare('SELECT status, deletado_em FROM ANIMAL WHERE animal_id = ? FOR UPDATE');
            $stmt->execute([$animalId]);
            $animal = $stmt->fetch();
            if (!$animal) throw new DomainException('Animal não encontrado.');
            $anterior = $animal['status'];
            if ($reativar) {
                if (!in_array($anterior, ['adotado', 'desativado'], true)) {
                    throw new DomainException('Somente animais adotados ou desativados podem ser reativados.');
                }
                (new \app\repositories\AnimalRepository($this->db))->reativarAnimal($animalId);
            } else {
                if ($anterior === 'desativado') throw new DomainException('Este animal já está desativado.');
                (new \app\repositories\AnimalRepository($this->db))->excluirLogico($animalId);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}

