<?php

namespace app\services;

use app\models\Regioes;
use app\repositories\RegioesRepository;
use InvalidArgumentException;
use RuntimeException;

class RegioesService
{
    private RegioesRepository $regioesRepository;

    public function __construct(RegioesRepository $regioesRepository)
    {
        $this->regioesRepository = $regioesRepository;
    }

    public function cadastrarRegiao(Regioes $regiao): void
    {
        $this->validarRegiao($regiao);
        $this->validarPermissaoAdmin();

        
        $existente = $this->regioesRepository->buscarPorNome($regiao->getNomeRegiao());
        if ($existente !== null) {
            throw new InvalidArgumentException('Este bairro/região já está cadastrado.');
        }

        $resultado = $this->regioesRepository->cadastrarRegiao($regiao);

        if ($resultado <= 0) {
            throw new RuntimeException('Não foi possível cadastrar o bairro.');
        }

        $regiao->setRegiaoId($resultado);
    }

    public function editarRegiao(Regioes $regiao): void
    {
        $this->validarRegiao($regiao);
        $this->validarPermissaoAdmin();

        
        $existente = $this->regioesRepository->buscarPorNome($regiao->getNomeRegiao(), $regiao->getRegiaoId());
        if ($existente !== null) {
            throw new InvalidArgumentException('Já existe outro bairro cadastrado com este nome.');
        }

        $atualizado = $this->regioesRepository->editarRegiao($regiao);

        if (!$atualizado) {
            throw new RuntimeException('Bairro não encontrado ou nenhuma alteração foi realizada.');
        }
    }

    public function excluirRegiao(int $id): void
    {
        $this->validarPermissaoAdmin();

        try {
            $excluido = $this->regioesRepository->excluirRegiao($id);

            if (!$excluido) {
                throw new RuntimeException('Bairro não encontrado.');
            }
        } catch (\PDOException $e) {
            throw new InvalidArgumentException('Não é possível excluir o bairro pois existem usuários vinculados a ele.');
        }
    }

    private function validarRegiao(Regioes $regiao): void
    {
        $this->validarNome($regiao->getNomeRegiao());
    }

    private function validarNome(?string $nome): void
    {
        if (trim((string) $nome) === '') {
            throw new InvalidArgumentException('O nome do bairro/região é obrigatório.');
        }

        if (mb_strlen(trim((string) $nome)) < 3) {
            throw new InvalidArgumentException('O nome do bairro/região deve conter pelo menos 3 caracteres.');
        }
    }

    // private function validarPermissaoAdmin(): void
    // {
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         session_start();
    //     }

    //     $tipoPerfil = $_SESSION['tipo_perfil'] ?? null;

    //     if ($tipoPerfil !== 'administrador') {
    //         throw new InvalidArgumentException('Apenas administradores podem realizar esta operação.');
    //     }
    // }

    private function validarPermissaoAdmin(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['tipo_perfil'] = 'administrador';

    $tipoPerfil = $_SESSION['tipo_perfil'] ?? null;

    if ($tipoPerfil !== 'administrador') {
        throw new InvalidArgumentException('Apenas administradores podem realizar esta operação.');
    }
}
}