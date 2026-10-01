<?php

namespace app\controllers\admin;

use app\services\SolicitacaoService;

class SolicitacaoProtetorController extends AdminBaseController
{
    private SolicitacaoService $solicitacaoService;

    public function __construct()
    {
        parent::__construct();
        $this->validarCsrf();
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

    public function documento(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $s = $this->solicitacaoService->obterDetalhesSolicitacao($id);
        $base = realpath(__DIR__ . '/../../../public/assets/uploads/comprovantes');
        $arquivo = $s['comprovante_documento'] ?? '';
        $nome = basename(str_replace('\\', '/', $arquivo));
        $caminho = $base && $nome !== '' ? realpath($base . '/' . $nome) : false;
        if (!$caminho || !is_file($caminho) || !str_starts_with(str_replace('\\','/',$caminho), str_replace('\\','/',$base).'/')) {
            http_response_code(404);
            return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($caminho);
        if (!in_array($mime, ['application/pdf','image/jpeg','image/png','image/webp'], true)) {
            http_response_code(404);
            return;
        }
        header('Content-Type: '.$mime);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Disposition: inline; filename="documento.'.pathinfo($nome,PATHINFO_EXTENSION).'"');
        readfile($caminho);
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

        try {
        if ($this->solicitacaoService->aprovarSolicitacao($id)) {
            $this->redirecionarComMensagem('sucesso', 'Cadastro aprovado e validado com sucesso!', '/admin/solicitacoes');
        }
        } catch (\Throwable $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível aprovar o cadastro. Confira o estado da solicitação.', '/admin/solicitacoes');
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

        if ($motivo === '') {
            $this->redirecionarComMensagem('erro', 'Informe o motivo da recusa.', '/admin/solicitacoes/detalhes?id=' . $id);
        }

        try {
        if ($this->solicitacaoService->recusarSolicitacao($id, $motivo)) {
            $this->redirecionarComMensagem('sucesso', 'Solicitação recusada com sucesso.', '/admin/solicitacoes');
        }
        } catch (\Throwable $e) {
            $this->redirecionarComMensagem('erro', $e instanceof \Exception && !($e instanceof \PDOException) ? $e->getMessage() : 'Não foi possível recusar o cadastro.', '/admin/solicitacoes/detalhes?id=' . $id);
        }

        $this->redirecionarComMensagem('erro', 'Ocorreu um erro ao recusar o cadastro.', '/admin/solicitacoes');
    }
}
