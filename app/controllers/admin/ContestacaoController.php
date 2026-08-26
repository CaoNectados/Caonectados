<?php

namespace app\controllers\admin;

use app\services\ContestacaoService;
use Exception;

/**
 * RF 22 (UC 13). Painel de moderação de contestações: revisar justificativa + anexo, aprovar
 * (encerra a advertência e restabelece a conta) ou reprovar (mantém a penalidade).
 */
class ContestacaoController extends AdminBaseController
{
    private ContestacaoService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ContestacaoService();
    }

    /** Lista as contestações por aba (pendentes/decididas). Usado pela rota GET /admin/contestacoes. */
    public function index(): void
    {
        $aba = $_GET['aba'] ?? 'pendentes';
        if (!in_array($aba, ['pendentes', 'decididas'], true)) {
            $aba = 'pendentes';
        }

        $contestacoes = $aba === 'pendentes' ? $this->service->listarPendentes() : $this->service->listarDecididas();

        $this->view('admin/contestacoes', [
            'titulo'        => 'Contestações',
            'contestacoes'  => $contestacoes,
            'abaAtual'      => $aba,
        ]);
    }

    /** Exibe os detalhes de uma contestação (justificativa + anexo) para decisão do administrador. Usado pela rota GET /admin/contestacoes/detalhes. */
    public function detalhes(): void
    {
        try {
            $contestacaoId = (int) ($_GET['id'] ?? 0);
            $contestacao = $this->service->obterDetalhes($contestacaoId);

            $this->view('admin/contestacao_detalhes', [
                'titulo'        => 'Detalhes da Contestação',
                'contestacao'   => $contestacao,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Contestação não encontrada.', '/admin/contestacoes', $e->getMessage());
        }
    }

    /** Registra a decisão (aprovar/reprovar) de uma contestação. Usado pela rota POST /admin/contestacoes/decidir. */
    public function decidir(): void
    {
        $contestacaoId = (int) ($_POST['id'] ?? 0);
        try {
            $aprovar = ($_POST['decisao'] ?? '') === 'aprovar';
            $parecer = (string) ($_POST['parecer'] ?? '');

            $this->service->decidir($contestacaoId, $aprovar, $parecer);

            $this->redirecionarComMensagem('sucesso', 'Decisão registrada.', '/admin/contestacoes?aba=decididas');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível registrar a decisão.', '/admin/contestacoes/detalhes?id=' . $contestacaoId, $e->getMessage());
        }
    }
}
