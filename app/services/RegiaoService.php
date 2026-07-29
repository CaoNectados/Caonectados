<?php

namespace app\services;

use app\models\Regiao;
use app\repositories\RegiaoRepository;
use InvalidArgumentException;
use RuntimeException;

class RegiaoService
{
    private RegiaoRepository $regiaoRepository;

    public function __construct(RegiaoRepository $regiaoRepository)
    {
        $this->regiaoRepository = $regiaoRepository;
    }

    public function cadastrarRegiao(Regiao $regiao): void
    {
        $this->validarRegiao($regiao);
        $this->validarPermissaoAdmin();

        $existente = $this->regiaoRepository->buscarPorNome($regiao->getNomeRegiao());
        if ($existente !== null) {
            throw new InvalidArgumentException('Este bairro/região já está cadastrado.');
        }

        $resultado = $this->regiaoRepository->cadastrarRegiao($regiao);

        if ($resultado <= 0) {
            throw new RuntimeException('Não foi possível cadastrar o bairro.');
        }

        $regiao->setRegiaoId($resultado);
    }

    public function editarRegiao(Regiao $regiao): void
    {
        $this->validarRegiao($regiao);
        $this->validarPermissaoAdmin();

        $existente = $this->regiaoRepository->buscarPorNome($regiao->getNomeRegiao(), $regiao->getRegiaoId());
        if ($existente !== null) {
            throw new InvalidArgumentException('Já existe outro bairro cadastrado com este nome.');
        }

        $atualizado = $this->regiaoRepository->editarRegiao($regiao);

        if (!$atualizado) {
            throw new RuntimeException('Bairro não encontrado ou nenhuma alteração foi realizada.');
        }
    }

    public function excluirRegiao(int $id): void
    {
        $this->validarPermissaoAdmin();

        try {
            $excluido = $this->regiaoRepository->excluirRegiao($id);

            if (!$excluido) {
                throw new RuntimeException('Bairro não encontrado.');
            }
        } catch (\PDOException $e) {
            throw new InvalidArgumentException('Não é possível excluir o bairro pois existem usuários vinculados a ele.');
        }
    }

    private function validarRegiao(Regiao $regiao): void
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

    private function validarPermissaoAdmin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $tipoPerfil = $_SESSION['tipo_perfil'] ?? null;

        if ($tipoPerfil !== 'administrador') {
            throw new InvalidArgumentException('Apenas administradores podem realizar esta operação.');
        }
    }
}