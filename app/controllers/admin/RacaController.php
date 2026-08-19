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

        $uriAtual = $this->getUriLimpa();
        if ($uriAtual !== '/raca/json' && $uriAtual !== '/admin/raca/json') {
            if (($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
                $this->redirect('/login');
            }
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
            // Tratamento de erro
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
            $especieNome = trim($_POST['especie_nome'] ?? '');
            $racasAceitas = $_POST['racas_aceitas'] ?? [];

            if ($especieNome !== '' && !empty($racasAceitas)) {
                $total = $this->service->importarSelecionadas($this->especieRepository, $especieNome, $racasAceitas);
                $_SESSION['mensagem'] = "Importação realizada com sucesso para {$especieNome}! {$total} raça(s) adicionada(s).";
            } else {
                $_SESSION['erro'] = "Nenhuma raça foi selecionada para importação.";
            }
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro durante a importação: " . $e->getMessage();
        }

        $this->redirect('/admin/gerenciar-especies-racas?aba=sugestoes');
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
            $especieId = filter_input(INPUT_GET, 'especie_id', FILTER_VALIDATE_INT);

            if ($especieId) {
                $racas = $this->racaRepo->buscarPorEspecie($especieId);
            } else {
                $racas = $this->racaRepo->buscarTodas();
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'dados' => $racas], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar raças.'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function gerenciarEspeciesRacas(): void
    {
        $especies = $this->especieService->listarTodas('ativos');
        $racas = $this->service->listarTodas('ativos');

        $especiesComRacas = [];
        $nomesRacasNoBanco = [];

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
            // Mapeia para filtrar as sugestões
            $nomesRacasNoBanco[strtolower(trim($raca->getNome()))] = true;
        }

        // Puxa as sugestões usando o método da service
        $sugestoesBrutas = $this->service->buscarSugestoesExternas();

        // Filtra para remover o que já está no banco
        $listaCaes = array_filter($sugestoesBrutas['caes'] ?? [], function($nome) use ($nomesRacasNoBanco) {
            return !isset($nomesRacasNoBanco[strtolower(trim($nome))]);
        });

        $listaGatos = array_filter($sugestoesBrutas['gatos'] ?? [], function($nome) use ($nomesRacasNoBanco) {
            return !isset($nomesRacasNoBanco[strtolower(trim($nome))]);
        });

        $sugestoesApi = [
            ['especie' => 'Cão', 'icon' => '🐶', 'racas' => array_values($listaCaes)],
            ['especie' => 'Gato', 'icon' => '🐱', 'racas' => array_values($listaGatos)]
        ];

        $this->view('admin/gerenciar_especies_racas', [
            'titulo' => 'Gerenciar Espécies e Raças',
            'especiesComRacas' => $especiesComRacas,
            'sugestoesApi' => $sugestoesApi
        ]);
    }

    
}