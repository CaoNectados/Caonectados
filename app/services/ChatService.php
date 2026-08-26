<?php

namespace app\services;

use app\repositories\ChatRepository;
use app\repositories\MensagemRepository;
use Exception;

/**
 * RF 13 (UC 03/03.1).
 *
 * RBAC estrito por PAPEL, não só por usuario_id: uma mesma conta pode ter usuario_id igual nos
 * dois lados de um chat (ex.: a conta de teste do admin, que acumula perfil de adotante E de
 * protetor/ONG no mesmo usuario_id) — então "usuario_id bate com um dos dois lados" não basta.
 * Cada ação verifica que o CHAPÉU ATIVO no momento (tipo_perfil da sessão, resolvido via
 * papelParaPerfil()) é exatamente o lado que aquele usuario_id está tentando acionar. Um
 * usuário com o perfil "administrador" ativo, por exemplo, não consegue acessar chat nenhum,
 * mesmo que o mesmo usuario_id apareça como adotante ou protetor por trás da conta.
 */
class ChatService
{
    private ChatRepository $chatRepo;
    private MensagemRepository $mensagemRepo;
    private NotificacaoService $notificacaoService;

    public function __construct()
    {
        $this->chatRepo = new ChatRepository();
        $this->mensagemRepo = new MensagemRepository();
        $this->notificacaoService = new NotificacaoService();
    }

    /**
     * Normaliza tipo_perfil da sessão pro papel de chat correspondente. 'protetor' e 'ong' são
     * o mesmo lado do chat (a distinção é só o tipo de documento no cadastro da PROTETOR).
     * Qualquer outro perfil (administrador, usuario) não tem papel em chat nenhum.
     */
    // Usado por: ChatController (todas as ações) e header.php (badge da navbar)
    public static function papelParaPerfil(string $tipoPerfil): ?string
    {
        return match ($tipoPerfil) {
            'adotante' => 'adotante',
            'protetor', 'ong' => 'protetor',
            default => null,
        };
    }

    // Usado por: ChatController (todas as ações) — RBAC + dados pro cabeçalho/celebração
    public function obterChatParaUsuario(int $chatId, int $usuarioId, ?string $papel): array
    {
        if ($papel === null) {
            throw new Exception('Troque para o perfil de Adotante ou Protetor/ONG para acessar o chat.');
        }

        $chat = $this->chatRepo->buscarDetalhado($chatId);
        if (!$chat) {
            throw new Exception('Conversa não encontrada.');
        }

        $idEsperado = $papel === 'adotante' ? (int) $chat['adotante_usuario_id'] : (int) $chat['protetor_usuario_id'];
        if ($idEsperado !== $usuarioId) {
            throw new Exception('Você não tem permissão para acessar esta conversa.');
        }

        $chat['meu_papel'] = $papel;
        return $chat;
    }

    // Usado por: ChatController::listar() — lista de conversas com badge de não lidas por item
    public function listarConversas(int $usuarioId, ?string $papel): array
    {
        if ($papel === null) {
            return [];
        }

        $chats = $this->chatRepo->listarPorPapel($usuarioId, $papel);

        return array_map(function (array $chat) use ($papel) {
            $mensagens = $this->mensagemRepo->listarPorChat((int) $chat['chat_id']);
            $ultima = end($mensagens);

            $chat['ultima_mensagem'] = $ultima ? $ultima['texto'] : null;
            $chat['ultima_data'] = $ultima ? $ultima['data_hora'] : $chat['criado_em'];
            $chat['nao_lidas'] = count(array_filter(
                $mensagens,
                fn(array $m) => ($m['remetente_perfil'] === null || $m['remetente_perfil'] !== $papel) && !$m['lida']
            ));

            return $chat;
        }, $chats);
    }

    // Usado por: ChatController::conversa() — histórico completo, marcando como lidas as da outra parte
    public function buscarMensagens(int $chatId, int $usuarioId, ?string $papel): array
    {
        $this->obterChatParaUsuario($chatId, $usuarioId, $papel);
        $this->mensagemRepo->marcarComoLidas($chatId, $papel);
        return $this->mensagemRepo->listarPorChat($chatId);
    }

    // Usado por: ChatController::novasMensagens() — polling leve (AJAX)
    public function buscarMensagensNovas(int $chatId, int $usuarioId, ?string $papel, int $depoisDeId): array
    {
        $this->obterChatParaUsuario($chatId, $usuarioId, $papel);
        $novas = $this->mensagemRepo->listarNovasDesde($chatId, $depoisDeId);

        if (!empty($novas)) {
            $this->mensagemRepo->marcarComoLidas($chatId, $papel);
        }

        return $novas;
    }

    // Usado por: ChatController::enviar()
    public function enviarMensagem(int $chatId, int $usuarioId, ?string $papel, string $texto): array
    {
        $chat = $this->obterChatParaUsuario($chatId, $usuarioId, $papel);

        if ($chat['status'] !== 'ativo') {
            throw new Exception('Esta conversa está encerrada.');
        }

        $texto = trim($texto);
        if ($texto === '') {
            throw new Exception('Escreva uma mensagem antes de enviar.');
        }

        $mensagemId = $this->mensagemRepo->criar($chatId, $usuarioId, $papel, $texto);

        // RF 11 / RN 06: notifica a outra parte. Não dá pra saber com certeza se ela está com
        // o chat aberto nesse instante (sem presença em tempo real) — notifica sempre, mesmo
        // padrão já usado nas notificações de solicitação de adoção.
        $destinatarioId = $papel === 'adotante' ? (int) $chat['protetor_usuario_id'] : (int) $chat['adotante_usuario_id'];
        $this->notificacaoService->notificarNovaMensagem($destinatarioId, $chat['animal_nome'], $chatId);

        return $this->mensagemRepo->buscarPorId($mensagemId);
    }

    // Usado por: ChatController::encerrar() (RN 10 — encerramento manual por qualquer uma das partes)
    public function encerrar(int $chatId, int $usuarioId, ?string $papel): void
    {
        $chat = $this->obterChatParaUsuario($chatId, $usuarioId, $papel);

        if ($chat['status'] !== 'ativo') {
            return;
        }

        $this->chatRepo->atualizarStatus($chatId, 'encerrado');

        $destinatarioId = $papel === 'adotante' ? (int) $chat['protetor_usuario_id'] : (int) $chat['adotante_usuario_id'];
        $this->notificacaoService->notificarChatEncerrado($destinatarioId, $chat['animal_nome'], $chatId);
    }
}
