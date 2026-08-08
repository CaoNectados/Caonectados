<?php 

namespace app\controllers\animal;

use app\core\Controller;
use app\models\Animal;
use app\repositories\AnimalRepository;
use app\services\AnimalService;
use app\database\ConnectionFactory;
use PDO;
use Exception;

class AnimalController extends Controller
{
    private AnimalService $service;
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = ConnectionFactory::getConnection();
        $repository = new AnimalRepository($this->db);
        $this->service = new AnimalService($repository);
    }

    public function index(): void
    {
        try {
            $repository = new AnimalRepository($this->db);
            $animais = $repository->listarAnimal();
                // Se for requisição por View HTML: armazenar tudo em session
                if ($this->isHtmlRequest()) {
                    $_SESSION['animais'] = $animais;
                    $this->view('animal/index');
                    return;
                }

                // Se for requisição por API JSON: padrão { status, data }
                $this->json(200, [
                    'status' => 'sucesso',
                    'data' => array_map(function (Animal $animal): array {
                        return $this->animalToArray($animal);
                    }, $animais)
                ]);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function create(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        if ($this->isHtmlRequest()) {
            $this->view('animal/cadastrar');
            return;
        }

            $this->json(200, [
                'status' => 'sucesso',
                'mensagem' => 'Endpoint de criação pronto.'
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
                $this->json(201, [
                    'status' => 'sucesso',
                    'mensagem' => 'Animal cadastrado com sucesso.'
                ]);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
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

                $this->json(404, ['status' => 'erro', 'mensagem' => 'Animal não encontrado.']);
                return;
            }

            if ($this->isHtmlRequest()) {
                $_SESSION['animal'] = $animal;
                $this->view('animal/detalhes');
                return;
            }

            $this->json(200, [
                'status' => 'sucesso',
                'data' => $this->animalToArray($animal)
            ]);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
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
            $_SESSION['animal'] = $animal;
            $this->view('animal/editar');
            return;
        }

        $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Endpoint de edição pronto.']);
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

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Animal atualizado com sucesso.']);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
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
                throw new Exception('O status é obrigatório.');
            }

            $animal = new Animal();
            $animal->setAnimalId($id);
            $animal->setStatus($status);
            $this->service->atualizarStatus($animal);

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Status atualizado com sucesso.']);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
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

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Animal reativado com sucesso.']);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
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

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Animal excluído com sucesso.']);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
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
            throw new Exception('Os dados enviados são inválidos.');
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
            throw new Exception('Corpo da requisição inválido.');
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
            throw new Exception('ID inválido.');
        }

        return (int) $id;
    }

    

}
