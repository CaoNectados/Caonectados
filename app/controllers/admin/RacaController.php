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

        if (($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
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

        $this->view('raca/index', [
            'racas' => $racas,
            'status' => $status,
            'titulo'   => 'Gerenciar Raças'
        ]);
    }

    public function create(): void
    {
        $especies = $this->especieService->listarTodas();

        $this->view('raca/cadastrar', [
            'especies' => $especies,
            'titulo'   => 'Cadastrar Raça'
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

        $this->redirect('/admin/raca');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $raca = $this->service->buscarPorId($id);
       
        if (!$raca) {
            $this->redirect('/admin/raca');
        }

        $especies = $this->especieService->listarTodas();

        $this->view('raca/editar', [
            'raca' => $raca,
            'especies' => $especies,
            'titulo'   => 'Editar Raça'

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

        $this->redirect('/admin/raca');
    }

    public function deleteView(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $raca = $this->service->buscarPorId($id);

        if (!$raca) {
            $this->redirect('/admin/raca');
        }

        $this->view('raca/excluir', [
            'raca' => $raca,
            'titulo'   => 'Excluir Raça'

        ]);
    }

    public function destroy(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->excluir($id);
        }

        $this->redirect('/admin/raca');
    }

    public function importar(): void
    {
        try {
            $resultado = $this->service->importarDeApisExternas($this->especieRepository);

            $_SESSION['mensagem'] = "Sincronização concluída! {$resultado['total']} nova(s) raça(s) adicionada(s).";
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro durante a importação: " . $e->getMessage();
        }

        $this->redirect('/admin/raca');
    }

    public function reativar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->service->reativar($id);
        }

        $this->redirect('/admin/raca');
    }

    public function buscarJson()
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $especieId = filter_input(INPUT_GET, 'especie_id', FILTER_VALIDATE_INT);

            if ($especieId) {
                $racas = $this->racaRepo->buscarPorEspecie($especieId);
            } else {
                $racas = $this->racaRepo->buscarTodas();
            }

            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $racas]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar raças.']);
        }
        exit;
    }

    public function gerenciarEspeciesRacas(): void
    {
        // Busca todas as espécies e raças ativas
        $especies = $this->especieService->listarTodas('ativos');
        $racas = $this->service->listarTodas('ativos');

        // Cria um array agrupando as raças dentro de suas respectivas espécies
        $especiesComRacas = [];
        foreach ($especies as $especie) {
            $especiesComRacas[$especie->getId()] = [
                'especie' => $especie,
                'racas' => []
            ];
        }

        foreach ($racas as $raca) {
            $especieId = $raca->getEspecieId();
            if (isset($especiesComRacas[$especieId])) {
                $especiesComRacas[$especieId]['racas'][] = $raca;
            }
        }

        // Envia para a View
        $this->view('admin/gerenciar_especies_racas', [
            'titulo' => 'Visão Geral: Espécies e Raças',
            'especiesComRacas' => $especiesComRacas
        ]);
    }
}
