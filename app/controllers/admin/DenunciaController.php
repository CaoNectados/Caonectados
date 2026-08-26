<?php

namespace app\controllers\admin;

use app\services\DenunciaService;
use Exception;

/**
 * RF 21 (UC 12). Painel de moderação: abas Abertas / Em Análise / Resolvidas, com detalhe e
 * decisão (colocar em análise, reprovar/arquivar, aprovar + aplicar advertência/bloqueio).
 */
class DenunciaController extends AdminBaseController
{
    private DenunciaService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DenunciaService();
    }

    /** Lista as denúncias por aba (abertas/em_analise/resolvidas). Usado pela rota GET /admin/denuncias. */
    public function index(): void
    {
        $aba = $_GET['aba'] ?? 'abertas';
        if (!in_array($aba, ['abertas', 'em_analise', 'resolvidas'], true)) {
            $aba = 'abertas';
        }

        $this->view('admin/denuncias', [
            'titulo'    => 'Denúncias',
            'denuncias' => $this->service->listarParaModeracao($aba),
            'abaAtual'  => $aba,
        ]);
    }

    /** Exibe os detalhes de uma denúncia para decisão do administrador. Usado pela rota GET /admin/denuncias/detalhes. */
    public function detalhes(): void
    {
        try {
            $denunciaId = (int) ($_GET['id'] ?? 0);
            $denuncia = $this->service->obterDetalhes($denunciaId);

            $this->view('admin/denuncia_detalhes', [
                'titulo'   => 'Detalhes da Denúncia',
                'denuncia' => $denuncia,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Denúncia não encontrada.', '/admin/denuncias', $e->getMessage());
        }
    }

    /** Coloca uma denúncia em análise. Usado pela rota POST /admin/denuncias/em-analise. */
    public function colocarEmAnalise(): void
    {
        try {
            $denunciaId = (int) ($_POST['id'] ?? 0);
            $this->service->colocarEmAnalise($denunciaId);

            $this->redirecionarComMensagem('sucesso', 'Denúncia colocada em análise.', '/admin/denuncias?aba=em_analise');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível atualizar a denúncia.', '/admin/denuncias', $e->getMessage());
        }
    }

    /** Reprova e arquiva uma denúncia sem aplicar penalidade. Usado pela rota POST /admin/denuncias/reprovar. */
    public function reprovar(): void
    {
        try {
            $denunciaId = (int) ($_POST['id'] ?? 0);
            $this->service->reprovar($denunciaId);

            $this->redirecionarComMensagem('sucesso', 'Denúncia reprovada e arquivada.', '/admin/denuncias?aba=resolvidas');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível reprovar a denúncia.', '/admin/denuncias/detalhes?id=' . (int) ($_POST['id'] ?? 0), $e->getMessage());
        }
    }

    /** Aprova a denúncia e aplica advertência, com bloqueio opcional da conta (RF 19). Usado pela rota POST /admin/denuncias/aprovar. */
    public function aprovar(): void
    {
        $denunciaId = (int) ($_POST['id'] ?? 0);
        try {
            $pesoStatus = (string) ($_POST['peso_status'] ?? 'leve');
            $bloquearConta = !empty($_POST['bloquear_conta']);
            $dataFim = !empty($_POST['data_fim']) ? (string) $_POST['data_fim'] : null;

            $this->service->aprovar($denunciaId, $pesoStatus, $bloquearConta, $dataFim);

            $this->redirecionarComMensagem('sucesso', 'Denúncia aprovada e advertência aplicada.', '/admin/denuncias?aba=resolvidas');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível aprovar a denúncia.', '/admin/denuncias/detalhes?id=' . $denunciaId, $e->getMessage());
        }
    }
}
