<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\services\DenunciaService;
use app\repositories\UsuarioRepository;
use app\repositories\ProtetorRepository;
use Exception;

/**
 * RF 18 (UC 05). Qualquer usuário autenticado pode denunciar outro; RN 21 permite anexar
 * opcionalmente a uma solicitação de adoção ou a um chat (via query string na abertura do form).
 */
class DenunciaController extends Controller
{
    private DenunciaService $service;

    public function __construct()
    {
        $this->autenticacaoRequired();
        $this->service = new DenunciaService();
    }

    /**
     * Exibe o formulário de denúncia (com ou sem alvo pré-selecionado). Aceita o alvo via
     * ?usuario_id= (genérico) OU ?protetor_id= (usado pelo 🚩 do card de animal no Feed e na
     * página pública, que só têm o protetor_id à mão) — resolvido pro usuario_id por trás, com
     * o nome_fantasia preferido ao nome pessoal na exibição. Usado pela rota GET /denunciar.
     */
    public function criar(): void
    {
        $usuarioRepo = new UsuarioRepository();
        $alvoId = (int) ($_GET['usuario_id'] ?? 0);
        $protetorId = (int) ($_GET['protetor_id'] ?? 0);
        $alvo = null;

        if ($alvoId > 0) {
            $alvo = $usuarioRepo->buscarPorId($alvoId);
        } elseif ($protetorId > 0) {
            $protetorRepo = new ProtetorRepository();
            $usuarioIdResolvido = $protetorRepo->buscarUsuarioIdPorProtetorId($protetorId);
            if ($usuarioIdResolvido) {
                $alvo = $usuarioRepo->buscarPorId($usuarioIdResolvido);
                $basico = $protetorRepo->buscarBasicoPorId($protetorId);
                if ($alvo && $basico) {
                    $alvo['nome'] = $basico['nome_fantasia'];
                }
            }
        }

        $this->view('denuncia/criar', [
            'titulo'        => 'Denúncias',
            'alvo'          => $alvo,
            'solicitacaoId' => !empty($_GET['solicitacao_id']) ? (int) $_GET['solicitacao_id'] : null,
            'chatId'        => !empty($_GET['chat_id']) ? (int) $_GET['chat_id'] : null,
        ]);
    }

    /** Endpoint AJAX do campo "Buscar usuário ou ONG". Usado pela rota GET /denunciar/buscar. */
    public function buscar(): void
    {
        $termo = trim($_GET['q'] ?? '');
        if (mb_strlen($termo) < 2) {
            $this->json(200, ['status' => 'sucesso', 'resultados' => []]);
            return;
        }

        $resultados = (new UsuarioRepository())->buscarParaDenuncia($termo, (int) $_SESSION['usuario_id']);
        $this->json(200, ['status' => 'sucesso', 'resultados' => $resultados]);
    }

    /** Registra a denúncia enviada. Usado pela rota POST /denunciar/enviar. */
    public function enviar(): void
    {
        try {
            $denunciadoId = (int) ($_POST['denunciado_id'] ?? 0);
            $motivo = (string) ($_POST['motivo'] ?? '');
            $descricao = (string) ($_POST['descricao'] ?? '');
            $solicitacaoId = !empty($_POST['solicitacao_id']) ? (int) $_POST['solicitacao_id'] : null;
            $chatId = !empty($_POST['chat_id']) ? (int) $_POST['chat_id'] : null;

            $this->service->criar((int) $_SESSION['usuario_id'], $denunciadoId, $motivo, $descricao, $solicitacaoId, $chatId);

            $this->redirecionarComMensagem('sucesso', 'Denúncia enviada! Nossa equipe vai analisar.', '/minhas-denuncias');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível enviar a denúncia.', '/denunciar', $e->getMessage());
        }
    }

    /** Lista as denúncias feitas pelo usuário logado (UC 05.2 — acompanhamento). Usado pela rota GET /minhas-denuncias. */
    public function minhas(): void
    {
        $denuncias = $this->service->listarMinhas((int) $_SESSION['usuario_id']);

        $this->view('denuncia/minhas', [
            'titulo'     => 'Minhas Denúncias',
            'denuncias'  => $denuncias,
        ]);
    }
}
