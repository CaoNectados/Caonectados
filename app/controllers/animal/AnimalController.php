<?php 

namespace app\controllers\animal;

use app\core\Controller;
use app\models\Animal;
use app\repositories\AnimalRepository;
use app\services\AnimalService;
use app\database\ConnectionFactory;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class AnimalController extends Controller
{
    private AnimalService $service;
    private PDO $db;

    public function __construct()
    {
        $this->db = ConnectionFactory::getConnection();
        $repository = new AnimalRepository($this->db);
        $this->service = new AnimalService($repository);
    }

    public function index(): void
    {
        try {
            $repository = new AnimalRepository($this->db);
            $animais = $repository->listarAnimal();

            // Se for requisição por View HTML
            if ($this->isHtmlRequest()) {
                $this->view('animal/index', ['animais' => $animais]);
                return;
            }

            // Se for requisição por API JSON
            $this->sendJson(200, [
                'success' => true,
                'data' => array_map(function (Animal $animal): array {
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
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        if ($this->isHtmlRequest()) {
            $this->view('animal/cadastrar');
            return;
        }

        $this->sendJson(200, [
            'success' => true,
            'message' => 'Endpoint de criação pronto.'
        ]);
    }

    public function store(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $data = $this->getJsonBody();

            $animal = $this->buildAnimalFromArray($data);
            $this->service->cadastrarAnimal($animal);

            if ($this->isHtmlRequest()) {
                $this->redirecionarComMensagem('sucesso', 'Animal cadastrado com sucesso!', '/animal');
                return;
            }

            $this->sendJson(201, [
                'success' => true,
                'message' => 'Animal cadastrado com sucesso.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, ['success' => false, 'message' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            $this->sendJson(500, ['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function show(): void
    {
        try {
            $id = $this->getIdFromRequest();
            $repository = new AnimalRepository($this->db);
            $animal = $repository->buscarPorId($id);

            if ($animal === null) {
                if ($this->isHtmlRequest()) {
                    $this->redirecionarComMensagem('aviso', 'Animal não encontrado.', '/animal');
                    return;
                }
                $this->sendJson(404, ['success' => false, 'message' => 'Animal não encontrado.']);
                return;
            }

            if ($this->isHtmlRequest()) {
                $this->view('animal/detalhes', ['animal' => $animal]);
                return;
            }

            $this->sendJson(200, [
                'success' => true,
                'data' => $this->animalToArray($animal)
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(400, ['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function edit(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        
        $id = $this->getIdFromRequest();
        $repository = new AnimalRepository($this->db);
        $animal = $repository->buscarPorId($id);

        if (!$animal) {
            $this->redirecionarComMensagem('aviso', 'Animal não encontrado.', '/animal');
        }

        if ($this->isHtmlRequest()) {
            $this->view('animal/editar', ['animal' => $animal]);
            return;
        }

        $this->sendJson(200, ['success' => true, 'message' => 'Endpoint de edição pronto.']);
    }

    public function update(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();
            $data = $this->getJsonBody();

            $animal = $this->buildAnimalFromArray($data);
            $animal->setAnimalId($id);
            $this->service->editarAnimal($animal);

            if ($this->isHtmlRequest()) {
                $this->redirecionarComMensagem('sucesso', 'Animal atualizado com sucesso!', '/animal');
                return;
            }

            $this->sendJson(200, ['success' => true, 'message' => 'Animal atualizado com sucesso.']);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, ['success' => false, 'message' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            $this->sendJson(500, ['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function status(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();
            $data = $this->getJsonBody();

            $status = $data['status'] ?? null;
            if (!is_string($status) || trim($status) === '') {
                throw new InvalidArgumentException('O status é obrigatório.');
            }

            $animal = new Animal();
            $animal->setAnimalId($id);
            $animal->setStatus($status);
            $this->service->atualizarStatus($animal);

            $this->sendJson(200, ['success' => true, 'message' => 'Status atualizado com sucesso.']);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, ['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function reativar(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();

            $animal = new Animal();
            $animal->setAnimalId($id);
            $this->service->reativarAnimal($animal);

            $this->sendJson(200, ['success' => true, 'message' => 'Animal reativado com sucesso.']);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, ['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();

            $animal = new Animal();
            $animal->setAnimalId($id);
            $this->service->desativarAnimal($animal);

            if ($this->isHtmlRequest()) {
                $this->redirecionarComMensagem('sucesso', 'Animal excluído com sucesso!', '/animal');
                return;
            }

            $this->sendJson(200, ['success' => true, 'message' => 'Animal excluído com sucesso.']);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(422, ['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function isHtmlRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'text/html') !== false;
    }

    private function buildAnimalFromArray(?array $data): Animal
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Os dados enviados são inválidos.');
        }

        $animal = new Animal();
        $animal->setProtetorId((int) ($data['protetor_id'] ?? $_SESSION['protetor_id'] ?? 0));
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

    private function animalToArray(Animal $animal): array
    {
        return [
            'animal_id'       => $animal->getAnimalId(),
            'protetor_id'     => $animal->getProtetorId(),
            'raca_id'         => $animal->getRacaId(),
            'nome'            => $animal->getNome(),
            'dt_nasc'          => $animal->getDtNasc(),
            'sexo'            => $animal->getSexo(),
            'porte'           => $animal->getPorte(),
            'status'          => $animal->getStatus(),
            'descricao'       => $animal->getDescricao(),
            'vacinado'        => $animal->isVacinado(),
            'castrado'        => $animal->isCastrado(),
            'comportamento'   => $animal->getComportamento(),
            'historico_saude' => $animal->getHistoricoSaude(),
            'criado_em'       => $animal->getCriadoEm(),
            'deletado_em'     => $animal->getDeletadoEm(),
            'atualizado_em'   => $animal->getAtualizadoEm(),
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