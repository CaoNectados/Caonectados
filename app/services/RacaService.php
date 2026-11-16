<?php

namespace app\services;

use app\models\Raca;
use app\repositories\RacaRepository;
use app\repositories\EspecieRepository;
use InvalidArgumentException;

class RacaService
{
    private RacaRepository $repository;
    private string $dogApiKey;
    private string $catApiKey;

    public function __construct(RacaRepository $repository)
    {
        $this->repository = $repository;
        $arquivoLocal = __DIR__ . '/../config/animais_api.local.php';
        $configuracao = is_file($arquivoLocal) ? require $arquivoLocal : [];
        $this->dogApiKey = getenv('DOG_API_KEY') ?: ($configuracao['dog'] ?? '');
        $this->catApiKey = getenv('CAT_API_KEY') ?: ($configuracao['cat'] ?? '');
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
        $raca->setNome(trim($raca->getNome()));
        if ($this->repository->existePorNomeEEspecie($raca->getNome(), $raca->getEspecieId())) throw new InvalidArgumentException('Este nome já está cadastrado para esta espécie.');
        ValidationService::validarTexto($raca->getNome(), 'Raça', 1, 100);
        if (!(new EspecieRepository())->buscarPorId($raca->getEspecieId())) throw new InvalidArgumentException('Selecione uma espécie cadastrada.');
        if (trim($raca->getNome()) === '' || $raca->getEspecieId() <= 0) {
            throw new InvalidArgumentException("Nome da raça e espécie são obrigatórios.");
        }
        return $this->repository->cadastrar($raca);
    }

    public function atualizar(Raca $raca): bool
    {
        $raca->setNome(trim($raca->getNome()));
        if ($this->repository->existePorNomeEEspecie($raca->getNome(), $raca->getEspecieId(), (int) $raca->getId())) throw new InvalidArgumentException('Este nome já está cadastrado para esta espécie.');
        ValidationService::validarTexto($raca->getNome(), 'Raça', 1, 100);
        if (!(new EspecieRepository())->buscarPorId($raca->getEspecieId())) throw new InvalidArgumentException('Selecione uma espécie cadastrada.');
        if ($raca->getId() <= 0 || trim($raca->getNome()) === '' || $raca->getEspecieId() <= 0) {
            throw new InvalidArgumentException("Dados inválidos para atualizar raça.");
        }
        return $this->repository->atualizar($raca);
    }

    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }

    public function reativar(int $id): bool
    {
        return $this->repository->reativar($id);
    }

    /**
     * Retorna sugestões brutas de cães e gatos utilizando o método unificado.
     */
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
    public function importarSelecionadas(EspecieRepository $especieRepo, string $especieNome, array $racasAceitas): int
    {
        if (empty($racasAceitas)) {
            return 0;
        }

        ValidationService::validarTexto($especieNome, 'Espécie', 1, 100);
        foreach ($racasAceitas as $nome) ValidationService::validarTexto($nome, 'Raça', 1, 100);
        $db = \app\database\ConnectionFactory::getConnection();
        $propria = !$db->inTransaction();
        if ($propria) $db->beginTransaction();
        try {
            $especie = $especieRepo->buscarOuCriarPorNome(trim($especieNome));
            $cadastros = 0;
            foreach (array_unique(array_map('trim', $racasAceitas)) as $nomeRaca) {
                if ($nomeRaca === '' || $this->repository->existePorNomeEEspecie($nomeRaca, $especie->getId())) continue;
                $raca = new Raca();
                $raca->setNome($nomeRaca);
                $raca->setEspecieId($especie->getId());
                $this->repository->cadastrar($raca);
                $cadastros++;
            }
            if ($propria) $db->commit();
            return $cadastros;
        } catch (\Throwable $e) {
            if ($propria && $db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * Método unificado e privado para consumir qualquer API externa de raças.
     */
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
