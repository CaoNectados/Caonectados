<?php

namespace app\controllers;

use app\core\Controller;
use app\models\Especies;
use app\repositories\EspeciesRepository;
use app\services\EspeciesService;
use Exception;
use PDO;

class EspeciesController extends Controller
{
    private EspeciesService $service;

    public function __construct()
    {
        $db = new PDO('mysql:host=localhost;dbname=caonectados;charset=utf8mb4', 'root', '');
        $repository = new EspeciesRepository($db);
        $this->service = new EspeciesService($repository);
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? 'todos';
        $especies = $this->service->listarTodas($status);

        require_once __DIR__ . '/../views/especies/index.php';
    }

    public function create(): void
    {
        require_once __DIR__ . '/../views/especies/cadastrar.php';
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $especie = new Especies();
            $especie->setNome($nome);
            $this->service->cadastrar($especie);
        } catch (Exception $e) {
        }

        header('Location: /especies');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $especie = $this->service->buscarPorId($id);

        if (!$especie) {
            header('Location: /especies');
            exit;
        }

        require_once __DIR__ . '/../views/especies/editar.php';
    }

    public function update(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');

            $especie = new Especies();
            $especie->setId($id);
            $especie->setNome($nome);

            $this->service->atualizar($especie);
        } catch (Exception $e) {
        }

        header('Location: /especies');
        exit;
    }

    public function deleteView(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $especie = $this->service->buscarPorId($id);

        if (!$especie) {
            header('Location: /especies');
            exit;
        }

        require_once __DIR__ . '/../views/especies/excluir.php';
    }

    public function destroy(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->excluir($id);
        }

        header('Location: /especies');
        exit;
    }

    public function reativar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->reativar($id);
        }

        header('Location: /especies');
        exit;
    }
}
