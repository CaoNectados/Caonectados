<?php

namespace app\services;

use app\models\Especies;
use app\repositories\EspeciesRepository;
use InvalidArgumentException;

class EspeciesService
{
    private EspeciesRepository $repository;

    public function __construct(EspeciesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listarTodas(string $status = 'todos'): array
    {
        return $this->repository->listarTodas($status);
    }

    public function buscarPorId(int $id): ?Especies
    {
        return $this->repository->buscarPorId($id);
    }

    public function cadastrar(Especies $especie): bool
    {
        if (trim($especie->getNome()) === '') {
            throw new InvalidArgumentException("O nome da espécie é obrigatório.");
        }
        return $this->repository->cadastrar($especie);
    }

    public function atualizar(Especies $especie): bool
    {
        if ($especie->getId() <= 0 || trim($especie->getNome()) === '') {
            throw new InvalidArgumentException("Dados inválidos para atualizar espécie.");
        }
        return $this->repository->atualizar($especie);
    }

    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }

    public function reativar(int $id): bool
    {
        return $this->repository->reativar($id);
    }
}
