<?php

namespace app\controllers;

use app\core\Controller;
use app\models\Regioes;
use app\repositories\RegioesRepository;
use app\services\RegioesService;
use Exception;
use PDO;

class RegioesController extends Controller
{
    private RegioesService $service;
    private PDO $db;

    public function __construct()
    {
        $this->db = new PDO(
            'mysql:host=localhost;dbname=caonectados;charset=utf8mb4',
            'root',
            ''
        );

        $repository = new RegioesRepository($this->db);
        $this->service = new RegioesService($repository);
    }

    public function index(): void
    {
        try {
            $repository = new RegioesRepository($this->db);
            $regioes = $repository->listarRegioes();

            require_once __DIR__ . '/../views/regioes/index.php';
        } catch (Exception $e) {
            echo "Erro ao listar regiões: " . $e->getMessage();
        }
    }

    public function create(): void
    {
        require_once __DIR__ . '/../views/regioes/cadastrar.php';
    }

    public function store(): void
    {
        try {
            $nome = trim($_POST['nome_regiao'] ?? '');

            $regiao = new Regioes();
            $regiao->setNomeRegiao($nome);

            $this->service->cadastrarRegiao($regiao);
        } catch (Exception $e) {
            
        }

        header('Location: /regioes');
        exit;
    }

    public function edit(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $repository = new RegioesRepository($this->db);
            $regiao = $repository->buscarPorId($id);

            if ($regiao === null) {
                header('Location: /regioes');
                exit;
            }

            require_once __DIR__ . '/../views/regioes/editar.php';
        } catch (Exception $e) {
            header('Location: /regioes');
            exit;
        }
    }

    public function update(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $nome = trim($_POST['nome_regiao'] ?? '');

            $regiao = new Regioes();
            $regiao->setRegiaoId($id);
            $regiao->setNomeRegiao($nome);

            $this->service->editarRegiao($regiao);
        } catch (Exception $e) {
           
        }

        header('Location: /regioes');
        exit;
    }

    public function deleteView(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $repository = new RegioesRepository($this->db);
            $regiao = $repository->buscarPorId($id);

            if ($regiao === null) {
                header('Location: /regioes');
                exit;
            }

            require_once __DIR__ . '/../views/regioes/excluir.php';
        } catch (Exception $e) {
            header('Location: /regioes');
            exit;
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
            
        }

        header('Location: /regioes');
        exit;
    }

//Para implementar a verificacao quando o Usuario estiver Ok
//     private function verificarSeEAdmin(): void
// {
//     // Exemplo usando Sessão do PHP
//     if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil'] !== 'admin') {
//         // Redireciona o usuário para outra página
//         header('Location: /login?erro=acesso_negado');
//         exit;
//     }
// }
}