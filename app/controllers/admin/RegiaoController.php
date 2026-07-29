<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\models\Regiao;
use app\repositories\RegiaoRepository;
use app\services\RegiaoService;
use app\database\ConnectionFactory;
use Exception;

class RegiaoController extends Controller
{
    private RegiaoService $service;
    private RegiaoRepository $regiaoRepo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
            $this->redirect('/login');
        }

        $pdo = ConnectionFactory::getConnection();
        $this->regiaoRepo = new RegiaoRepository($pdo);
        $this->service = new RegiaoService($this->regiaoRepo);
    }

    public function index(): void
    {
        try {
            $regioes = $this->regiaoRepo->buscarTodas();
            $this->view('regiao/index', [
                'regioes' => $regioes,
                'titulo' => 'Gerenciar Bairros'
            ]);
        } catch (Exception $e) {
            echo "Erro ao listar regiões: " . $e->getMessage();
        }
    }

    public function create(): void
    {
        $this->view('regiao/cadastrar', [
            'titulo' => 'Cadastrar Bairro'
        ]);
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome_regiao'] ?? '');

            $regiao = new Regiao();
            $regiao->setNomeRegiao($nome);

            $this->service->cadastrarRegiao($regiao);
        } catch (Exception $e) {
            // Tratamento de erro
        }

        $this->redirect('/admin/regiao');
    }

    public function edit(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $regiao = $this->regiaoRepo->buscarPorId($id);

            if ($regiao === null) {
                $this->redirect('/admin/regiao');
            }

            $this->view('regiao/editar', [
                'regiao' => $regiao,
                'titulo' => 'Editar Bairro'
            ]);
        } catch (Exception $e) {
            $this->redirect('/admin/regiao');
        }
    }

    public function update(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $nome = trim($_POST['nome_regiao'] ?? '');

            $regiao = new Regiao();
            $regiao->setRegiaoId($id);
            $regiao->setNomeRegiao($nome);

            $this->service->editarRegiao($regiao);
        } catch (Exception $e) {
            // Tratamento de erro
        }

        $this->redirect('/admin/regiao');
    }

    public function deleteView(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $regiao = $this->regiaoRepo->buscarPorId($id);

            if ($regiao === null) {
                $this->redirect('/admin/regiao');
            }

            $this->view('regiao/excluir', [
                'regiao' => $regiao,
                'titulo' => 'Excluir Bairro'
            ]);
        } catch (Exception $e) {
            $this->redirect('/admin/regiao');
        }
    }

    public function destroy(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            if ($id > 0) {
                $this->service->excluirRegiao($id);
            }
        } catch (Exception $e) {
            // Tratamento de erro
        }

        $this->redirect('/admin/regiao');
    }

    public function buscarJson()
    {
        try {
            $regioes = $this->regiaoRepo->buscarTodas();
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $regioes]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar regiões.']);
        }
        exit;
    }
}