<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\services\ContestacaoService;
use Exception;

/**
 * RF 17 (UC 07). Adotantes e Protetores com advertência ATIVA podem submeter uma justificativa
 * formal, opcionalmente com anexo de evidência.
 */
class ContestacaoController extends Controller
{
    private ContestacaoService $service;

    public function __construct()
    {
        $this->autenticacaoRequired();
        $this->service = new ContestacaoService();
    }

    /** Exibe o formulário de contestação (advertência pode vir pré-selecionada via ?advertencia_id=). Usado pela rota GET /contestar. */
    public function criar(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $advertencias = $this->service->listarAdvertenciasContestaveis($usuarioId);
        $advertenciaIdPreSelecionada = (int) ($_GET['advertencia_id'] ?? 0);

        $this->view('contestacao/criar', [
            'titulo'                       => 'Contestar Advertência',
            'advertencias'                 => $advertencias,
            'advertenciaIdPreSelecionada'  => $advertenciaIdPreSelecionada,
        ]);
    }

    /** Registra a contestação enviada. Usado pela rota POST /contestar/enviar. */
    public function enviar(): void
    {
        try {
            $advertenciaId = (int) ($_POST['advertencia_id'] ?? 0);
            $justificativa = (string) ($_POST['justificativa'] ?? '');
            $anexo = $_FILES['anexo'] ?? null;

            $this->service->criar($advertenciaId, (int) $_SESSION['usuario_id'], $justificativa, $anexo);

            $this->redirecionarComMensagem('sucesso', 'Contestação enviada! Você será notificado assim que houver uma decisão.', '/minhas-contestacoes');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível enviar a contestação.', '/contestar', $e->getMessage());
        }
    }

    /** Lista as contestações do usuário logado (UC 07.2 — acompanhamento). Usado pela rota GET /minhas-contestacoes. */
    public function minhas(): void
    {
        $contestacoes = $this->service->listarMinhas((int) $_SESSION['usuario_id']);

        $this->view('contestacao/minhas', [
            'titulo'        => 'Minhas Contestações',
            'contestacoes'  => $contestacoes,
        ]);
    }
}
