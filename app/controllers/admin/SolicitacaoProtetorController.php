<?php

namespace app\controllers\admin;

use app\services\SolicitacaoService;

class SolicitacaoProtetorController extends AdminBaseController
{
    private SolicitacaoService $solicitacaoService;

    public function __construct()
    {
        parent::__construct();
        $this->solicitacaoService = new SolicitacaoService();
    }

    // Usado por: rota GET /admin/solicitacoes
    public function index(): void
    {
        $status = $_GET['status'] ?? 'pendentes';
        $busca = $_GET['busca'] ?? '';

        $solicitacoes = $this->solicitacaoService->listarSolicitacoes($status, $busca);

        $this->view('admin/solicitacoes', [
            'titulo'       => 'Solicitações de protetores',
            'solicitacoes' => $solicitacoes,
            'statusAtual'  => $status,
            'busca'        => $busca
        ]);
    }

    // Usado por: rota GET /admin/solicitacoes/detalhes
    public function detalhes(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirecionarComMensagem('erro', 'Solicitação não informada.', '/admin/solicitacoes');
        }

        $solicitacao = $this->solicitacaoService->obterDetalhesSolicitacao($id);
        if (!$solicitacao) {
            $this->redirecionarComMensagem('erro', 'Solicitação não encontrada.', '/admin/solicitacoes');
        }

        $this->view('admin/solicitacoes_detalhes', [
            'titulo'       => 'Detalhes da solicitação',
            'solicitacao' => $solicitacao
        ]);
    }

    // Usado por: rota POST /admin/solicitacoes/aprovar
    public function aprovar(): void
    {
        $id = (int)($_POST['protetor_id'] ?? 0);
        if ($id <= 0) {
            $this->redirecionarComMensagem('erro', 'ID de solicitação inválido.', '/admin/solicitacoes');
        }

        if ($this->solicitacaoService->aprovarSolicitacao($id)) {
            $this->redirecionarComMensagem('sucesso', 'Cadastro aprovado e validado com sucesso!', '/admin/solicitacoes');
        }

        $this->redirecionarComMensagem('erro', 'Ocorreu um erro ao aprovar o cadastro.', '/admin/solicitacoes');
    }

    // Usado por: rota POST /admin/solicitacoes/rejeitar
    public function rejeitar(): void
    {
        $id = (int)($_POST['protetor_id'] ?? 0);
        $motivo = trim($_POST['motivo_recusa'] ?? '');

        if ($id <= 0) {
            $this->redirecionarComMensagem('erro', 'ID de solicitação inválido.', '/admin/solicitacoes');
        }

        if ($this->solicitacaoService->recusarSolicitacao($id, $motivo)) {
            $this->redirecionarComMensagem('sucesso', 'Solicitação recusada com sucesso. O e-mail foi enviado.', '/admin/solicitacoes');
        }

        $this->redirecionarComMensagem('erro', 'Ocorreu um erro ao recusar o cadastro.', '/admin/solicitacoes');
    }
}