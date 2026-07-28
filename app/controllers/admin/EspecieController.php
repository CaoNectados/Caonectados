<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\models\Especie;
use app\repositories\EspecieRepository;
use app\services\EspecieService;
use app\database\ConnectionFactory;
use Exception;

class EspecieController extends Controller
{
    private EspecieService $service;
    private EspecieRepository $especieRepo;

    public function __construct()
    {
        // Trava nativa do Controller base restrita a administradores
        $this->autenticacaoRequired(['administrador']);

        $pdo = ConnectionFactory::getConnection();
        $this->especieRepo = new EspecieRepository($pdo);
        $this->service = new EspecieService($this->especieRepo);
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? 'todos';
        $especies = $this->service->listarTodas($status);

        $this->view('admin/especies/index', [
            'especies' => $especies,
            'status'   => $status
        ]);
    }

    public function create(): void
    {
        $this->view('admin/especies/cadastrar');
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $especie = new Especie();
            $especie->setNome($nome);
            $this->service->cadastrar($especie);

            $this->redirecionarComMensagem('sucesso', 'Espécie cadastrada com sucesso!', '/admin/especies');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao cadastrar espécie.', '/admin/especies', $e->getMessage());
        }
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $especie = $this->service->buscarPorId($id);

        if (!$especie) {
            $this->redirecionarComMensagem('aviso', 'Espécie não encontrada.', '/admin/especies');
        }

        $this->view('admin/especies/editar', [
            'especie' => $especie
        ]);
    }

    public function update(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');

            $especie = new Especie();
            $especie->setId($id);
            $especie->setNome($nome);

            $this->service->atualizar($especie);

            $this->redirecionarComMensagem('sucesso', 'Espécie atualizada com sucesso!', '/admin/especies');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao atualizar espécie.', '/admin/especies', $e->getMessage());
        }
    }

    public function deleteView(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $especie = $this->service->buscarPorId($id);

        if (!$especie) {
            $this->redirecionarComMensagem('aviso', 'Espécie não encontrada.', '/admin/especies');
        }

        $this->view('admin/especies/excluir', [
            'especie' => $especie
        ]);
    }

    public function destroy(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $this->service->excluir($id);
                $this->redirecionarComMensagem('sucesso', 'Espécie excluída com sucesso!', '/admin/especies');
            }
            $this->redirect('/admin/especies');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao excluir espécie.', '/admin/especies', $e->getMessage());
        }
    }

    public function reativar(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $this->service->reativar($id);
                $this->redirecionarComMensagem('sucesso', 'Espécie reativada com sucesso!', '/admin/especies');
            }
            $this->redirect('/admin/especies');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao reativar espécie.', '/admin/especies', $e->getMessage());
        }
    }

    /**
     * Endpoint para requisições AJAX/Fetch do JavaScript
     */
    public function buscarJson(): void
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $especies = $this->especieRepo->buscarTodas($pdo);
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $especies]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar espécies.']);
        }
        exit;
    }
}