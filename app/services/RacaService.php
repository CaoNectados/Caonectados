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

    // Usado por: RacaController (listagem/filtro de raças)
    public function listarTodas(string $status = 'todos'): array
    {
        return $this->repository->listarTodas($status);
    }

    // Usado por: RacaController (edição/exclusão de raça)
    public function buscarPorId(int $id): ?Raca
    {
        return $this->repository->buscarPorId($id);
    }

    // Usado por: RacaController::store
    public function cadastrar(Raca $raca): bool
    {
        if (trim($raca->getNome()) === '' || $raca->getEspecieId() <= 0) {
            throw new InvalidArgumentException("Nome da raça e espécie são obrigatórios.");
        }
        return $this->repository->cadastrar($raca);
    }

    // Usado por: RacaController::update
    public function atualizar(Raca $raca): bool
    {
        if ($raca->getId() <= 0 || trim($raca->getNome()) === '' || $raca->getEspecieId() <= 0) {
            throw new InvalidArgumentException("Dados inválidos para atualizar raça.");
        }
        return $this->repository->atualizar($raca);
    }

    // Usado por: RacaController::destroy
    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }

    // Usado por: RacaController (reativar raça desativada)
    public function reativar(int $id): bool
    {
        return $this->repository->reativar($id);
    }

    /**
     * Retorna sugestões brutas de cães e gatos utilizando o método unificado.
     */
    // Usado por: RacaController (busca de sugestões de raças via API externa)
    public function buscarSugestoesExternas(): array
    {
        return [
            'caes' => $this->buscarDaApi('https://api.thedogapi.com/v1/breeds', $this->dogApiKey),
            'gatos' => $this->buscarDaApi('https://api.thecatapi.com/v1/breeds', $this->catApiKey)
        ];
    }

    /**
     * Cadastra em lote as raças selecionadas pelo administrador.
     */
    // Usado por: RacaController::importar
    public function importarSelecionadas(EspecieRepository $especieRepo, string $especieNome, array $racasAceitas): int
    {
        if (empty($racasAceitas)) {
            return 0;
        }

        $especie = $especieRepo->buscarOuCriarPorNome($especieNome);
        $cadastros = 0;

        foreach ($racasAceitas as $nomeRaca) {
            $nomeRaca = trim($nomeRaca);
            if ($nomeRaca !== '' && !$this->repository->existePorNomeEEspecie($nomeRaca, $especie->getId())) {
                $raca = new Raca();
                $raca->setNome($nomeRaca);
                $raca->setEspecieId($especie->getId());
                $this->repository->cadastrar($raca);
                $cadastros++;
            }
        }

        return $cadastros;
    }

    // Usado por: (não referenciado atualmente)
    public function importarDeApisExternas(EspecieRepository $especieRepo): array
    {
        $sugestoes = $this->buscarSugestoesExternas();
        $totalCaes = $this->importarSelecionadas($especieRepo, 'Cão', $sugestoes['caes']);
        $totalGatos = $this->importarSelecionadas($especieRepo, 'Gato', $sugestoes['gatos']);

        return ['sucesso' => true, 'total' => ($totalCaes + $totalGatos)];
    }

    /**
     * Método unificado e privado para consumir qualquer API externa de raças.
     */
    // Usado por: buscarSugestoesExternas()
    private function buscarDaApi(string $url, string $apiKey): array
    {
        $headers = [
            "User-Agent: Mozilla/5.0",
            "x-api-key: " . $apiKey
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 15
            ]
        ]);

        $json = @file_get_contents($url, false, $context);
        if ($json !== false) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                return array_column($data, 'name');
            }
        }

        return [];
    }
}