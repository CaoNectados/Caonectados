<?php

namespace app\services;

use app\models\Animal;
use app\repositories\AnimalRepository;
use DateTime;
use InvalidArgumentException;

class AnimalService
{
    private AnimalRepository $animalRepository;
    private UploadService $uploadService;
    private array $erros = [];

    public function __construct(AnimalRepository $animalRepository, ?UploadService $uploadService = null)
    {
        $this->animalRepository = $animalRepository;
        $this->uploadService = $uploadService ?? new UploadService();
    }

    /**
     * Salva/substitui a foto principal do animal. Aceita tanto um array de $_FILES quanto uma string Base64.
     */
    public function salvarFoto($arquivoOuBase64, int $animalId): ?string
    {
        if (empty($arquivoOuBase64) || $animalId <= 0) {
            return null;
        }

        $caminhoFoto = $this->uploadService->salvar($arquivoOuBase64, 'animal');

        if ($caminhoFoto) {
            $this->animalRepository->salvarFotoPrincipal($animalId, $caminhoFoto);
        }

        return $caminhoFoto;
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    public function buscarPorId(int $id): ?Animal
    {
        return $this->animalRepository->buscarPorId($id);
    }

    public function listarComFiltros(string $tipoPerfil, int $protetorId, string $status = 'todos'): array
    {
        return $this->animalRepository->listarComFiltros($tipoPerfil, $protetorId, $status);
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

        $atualizado = $this->animalRepository->editarAnimal($animal);

        if (!$atualizado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível atualizar.');
        }
    }

    public function atualizarStatus(Animal $animal): void
    {
        $this->validarStatus($animal->getStatus());

        if (!empty($this->erros)) {
            throw new InvalidArgumentException(implode(' ', $this->erros));
        }

        $atualizado = $this->animalRepository->alterarStatus($animal->getAnimalId(), $animal->getStatus());

        if (!$atualizado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível atualizar o status.');
        }
    }

    public function desativarAnimal(Animal $animal): void
    {
        $excluido = $this->animalRepository->excluirLogico($animal->getAnimalId());

        if (!$excluido) {
            throw new \RuntimeException('Animal não encontrado ou já está excluído.');
        }
    }

    public function reativarAnimal(Animal $animal): void
    {
        $reativado = $this->animalRepository->reativarAnimal($animal->getAnimalId());

        if (!$reativado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível reativar.');
        }
    }

    private function validarAnimal(Animal $animal): void
    {
        $this->erros = [];

        $this->validarProtetor($animal->getProtetorId());
        $this->validarNome($animal->getNome());
        $this->validarRaca($animal->getRacaId());
        $this->validarSexo($animal->getSexo());
        $this->validarPorte($animal->getPorte());
        $this->validarStatus($animal->getStatus());
        $this->validarDataNascimento($animal->getDtNasc());

        if (!empty($this->erros)) {
            throw new InvalidArgumentException('Por favor, corrija os erros do formulário.');
        }
    }

    private function validarProtetor(int $protetorId): void
    {
        if ($protetorId <= 0) {
            $this->erros['protetor_id'] = 'O protetor responsável é obrigatório e deve ser válido.';
        }
    }

    private function validarNome(?string $nome): void
    {
        if (trim((string) $nome) === '') {
            throw new InvalidArgumentException('O nome do animal é obrigatório.');
        }
    }

    private function validarRaca(int $racaId): void
    {
        if ($racaId <= 0) {
            $this->erros['raca_id'] = 'Selecione uma raça válida.';
        }
    }

    private function validarSexo(?string $sexo): void
    {
        $sexosPermitidos = ['macho', 'femea', 'indefinido'];
        if (trim((string) $sexo) === '' || !in_array($sexo, $sexosPermitidos, true)) {
            $this->erros['sexo'] = 'Selecione uma opção de sexo válida.';
        }
    }

    private function validarPorte(?string $porte): void
    {
        $portesPermitidos = ['pequeno', 'medio', 'grande'];
        if (trim((string) $porte) === '' || !in_array($porte, $portesPermitidos, true)) {
            $this->erros['porte'] = 'Selecione um porte válido.';
        }
    }

    private function validarStatus(?string $status): void
    {
        $statusPermitidos = ['disponivel', 'em_analise', 'adotado', 'desativado'];
        if (!in_array($status, $statusPermitidos, true)) {
            $this->erros['status'] = 'Selecione um status válido.';
        }
    }

    private function validarDataNascimento(?string $dataNascimento): void
    {
        // Campo opcional (dt_nasc é NULL-able no banco e não é obrigatório no formulário).
        if ($dataNascimento === null || trim($dataNascimento) === '') {
            return;
        }

        $data = DateTime::createFromFormat('Y-m-d', $dataNascimento);

        if ($data === false || $data->format('Y-m-d') !== $dataNascimento) {
            $this->erros['dt_nasc'] = 'A data de nascimento informada é inválida.';
        } else {
            $hoje = new DateTime('today');
            if ($data > $hoje) {
                $this->erros['dt_nasc'] = 'A data de nascimento não pode ser uma data futura.';
            }
        }
    }
}