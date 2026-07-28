<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\models\Raca;
use app\repositories\RacaRepository;
use app\repositories\EspecieRepository;
use app\services\RacaService;
use app\services\EspecieService;
use app\database\ConnectionFactory;
use Exception;

class RacaController extends Controller
{
    private RacaService $service;
    private EspecieService $especieService;
    private RacaRepository $racaRepo;
    private EspecieRepository $especieRepository;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (($_SESSION['tipo_conta'] ?? '') !== 'administrador') {
            $this->redirect('/login');
        }

        $pdo = ConnectionFactory::getConnection();

        $this->racaRepo = new RacaRepository($pdo);
        $this->especieRepository = new EspecieRepository($pdo);

        $this->service = new RacaService($this->racaRepo);
        $this->especieService = new EspecieService($this->especieRepository);
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? 'todos';
        $racas = $this->service->listarTodas($status);

        $this->view('admin/racas/index', [
            'racas' => $racas,
            'status' => $status
        ]);
    }

    public function create(): void
    {
        $especies = $this->especieService->listarTodas();
        
        $this->view('admin/racas/cadastrar', [
            'especies' => $especies
        ]);
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $especieId = (int)($_POST['especie_id'] ?? 0);

            $raca = new Raca();
            $raca->setNome($nome);
            $raca->setEspecieId($especieId);

            $this->service->cadastrar($raca);
        } catch (Exception $e) {
            // Tratamento de erro silencioso ou log
        }

        $this->redirect('/admin/racas');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $raca = $this->service->buscarPorId($id);

        if (!$raca) {
            $this->redirect('/admin/racas');
        }

        $especies = $this->especieService->listarTodas();
        
        $this->view('admin/racas/editar', [
            'raca' => $raca,
            'especies' => $especies
        ]);
    }

    public function update(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $especieId = (int)($_POST['especie_id'] ?? 0);

            $raca = new Raca();
            $raca->setId($id);
            $raca->setNome($nome);
            $raca->setEspecieId($especieId);

            $this->service->atualizar($raca);
        } catch (Exception $e) {
            // Tratamento de erro
        }

        $this->redirect('/admin/racas');
    }

    public function deleteView(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $raca = $this->service->buscarPorId($id);

        if (!$raca) {
            $this->redirect('/admin/racas');
        }

        $this->view('admin/racas/excluir', [
            'raca' => $raca
        ]);
    }

    public function destroy(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->excluir($id);
        }

        $this->redirect('/admin/racas');
    }

    public function importar(): void
    {
        try {
            $resultado = $this->service->importarDeApisExternas($this->especieRepository);

            $_SESSION['mensagem'] = "Sincronização concluída! {$resultado['total']} nova(s) raça(s) adicionada(s).";
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro durante a importação: " . $e->getMessage();
        }

        $this->redirect('/admin/racas');
    }

    public function reativar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->reativar($id);
        }

        $this->redirect('/admin/racas');
    }

    public function buscarJson()
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $especieId = filter_input(INPUT_GET, 'especie_id', FILTER_VALIDATE_INT);
            
            if ($especieId) {
                $racas = $this->racaRepo->buscarPorEspecie($pdo, $especieId);
            } else {
                $racas = $this->racaRepo->buscarTodas($pdo);
            }
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $racas]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar raças.']);
        }
        exit;
    }
}