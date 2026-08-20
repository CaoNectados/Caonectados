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

    // Usado por: instanciado pelo Router para todas as rotas /admin/raca/* e /raca/json
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // /raca/json e /admin/raca/json são compartilhadas com outros perfis (AJAX de raças por espécie).
        $uriAtual = $this->getUriLimpa();
        if ($uriAtual !== '/raca/json' && $uriAtual !== '/admin/raca/json') {
            $this->autenticacaoRequired(['administrador']);
        }

        $pdo = ConnectionFactory::getConnection();

        $this->racaRepo = new RacaRepository($pdo);
        $this->especieRepository = new EspecieRepository($pdo);

        $this->service = new RacaService($this->racaRepo);
        $this->especieService = new EspecieService($this->especieRepository);
    }

    // Usado por: rota GET /admin/raca
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

    // Usado por: rota GET /admin/raca/cadastrar
    public function create(): void
    {
        $especies = $this->especieService->listarTodas();

        $this->view('raca/cadastrar', [
            'especies' => $especies,
            'titulo'   => 'Cadastrar Raça'
        ]);
    }

    // Usado por: rota POST /admin/raca/salvar
    public function store(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $especieId = (int)($_POST['especie_id'] ?? 0);

            $raca = new Raca();
            $raca->setNome($nome);
            $raca->setEspecieId($especieId);

            $this->service->cadastrar($raca);

            $this->redirecionarComMensagem('sucesso', 'Raça cadastrada com sucesso!', '/admin/raca');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao cadastrar raça.', '/admin/raca', $e->getMessage());
        }
    }

    // Usado por: rota GET /admin/raca/editar
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

    // Usado por: rota POST /admin/raca/atualizar
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

            $this->redirecionarComMensagem('sucesso', 'Raça atualizada com sucesso!', '/admin/raca');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao atualizar raça.', '/admin/raca', $e->getMessage());
        }
    }

    // Usado por: rota GET /admin/raca/excluir
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

    // Usado por: rota POST /admin/raca/deletar
    public function destroy(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Raça inválida.');
            }
            $this->service->excluir($id);

            $this->redirecionarComMensagem('sucesso', 'Raça desativada com sucesso!', '/admin/raca');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao desativar raça.', '/admin/raca', $e->getMessage());
        }
    }

    // Usado por: rota POST /admin/raca/importar
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

    // Usado por: rota GET /admin/raca/reativar
    public function reativar(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Raça inválida.');
            }
            $this->service->reativar($id);

            $this->redirecionarComMensagem('sucesso', 'Raça reativada com sucesso!', '/admin/raca');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao reativar raça.', '/admin/raca', $e->getMessage());
        }
    }

    // Usado por: rotas GET /admin/raca/json e /raca/json (chamada por outros perfis)
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

    // Usado por: rota GET /admin/gerenciar-especies-racas
    public function gerenciarEspeciesRacas(): void
    {
        // Carrega SOMENTE dados locais do banco — nenhuma chamada às APIs externas
        // (TheDogAPI/TheCatAPI) acontece aqui. Elas só rodam sob demanda em sugestoesJson().
        $especies = $this->especieService->listarTodas('ativos');
        $racas = $this->service->listarTodas('ativos');

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

        $this->view('admin/gerenciar_especies_racas', [
            'titulo' => 'Gerenciar Espécies e Raças',
            'especiesComRacas' => $especiesComRacas,
        ]);
    }

    /**
     * Endpoint AJAX: busca sugestões de raças nas APIs externas (TheDogAPI/TheCatAPI)
     * SOB DEMANDA, disparado apenas pelo clique no botão "Sugestões da API" da view.
     */
    // Usado por: rota GET /admin/raca/sugestoes-json
    public function sugestoesJson(): void
    {
        try {
            $racas = $this->service->listarTodas('ativos');
            $nomesRacasNoBanco = [];
            foreach ($racas as $raca) {
                $nomesRacasNoBanco[strtolower(trim($raca->getNome()))] = true;
            }

            $sugestoesBrutas = $this->service->buscarSugestoesExternas();

            $listaCaes = array_values(array_filter($sugestoesBrutas['caes'] ?? [], function ($nome) use ($nomesRacasNoBanco) {
                return !isset($nomesRacasNoBanco[strtolower(trim($nome))]);
            }));
            $listaGatos = array_values(array_filter($sugestoesBrutas['gatos'] ?? [], function ($nome) use ($nomesRacasNoBanco) {
                return !isset($nomesRacasNoBanco[strtolower(trim($nome))]);
            }));

            $this->json(200, [
                'status' => 'sucesso',
                'sugestoes' => [
                    ['especie' => 'Cão', 'icon' => '🐶', 'racas' => $listaCaes],
                    ['especie' => 'Gato', 'icon' => '🐱', 'racas' => $listaGatos],
                ],
            ]);
        } catch (Exception $e) {
            $this->json(500, ['status' => 'erro', 'mensagem' => 'Não foi possível buscar sugestões externas no momento.']);
        }
    }
}