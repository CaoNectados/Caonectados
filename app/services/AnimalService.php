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

    // Usado por: AnimalController::index
    public function buscarPorId(int $id): ?Animal
    {
        return $this->animalRepository->buscarPorId($id);
    }

    // Usado por: AnimalController::index
    public function listarComFiltros(string $tipoPerfil, int $protetorId, string $status = 'todos'): array
    {
        return $this->animalRepository->listarComFiltros($tipoPerfil, $protetorId, $status);
    }

    // Usado por: AnimalController::store
    public function cadastrarAnimal(Animal $animal): void
    {
        $this->validarAnimal($animal);

        $resultado = $this->animalRepository->cadastrarAnimal($animal);

        if ($resultado <= 0) {
            throw new \RuntimeException('Não foi possível cadastrar o animal.');
        }

        $animal->setAnimalId($resultado);
    }

    // Usado por: AnimalController::update
    public function editarAnimal(Animal $animal): void
    {
        $this->validarAnimal($animal);

        $atualizado = $this->animalRepository->editarAnimal($animal);

        if (!$atualizado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível atualizar.');
        }
    }

    // Usado por: AnimalController (alteração de status do animal)
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

    // Usado por: AnimalController::destroy
    public function desativarAnimal(Animal $animal): void
    {
        $excluido = $this->animalRepository->excluirLogico($animal->getAnimalId());

        if (!$excluido) {
            throw new \RuntimeException('Animal não encontrado ou já está excluído.');
        }

        // UC 17.3 / FA 03: a desativação é lógica (deletado_em), mas os arquivos de imagem
        // não precisam continuar ocupando espaço no servidor — remove todas as fotos do
        // animal (registro em FOTO_ANIMAL + arquivo físico em uploads/). Se o animal for
        // reativado depois, ele volta sem fotos; o protetor cadastra novas.
        $caminhos = $this->animalRepository->removerTodasFotos($animal->getAnimalId());
        foreach ($caminhos as $caminho) {
            $this->uploadService->remover($caminho);
        }
    }

    // Usado por: AnimalController (reativar animal desativado)
    public function reativarAnimal(Animal $animal): void
    {
        $reativado = $this->animalRepository->reativarAnimal($animal->getAnimalId());

        if (!$reativado) {
            throw new \RuntimeException('Animal não encontrado ou não foi possível reativar.');
        }
    }

    /**
     * Salva/substitui a foto principal do animal. Aceita tanto um array de $_FILES quanto uma string Base64.
     */
    // Usado por: AnimalController (cadastro/edição de animal)
    public function salvarFoto($arquivoOuBase64, int $animalId): ?string
    {
        if (empty($arquivoOuBase64) || $animalId <= 0) {
            return null;
        }

        $animalAtual = $this->animalRepository->buscarPorId($animalId);
        $fotoAntiga = $animalAtual?->getFotoPrincipal();

        $caminhoFoto = $this->uploadService->salvar($arquivoOuBase64, 'animal');

        if ($caminhoFoto) {
            $this->animalRepository->salvarFotoPrincipal($animalId, $caminhoFoto);

            if ($fotoAntiga && $fotoAntiga !== $caminhoFoto) {
                $this->uploadService->remover($fotoAntiga);
            }
        }

        return $caminhoFoto;
    }

    // Usado por: AnimalController (galeria de fotos no cadastro/edição, e FeedRepository
    // indiretamente via AnimalRepository na montagem do carrossel do card do feed)
    public function listarFotos(int $animalId): array
    {
        return $this->animalRepository->buscarFotosPorAnimal($animalId);
    }

    /**
     * Salva um lote de fotos adicionais (além da principal). Aceita tanto um array de
     * $_FILES (múltiplos arquivos no mesmo campo) quanto uma lista de strings Base64
     * (recorte feito no cliente), sem exigir cropper — a foto principal é que passa pelo
     * cropper existente; estas aqui só ficam armazenadas como enviadas.
     */
    // Usado por: AnimalController (cadastro/edição de animal)
    public function salvarFotosAdicionais(array $arquivosOuBase64, int $animalId): array
    {
        if ($animalId <= 0) {
            return [];
        }

        $caminhosSalvos = [];
        foreach ($arquivosOuBase64 as $arquivo) {
            if (empty($arquivo)) {
                continue;
            }

            // Item de um input múltiplo de $_FILES: PHP entrega erro UPLOAD_ERR_NO_FILE
            // pros slots vazios do array — ignora em vez de tentar salvar "nada".
            if (is_array($arquivo) && ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $caminho = $this->uploadService->salvar($arquivo, 'animal');
            if ($caminho) {
                $this->animalRepository->salvarFotoAdicional($animalId, $caminho);
                $caminhosSalvos[] = $caminho;
            }
        }

        return $caminhosSalvos;
    }

    // Usado por: AnimalController — remove uma foto específica (principal ou adicional).
    // $animalIdEsperado é sempre o animal já validado como pertencente ao protetor logado
    // (ver AnimalController::carregarEValidarPropriedade()); aqui é só uma segunda checagem
    // pra garantir que o foto_id realmente pertence a ESSE animal antes de apagar algo.
    public function removerFoto(int $fotoId, int $animalIdEsperado): void
    {
        $foto = $this->animalRepository->buscarFotoPorId($fotoId);

        if (!$foto || (int) $foto['animal_id'] !== $animalIdEsperado) {
            throw new \RuntimeException('Foto não encontrada para este animal.');
        }

        if (!$this->animalRepository->removerFotoPorId($fotoId)) {
            throw new \RuntimeException('Não foi possível remover a foto.');
        }

        $this->uploadService->remover($foto['caminho_foto']);
    }

    // Usado por: AnimalController — promove uma foto adicional a foto principal
    public function definirFotoPrincipal(int $fotoId, int $animalIdEsperado): void
    {
        $foto = $this->animalRepository->buscarFotoPorId($fotoId);

        if (!$foto || (int) $foto['animal_id'] !== $animalIdEsperado) {
            throw new \RuntimeException('Foto não encontrada para este animal.');
        }

        if (!$this->animalRepository->definirFotoPrincipal($animalIdEsperado, $fotoId)) {
            throw new \RuntimeException('Não foi possível definir a foto principal.');
        }
    }

    // Usado por: cadastrarAnimal(), editarAnimal()
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

    // Usado por: validarAnimal()
    private function validarProtetor(int $protetorId): void
    {
        if ($protetorId <= 0) {
            $this->erros['protetor_id'] = 'O protetor responsável é obrigatório e deve ser válido.';
        }
    }

    // Usado por: validarAnimal()
    private function validarNome(?string $nome): void
    {
        if (trim((string) $nome) === '') {
            throw new InvalidArgumentException('O nome do animal é obrigatório.');
        }
    }

    // Usado por: validarAnimal()
    private function validarRaca(int $racaId): void
    {
        if ($racaId <= 0) {
            $this->erros['raca_id'] = 'Selecione uma raça válida.';
        }
    }

    // Usado por: validarAnimal()
    private function validarSexo(?string $sexo): void
    {
        $sexosPermitidos = ['macho', 'femea', 'indefinido'];
        if (trim((string) $sexo) === '' || !in_array($sexo, $sexosPermitidos, true)) {
            $this->erros['sexo'] = 'Selecione uma opção de sexo válida.';
        }
    }

    // Usado por: validarAnimal()
    private function validarPorte(?string $porte): void
    {
        $portesPermitidos = ['pequeno', 'medio', 'grande'];
        if (trim((string) $porte) === '' || !in_array($porte, $portesPermitidos, true)) {
            $this->erros['porte'] = 'Selecione um porte válido.';
        }
    }

    // Usado por: validarAnimal(), atualizarStatus()
    private function validarStatus(?string $status): void
    {
        $statusPermitidos = ['disponivel', 'em_analise', 'adotado', 'desativado'];
        if (!in_array($status, $statusPermitidos, true)) {
            $this->erros['status'] = 'Selecione um status válido.';
        }
    }

    // Usado por: validarAnimal()
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