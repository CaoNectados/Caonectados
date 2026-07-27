<?php

namespace app\controllers;

use app\core\Controller;
use app\models\Racas;
use app\repositories\RacasRepository;
use app\repositories\EspeciesRepository;
use app\services\RacasService;
use app\services\EspeciesService;
use Exception;
use PDO;

class RacasController extends Controller
{
    private RacasService $service;
    private EspeciesService $especieService;
    private EspeciesRepository $especieRepository;

    public function __construct()
    {
        $db = new PDO('mysql:host=localhost;dbname=caonectados;charset=utf8mb4', 'root', '');

        $racaRepo = new RacasRepository($db);
        $this->especieRepository = new EspeciesRepository($db);

        $this->service = new RacasService($racaRepo);
        $this->especieService = new EspeciesService($this->especieRepository);
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? 'todos';
        $racas = $this->service->listarTodas($status);

        require_once __DIR__ . '/../views/racas/index.php';
    }

    public function create(): void
    {
        $especies = $this->especieService->listarTodas();
        require_once __DIR__ . '/../views/racas/cadastrar.php';
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $especieId = (int)($_POST['especie_id'] ?? 0);

            $raca = new Racas();
            $raca->setNome($nome);
            $raca->setEspecieId($especieId);

            $this->service->cadastrar($raca);
        } catch (Exception $e) {
        }

        header('Location: /racas');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $raca = $this->service->buscarPorId($id);

        if (!$raca) {
            header('Location: /racas');
            exit;
        }

        $especies = $this->especieService->listarTodas();
        require_once __DIR__ . '/../views/racas/editar.php';
    }

    public function update(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $especieId = (int)($_POST['especie_id'] ?? 0);

            $raca = new Racas();
            $raca->setId($id);
            $raca->setNome($nome);
            $raca->setEspecieId($especieId);

            $this->service->atualizar($raca);
        } catch (Exception $e) {
        }

        header('Location: /racas');
        exit;
    }

    public function deleteView(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $raca = $this->service->buscarPorId($id);

        if (!$raca) {
            header('Location: /racas');
            exit;
        }

        require_once __DIR__ . '/../views/racas/excluir.php';
    }

    public function destroy(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->excluir($id);
        }

        header('Location: /racas');
        exit;
    }

    public function importar(): void
    {
        try {
            $resultado = $this->service->importarDeApisExternas($this->especieRepository);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['mensagem'] = "Sincronização concluída! {$resultado['total']} nova(s) raça(s) adicionada(s).";
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro durante a importação: " . $e->getMessage();
        }

        header('Location: /racas');
        exit;
    }

    public function reativar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->reativar($id);
        }

        header('Location: /racas');
        exit;
    }
}
