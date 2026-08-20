<?php

namespace app\services;

use app\models\Especie;
use app\repositories\EspecieRepository;
use InvalidArgumentException;

class EspecieService
{
    private EspecieRepository $repository;

    public function __construct(EspecieRepository $repository)
    {
        $this->repository = $repository;
    }

    // Usado por: EspecieController::index, RacaController (listagem/filtro de espécies)
    public function listarTodas(string $status = 'todos'): array
    {
        return $this->repository->listarTodas($status);
    }

    // Usado por: EspecieController (edição/exclusão de espécie)
    public function buscarPorId(int $id): ?Especie
    {
        return $this->repository->buscarPorId($id);
    }

    // Usado por: EspecieController::store (cadastro de espécie)
    public function cadastrar(Especie $especie): bool
    {
        if (trim($especie->getNome()) === '') {
            throw new InvalidArgumentException("O nome da espécie é obrigatório.");
        }
        return $this->repository->cadastrar($especie);
    }

    // Usado por: EspecieController::update
    public function atualizar(Especie $especie): bool
    {
        if ($especie->getId() <= 0 || trim($especie->getNome()) === '') {
            throw new InvalidArgumentException("Dados inválidos para atualizar espécie.");
        }
        return $this->repository->atualizar($especie);
    }

    // Usado por: EspecieController::destroy
    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }

    // Usado por: EspecieController (reativar espécie desativada)
    public function reativar(int $id): bool
    {
        return $this->repository->reativar($id);
    }
}