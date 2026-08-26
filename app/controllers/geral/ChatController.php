<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\services\ChatService;
use Exception;

/**
 * RF 13 (UC 03 / UC 03.1 — "CãoNectou!"). O chat só existe depois de uma adoção aprovada
 * (RN 09, ver SolicitacaoAdocaoService::aprovar()); este controller cobre a lista de
 * conversas, a tela de mensagens e o encerramento manual (RN 10).
 *
 * Toda ação resolve o PAPEL ativo (adotante/protetor) a partir do tipo_perfil da sessão e
 * passa pro service — não basta o usuario_id bater com um dos lados do chat, precisa ser
 * exatamente o chapéu calçado agora (ver ChatService::papelParaPerfil()).
 */
class ChatController extends Controller
{
    private ChatService $service;

    public function __construct()
    {
        $this->autenticacaoRequired(['adotante', 'protetor', 'ong']);
        $this->service = new ChatService();
    }

    /** Lista as conversas do usuário logado. Usado pela rota GET /chats. */
    public function listar(): void
    {
        $papel = $this->papelAtivo();
        $conversas = $this->service->listarConversas((int) $_SESSION['usuario_id'], $papel);

        $this->view('chat/lista', [
            'titulo'     => 'Conversas',
            'conversas'  => $conversas,
            'papelAtual' => $papel,
        ]);
    }

    /** Exibe a tela de mensagens de uma conversa específica. Usado pela rota GET /chats/conversa. */
    public function conversa(): void
    {
        try {
            $chatId = (int) ($_GET['id'] ?? 0);
            $usuarioId = (int) $_SESSION['usuario_id'];
            $papel = $this->papelAtivo();

            $chat = $this->service->obterChatParaUsuario($chatId, $usuarioId, $papel);
            $mensagens = $this->service->buscarMensagens($chatId, $usuarioId, $papel);

            $this->view('chat/conversa', [
                'titulo'     => 'Chat',
                'chat'       => $chat,
                'mensagens'  => $mensagens,
                'usuarioId'  => $usuarioId,
                'papelAtual' => $papel,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível abrir esta conversa.', '/chats', $e->getMessage());
        }
    }

    /** Envia uma mensagem numa conversa. Usado pela rota POST /chats/enviar (AJAX). */
    public function enviar(): void
    {
        try {
            $chatId = (int) ($_POST['chat_id'] ?? 0);
            $texto = (string) ($_POST['texto'] ?? '');

            $mensagem = $this->service->enviarMensagem($chatId, (int) $_SESSION['usuario_id'], $this->papelAtivo(), $texto);

            $this->json(200, ['status' => 'sucesso', 'mensagem' => $mensagem]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Retorna as mensagens novas de uma conversa desde um id, para polling leve. Usado pela rota GET /chats/novas-mensagens (AJAX). */
    public function novasMensagens(): void
    {
        try {
            $chatId = (int) ($_GET['id'] ?? 0);
            $depoisDe = (int) ($_GET['depois_de'] ?? 0);

            $novas = $this->service->buscarMensagensNovas($chatId, (int) $_SESSION['usuario_id'], $this->papelAtivo(), $depoisDe);

            $this->json(200, ['status' => 'sucesso', 'mensagens' => $novas]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Encerra manualmente uma conversa (RN 10 — por qualquer uma das partes). Usado pela rota POST /chats/encerrar. */
    public function encerrar(): void
    {
        try {
            $chatId = (int) ($_POST['id'] ?? 0);
            $this->service->encerrar($chatId, (int) $_SESSION['usuario_id'], $this->papelAtivo());

            $this->redirecionarComMensagem('sucesso', 'Conversa encerrada.', '/chats');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível encerrar a conversa.', '/chats', $e->getMessage());
        }
    }

    /** Resolve o papel de chat (adotante/protetor) do chapéu ativo na sessão agora. Usado por todas as ações. */
    private function papelAtivo(): ?string
    {
        return ChatService::papelParaPerfil($_SESSION['tipo_perfil'] ?? '');
    }
}
