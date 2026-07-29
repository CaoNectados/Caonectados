<?php

namespace app\services;

use app\models\Animal; 
use app\repositories\AnimalRepository;
use DateTime;
use InvalidArgumentException;

class AnimalService
{
    private AnimalRepository $animalRepository;

    public function __construct(AnimalRepository $animalRepository)
    {
        $this->animalRepository = $animalRepository;
    }

    public function cadastrarAnimal(Animal $animal): void
    {
        $this->validarAnimal($animal);

        $resultado = $this->animalRepository->cadastrarAnimal($animal);

        if ($resultado <= 0) {
            throw new \RuntimeException('Não foi possível cadastrar o animal.');
        }

        $animal->setAnimalId($resultado);
    }

    public function editarAnimal(Animal $animal): void
    {
        $this->validarAnimal($animal);
        $this->validarPermissaoDoProtetor($animal);

        $atualizado = $this->animalRepository->editarAnimal($animal);

        if (!$atualizado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível atualizar.');
        }
    }

    public function atualizarStatus(Animal $animal): void
    {
        $this->validarStatus($animal->getStatus());
        $this->validarPermissaoDoProtetor($animal);

        $atualizado = $this->animalRepository->alterarStatus($animal->getAnimalId(), $animal->getStatus());

        if (!$atualizado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível atualizar o status.');
        }
    }

    public function desativarAnimal(Animal $animal): void
    {
        $this->validarPermissaoDoProtetor($animal);

        $excluido = $this->animalRepository->excluirLogico($animal->getAnimalId());

        if (!$excluido) {
            throw new \RuntimeException('Animal não encontrado ou já está excluído.');
        }
    }

    public function reativarAnimal(Animal $animal): void
    {
        $this->validarPermissaoDoProtetor($animal);

        $reativado = $this->animalRepository->reativarAnimal($animal->getAnimalId());

        if (!$reativado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível reativar.');
        }
    }

    private function validarAnimal(Animal $animal): void
    {
        $this->validarNome($animal->getNome());
        $this->validarDescricao($animal->getDescricao());
        $this->validarRaca($animal->getRacaId());
        $this->validarSexo($animal->getSexo());
        $this->validarPorte($animal->getPorte());
        $this->validarStatus($animal->getStatus());
        $this->validarDataNascimento($animal->getDtNasc());
    }

    private function validarNome(?string $nome): void
    {
        if (trim((string) $nome) === '') {
            throw new InvalidArgumentException('O nome do animal é obrigatório.');
        }
    }

    private function validarDescricao(?string $descricao): void
    {
        if (trim((string) $descricao) === '') {
            throw new InvalidArgumentException('A descrição do animal é obrigatória.');
        }
    }

    private function validarRaca(int $racaId): void
    {
        if ($racaId <= 0) {
            throw new InvalidArgumentException('A raça do animal é obrigatória.');
        }
    }

    private function validarSexo(?string $sexo): void
    {
        if (trim((string) $sexo) === '') {
            throw new InvalidArgumentException('O sexo do animal é obrigatório.');
        }
    }

    private function validarPorte(?string $porte): void
    {
        if (trim((string) $porte) === '') {
            throw new InvalidArgumentException('O porte do animal é obrigatório.');
        }
    }

    private function validarStatus(?string $status): void
    {
        $statusPermitidos = ['disponivel', 'em_analise', 'adotado', 'desativado']; 

        if (!in_array($status, $statusPermitidos, true)) {
            throw new InvalidArgumentException('Status inválido. Os valores permitidos são: disponivel, em_analise, adotado e desativado.');
        }
    }

    private function validarDataNascimento(?string $dataNascimento): void
    {
        if ($dataNascimento === null || trim($dataNascimento) === '') {
            return;
        }

        $data = DateTime::createFromFormat('Y-m-d', $dataNascimento);

        if ($data === false || $data->format('Y-m-d') !== $dataNascimento) {
            throw new InvalidArgumentException('A data de nascimento informada é inválida.');
        }

        $hoje = new DateTime('today');

        if ($data > $hoje) {
            throw new InvalidArgumentException('A data de nascimento não pode ser futura.');
        }
    }

    private function validarPermissaoDoProtetor(Animal $animal): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $protetorId = $_SESSION['protetor_id'] ?? null;
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? $_SESSION['tipo_perfil'] ?? null;

        if ($usuarioId === null && $protetorId === null) {
            throw new InvalidArgumentException('Usuário não autenticado.');
        }

        if ($tipoPerfil === 'administrador') {
            return;
        }

        $animalBanco = $this->animalRepository->buscarPorId($animal->getAnimalId() ?? 0);

        if ($animalBanco === null) {
            throw new InvalidArgumentException('Animal não encontrado.');
        }

        $ownerIds = array_filter([(int) $protetorId, (int) $usuarioId], static function ($value): bool {
            return $value > 0;
        });

        if ($ownerIds === [] || !in_array((int) $animalBanco->getProtetorId(), $ownerIds, true)) {
            throw new InvalidArgumentException('Somente o protetor dono do animal pode realizar esta ação.');
        }
    }
}