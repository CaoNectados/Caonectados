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

        $this->view('especie/index', [
            'especies' => $especies,
            'status'   => $status,
            'titulo'   => 'Gerenciar Espécies'
        ]);
    }

    public function create(): void
    {
        $this->view('especie/cadastrar', [
            'titulo' => 'Cadastrar Espécie'
        ]);
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $especie = new Especie();
            $especie->setNome($nome);
            $this->service->cadastrar($especie);

            $this->redirecionarComMensagem('sucesso', 'Espécie cadastrada com sucesso!', '/admin/especie');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao cadastrar espécie.', '/admin/especie', $e->getMessage());
        }
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $especie = $this->service->buscarPorId($id);

        if (!$especie) {
            $this->redirecionarComMensagem('aviso', 'Espécie não encontrada.', '/admin/especie');
        }

        $this->view('especie/editar', [
            'especie' => $especie,
             'titulo'   => 'Editar Espécie'
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

            $this->redirecionarComMensagem('sucesso', 'Espécie atualizada com sucesso!', '/admin/especie');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao atualizar espécie.', '/admin/especie', $e->getMessage());
        }
    }

    public function deleteView(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $especie = $this->service->buscarPorId($id);

        if (!$especie) {
            $this->redirecionarComMensagem('aviso', 'Espécie não encontrada.', '/admin/especie');
        }

        $this->view('especie/excluir', [
            'especie' => $especie,
             'titulo'   => 'Desativar Espécie'
        ]);
    }

    public function destroy(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Espécie inválida.');
            }
            $this->service->excluir($id);
            $this->redirecionarComMensagem('sucesso', 'Espécie desativada com sucesso!', '/admin/especie');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao desativar a espécie.', '/admin/especie', $e->getMessage());
        }
    }

    public function reativar(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Espécie inválida.');
            }
            $this->service->reativar($id);
            $this->redirecionarComMensagem('sucesso', 'Espécie reativada com sucesso!', '/admin/especie');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao reativar espécie.', '/admin/especie', $e->getMessage());
        }
    }

    /**
     * Endpoint para requisições AJAX/Fetch do JavaScript
     */
    public function buscarJson(): void
    {
        try {
            $especies = $this->especieRepo->buscarTodas();

            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $especies]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar espécies.']);
        }
        exit;
    }
}