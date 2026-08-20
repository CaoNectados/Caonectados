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
        $this->autenticacaoRequired(['administrador']);

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
                'titulo' => 'Gerenciar Regiões'
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao listar regiões.', '/admin/dashboard', $e->getMessage());
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
            $this->redirecionarComMensagem('sucesso', 'Bairro cadastrado com sucesso!', '/admin/regiao');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/admin/regiao/cadastrar');
        }
    }

    public function edit(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $regiao = $this->regiaoRepo->buscarPorId($id);

            if ($regiao === null) {
                $this->redirecionarComMensagem('aviso', 'Bairro não encontrado.', '/admin/regiao');
            }

            $this->view('regiao/editar', [
                'regiao' => $regiao,
                'titulo' => 'Editar Bairro'
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao abrir edição.', '/admin/regiao', $e->getMessage());
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
            $this->redirecionarComMensagem('sucesso', 'Bairro atualizado com sucesso!', '/admin/regiao');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/admin/regiao/editar?id=' . ($_GET['id'] ?? 0));
        }
    }

    public function deleteView(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $regiao = $this->regiaoRepo->buscarPorId($id);

            if ($regiao === null) {
                $this->redirecionarComMensagem('aviso', 'Bairro não encontrado.', '/admin/regiao');
            }

            $this->view('regiao/excluir', [
                'regiao' => $regiao,
                'titulo' => 'Excluir Bairro'
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao acessar exclusão.', '/admin/regiao', $e->getMessage());
        }
    }

    public function destroy(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id > 0) {
                $this->service->excluirRegiao($id);
                $this->redirecionarComMensagem('sucesso', 'Bairro excluído com sucesso!', '/admin/regiao');
            }
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/admin/regiao');
        }
    }

    public function buscarJson(): void
    {
        try {
            $regioes = $this->regiaoRepo->buscarTodas();
            $this->json(200, ['sucesso' => true, 'dados' => $regioes]);
        } catch (Exception $e) {
            $this->json(500, ['sucesso' => false, 'mensagem' => 'Erro ao buscar regiões.']);
        }
    }

    public function listarTodas(): array
    {
        try {
            return $this->regiaoRepo->buscarTodas();
        } catch (Exception $e) {
            return [];
        }
    }

    public function deletarMultiplos(): void
    {
        try {
            $ids = $_POST['ids'] ?? [];
            
            if (!empty($ids) && is_array($ids)) {
                $count = 0;
                foreach ($ids as $id) {
                    $idFormatado = (int) $id;
                    if ($idFormatado > 0) {
                        // Utiliza o serviço existente para deletar cada ID validado
                        $this->service->excluirRegiao($idFormatado);
                        $count++;
                    }
                }
                $this->redirecionarComMensagem('sucesso', "{$count} bairro(s) excluído(s) com sucesso!", '/admin/regiao');
            } else {
                $this->redirecionarComMensagem('aviso', 'Nenhum bairro foi selecionado para exclusão.', '/admin/regiao');
            }
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Ação interrompida: ' . $e->getMessage(), '/admin/regiao');
        }
    }
}