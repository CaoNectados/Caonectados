<?php

namespace app\controllers;

use app\core\Controller;
use app\models\Animais;
use app\repositories\AnimaisRepository;
use app\services\AnimaisService;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class AnimaisController extends Controller
{
    private AnimaisService $service;
    private PDO $db;

    public function __construct()
    {
        $this->db = new PDO(
            'mysql:host=localhost;dbname=caonectados;charset=utf8mb4',
            'root',
            ''
        );

        $repository = new AnimaisRepository($this->db);
        $this->service = new AnimaisService($repository);
    }

    public function index(): void
    {
        try {
            $repository = new AnimaisRepository($this->db);
            $animais = $repository->listarAnimais();

            $this->sendJson(200, [
                'success' => true,
                'data' => array_map(function (Animais $animal): array {
                    return $this->animalToArray($animal);
                }, $animais)
            ]);
        } catch (RuntimeException $e) {
            $this->sendJson(500, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function create(): void
    {
        $this->sendJson(200, [
            'success' => true,
            'message' => 'Endpoint de criação pronto.'
        ]);
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonBody();

            $animal = $this->buildAnimalFromArray($data);
            $this->service->cadastrarAnimal($animal);

            $this->sendJson(201, [
                'success' => true,
                'message' => 'Animal cadastrado com sucesso.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (RuntimeException $e) {
            $this->sendJson(500, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show(): void
    {
        try {
            $id = $this->getIdFromRequest();
            $repository = new AnimaisRepository($this->db);
            $animal = $repository->buscarPorId($id);

            if ($animal === null) {
                $this->sendJson(404, [
                    'success' => false,
                    'message' => 'Animal não encontrado.'
                ]);
                return;
            }

            $this->sendJson(200, [
                'success' => true,
                'data' => $this->animalToArray($animal)
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(400, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function edit(): void
    {
        $this->sendJson(200, [
            'success' => true,
            'message' => 'Endpoint de edição pronto.'
        ]);
    }

    public function update(): void
    {
        try {
            $id = $this->getIdFromRequest();
            $data = $this->getJsonBody();

            $animal = $this->buildAnimalFromArray($data);
            $animal->setAnimalId($id);
            $this->service->editarAnimal($animal);

            $this->sendJson(200, [
                'success' => true,
                'message' => 'Animal atualizado com sucesso.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (RuntimeException $e) {
            $this->sendJson(500, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function status(): void
    {
        try {
            $id = $this->getIdFromRequest();
            $data = $this->getJsonBody();

            $status = $data['status'] ?? null;
            if (!is_string($status) || trim($status) === '') {
                throw new InvalidArgumentException('O status é obrigatório.');
            }

            $animal = new Animais();
            $animal->setAnimalId($id);
            $animal->setStatus($status);
            $this->service->atualizarStatus($animal);

            $this->sendJson(200, [
                'success' => true,
                'message' => 'Status atualizado com sucesso.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function reativar(): void
    {
        try {
            $id = $this->getIdFromRequest();

            $animal = new Animais();
            $animal->setAnimalId($id);
            $this->service->reativarAnimal($animal);

            $this->sendJson(200, [
                'success' => true,
                'message' => 'Animal reativado com sucesso.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy(): void
    {
        try {
            $id = $this->getIdFromRequest();

            $animal = new Animais();
            $animal->setAnimalId($id);
            $this->service->desativarAnimal($animal);

            $this->sendJson(200, [
                'success' => true,
                'message' => 'Animal excluído com sucesso.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function buildAnimalFromArray(?array $data): Animais
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Os dados enviados são inválidos.');
        }

        $animal = new Animais();
        $animal->setProtetorId((int) ($data['protetor_id'] ?? 0));
        $animal->setRacaId((int) ($data['raca_id'] ?? 0));
        $animal->setNome((string) ($data['nome'] ?? ''));
        $animal->setDtNasc($data['dt_nasc'] ?? null);
        $animal->setSexo((string) ($data['sexo'] ?? ''));
        $animal->setPorte((string) ($data['porte'] ?? ''));
        $animal->setStatus((string) ($data['status'] ?? 'disponivel'));
        $animal->setDescricao((string) ($data['descricao'] ?? ''));
        $animal->setVacinado((bool) ($data['vacinado'] ?? false));
        $animal->setCastrado((bool) ($data['castrado'] ?? false));
        $animal->setComportamento($data['comportamento'] ?? null);
        $animal->setHistoricoSaude($data['historico_saude'] ?? null);

        return $animal;
    }

    private function animalToArray(Animais $animal): array
    {
        return [
            'animal_id' => $animal->getAnimalId(),
            'protetor_id' => $animal->getProtetorId(),
            'raca_id' => $animal->getRacaId(),
            'nome' => $animal->getNome(),
            'dt_nasc' => $animal->getDtNasc(),
            'sexo' => $animal->getSexo(),
            'porte' => $animal->getPorte(),
            'status' => $animal->getStatus(),
            'descricao' => $animal->getDescricao(),
            'vacinado' => $animal->isVacinado(),
            'castrado' => $animal->isCastrado(),
            'comportamento' => $animal->getComportamento(),
            'historico_saude' => $animal->getHistoricoSaude(),
            'criado_em' => $animal->getCriadoEm(),
            'deletado_em' => $animal->getDeletadoEm(),
            'atualizado_em' => $animal->getAtualizadoEm(),
        ];
    }

    private function getJsonBody(): array
    {
        $rawBody = file_get_contents('php://input');

        if ($rawBody === '') {
            $data = $_POST;
        } else {
            $data = json_decode($rawBody, true);

            if (!is_array($data)) {
                parse_str($rawBody, $data);
            }
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Corpo da requisição inválido.');
        }

        return $data;
    }

    private function getIdFromRequest(): int
    {
        $id = $_GET['id'] ?? $_POST['id'] ?? null;

        if ($id === null) {
            $rawBody = file_get_contents('php://input');
            if ($rawBody !== '') {
                $data = json_decode($rawBody, true);
                if (is_array($data) && isset($data['id'])) {
                    $id = $data['id'];
                }
            }
        }

        if (!is_numeric($id) || (int) $id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        return (int) $id;
    }

    private function sendJson(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

}