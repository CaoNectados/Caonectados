<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\services\NotificacaoService;
use Exception;

/**
 * RF 11 (UC 06). Tela central de notificações — qualquer perfil autenticado (adotante,
 * protetor/ONG ou administrador) pode ter notificações endereçadas a ele.
 */
class NotificacaoController extends Controller
{
    private NotificacaoService $service;

    private const TAMANHO_PAGINA = 15;

    public function __construct()
    {
        $this->autenticacaoRequired();
        $this->service = new NotificacaoService();
    }

    /** Lista a primeira página de notificações do usuário logado. Usado pela rota GET /notificacoes. */
    public function index(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $notificacoes = $this->service->listarPagina($usuarioId, self::TAMANHO_PAGINA, 0);

        $this->view('notificacao/index', [
            'titulo'        => 'Notificações',
            'notificacoes'  => $notificacoes,
            'temMais'       => count($notificacoes) === self::TAMANHO_PAGINA,
            'proximoOffset' => self::TAMANHO_PAGINA,
        ]);
    }

    /** Retorna a próxima página de notificações. Usado pela rota GET /notificacoes/carregar-mais (AJAX — botão "Ver mais..."). */
    public function carregarMais(): void
    {
        try {
            $usuarioId = (int) $_SESSION['usuario_id'];
            $offset = max(0, (int) ($_GET['offset'] ?? 0));

            $notificacoes = $this->service->listarPagina($usuarioId, self::TAMANHO_PAGINA, $offset);

            $this->json(200, [
                'status'        => 'sucesso',
                'notificacoes'  => $notificacoes,
                'temMais'       => count($notificacoes) === self::TAMANHO_PAGINA,
                'proximoOffset' => $offset + self::TAMANHO_PAGINA,
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /**
     * Marca uma notificação como lida e redireciona pro contexto de origem (a partir de
     * tipo_notificacao + referencia_id). Usado pela rota GET /notificacoes/abrir (clique num card).
     */
    public function abrir(): void
    {
        try {
            $notificacaoId = (int) ($_GET['id'] ?? 0);
            $usuarioId = (int) $_SESSION['usuario_id'];

            $notificacao = $this->service->abrir($notificacaoId, $usuarioId);

            $referenciaId = $notificacao['referencia_id'] !== null ? (int) $notificacao['referencia_id'] : null;
            $tipoPerfil = (string) ($_SESSION['tipo_perfil'] ?? '');

            $rota = NotificacaoService::resolverRotaDestino($notificacao['tipo_notificacao'], $referenciaId, $tipoPerfil);

            $this->redirect($rota);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível abrir essa notificação.', '/notificacoes', $e->getMessage());
        }
    }
}
