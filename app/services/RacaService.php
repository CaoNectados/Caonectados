<?php

namespace app\services;

use app\models\Raca;
use app\repositories\RacaRepository;
use app\repositories\EspecieRepository;
use InvalidArgumentException;

class RacaService
{
    private RacaRepository $repository;
    private string $dogApiKey = 'live_ixYSWbjGA97zCwFVQ30KVxvNyCrXlIJWxVcJRgnMnAmtnr96Q0pwth20JGBIm1LD';
    private string $catApiKey = 'live_gcPUybmq8t9eNB1iOjDnHPGLfqUOMHtiQaY03pR6sVpRYePIMTQdtRthIt3lDP9K';

    public function __construct(RacaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listarTodas(string $status = 'todos'): array
    {
        return $this->repository->listarTodas($status);
    }

    public function buscarPorId(int $id): ?Raca
    {
        return $this->repository->buscarPorId($id);
    }

    public function cadastrar(Raca $raca): bool
    {
        if (trim($raca->getNome()) === '' || $raca->getEspecieId() <= 0) {
            throw new InvalidArgumentException("Nome da raça e espécie são obrigatórios.");
        }
        return $this->repository->cadastrar($raca);
    }

    public function atualizar(Raca $raca): bool
    {
        if ($raca->getId() <= 0 || trim($raca->getNome()) === '' || $raca->getEspecieId() <= 0) {
            throw new InvalidArgumentException("Dados inválidos para atualizar raça.");
        }
        return $this->repository->atualizar($raca);
    }

    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }

    public function importarDeApisExternas(EspecieRepository $especieRepo): array
    {
        $cao = $especieRepo->buscarOuCriarPorNome('Cão');
        $gato = $especieRepo->buscarOuCriarPorNome('Gato');

        $novosCadastros = 0;

        // API Cães
        $dogHeaders = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36",
            "x-api-key: " . $this->dogApiKey
        ];

        $dogContext = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $dogHeaders) . "\r\n"
            ]
        ]);

        $dogsJson = @file_get_contents('https://api.thedogapi.com/v1/breeds', false, $dogContext);

        if ($dogsJson !== false) {
            $dogs = json_decode($dogsJson, true);
            if (is_array($dogs)) {
                foreach ($dogs as $dog) {
                    $nomeRaca = trim($dog['name'] ?? '');
                    if ($nomeRaca !== '' && !$this->repository->existePorNomeEEspecie($nomeRaca, $cao->getId())) {
                        $raca = new Raca();
                        $raca->setNome($nomeRaca);
                        $raca->setEspecieId($cao->getId());
                        $this->repository->cadastrar($raca);
                        $novosCadastros++;
                    }
                }
            }
        }
        
        // API Gatos
        $catHeaders = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36",
            "x-api-key: " . $this->catApiKey
        ];

        $catContext = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $catHeaders) . "\r\n"
            ]
        ]);
        $catsJson = @file_get_contents('https://api.thecatapi.com/v1/breeds', false, $catContext);
        
        if ($catsJson !== false) {
            $cats = json_decode($catsJson, true);
            if (is_array($cats)) {
                foreach ($cats as $cat) {
                    $nomeRaca = trim($cat['name'] ?? '');
                    if ($nomeRaca !== '' && !$this->repository->existePorNomeEEspecie($nomeRaca, $gato->getId())) {
                        $raca = new Raca();
                        $raca->setNome($nomeRaca);
                        $raca->setEspecieId($gato->getId());
                        $this->repository->cadastrar($raca);
                        $novosCadastros++;
                    }
                }
            }
        }

        return ['sucesso' => true, 'total' => $novosCadastros];
    }

    public function reativar(int $id): bool
    {
        return $this->repository->reativar($id);
    }
}