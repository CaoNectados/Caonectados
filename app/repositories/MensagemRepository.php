<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 13. Mensagens de um CHAT (1:N).
 *
 * "Não lida"/"minha" são sempre relativos ao PAPEL de quem está lendo ('adotante' ou
 * 'protetor'), não ao usuario_id sozinho — uma mesma conta pode ter usuario_id igual nos dois
 * lados do chat (ex.: a conta de teste do admin, que acumula perfil de adotante E de
 * protetor/ONG), então comparar só remetente_id != usuario_id não distingue de qual lado a
 * mensagem partiu. Ver ChatService::papelParaPerfil().
 */
class MensagemRepository extends BaseRepository
{
    // Usado por: ChatService::enviarMensagem()
    public function criar(int $chatId, int $remetenteId, string $remetentePerfil, string $texto): int
    {
        $sql = "INSERT INTO MENSAGEM (chat_id, remetente_id, remetente_perfil, texto)
                VALUES (:chat_id, :remetente_id, :remetente_perfil, :texto)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
        $stmt->bindValue(':remetente_id', $remetenteId, PDO::PARAM_INT);
        $stmt->bindValue(':remetente_perfil', $remetentePerfil, PDO::PARAM_STR);
        $stmt->bindValue(':texto', $texto, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: ChatService::enviarMensagem() — devolve a linha recém-criada pro JS renderizar
    public function buscarPorId(int $mensagemId): ?array
    {
        $sql = "SELECT mensagem_id, chat_id, remetente_id, remetente_perfil, texto, lida, data_hora FROM MENSAGEM WHERE mensagem_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $mensagemId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Usado por: ChatService::buscarMensagens() — histórico completo ao abrir a conversa
    public function listarPorChat(int $chatId): array
    {
        $sql = "SELECT mensagem_id, chat_id, remetente_id, remetente_perfil, texto, lida, data_hora
                FROM MENSAGEM WHERE chat_id = :chat_id ORDER BY mensagem_id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: ChatService::buscarMensagensNovas() — polling leve (só o que chegou depois do último id visto no cliente)
    public function listarNovasDesde(int $chatId, int $ultimoMensagemId): array
    {
        $sql = "SELECT mensagem_id, chat_id, remetente_id, remetente_perfil, texto, lida, data_hora
                FROM MENSAGEM WHERE chat_id = :chat_id AND mensagem_id > :ultimo_id ORDER BY mensagem_id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
        $stmt->bindValue(':ultimo_id', $ultimoMensagemId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca como lidas as mensagens do OUTRO papel (quem não é $papelLeitor) nesta conversa.
     * Mensagens antigas sem remetente_perfil definido (anteriores a esta coluna existir) são
     * tratadas como "do outro lado" por padrão, pra não ficarem eternamente presas em "não lida".
     */
    // Usado por: ChatService::buscarMensagens() e buscarMensagensNovas()
    public function marcarComoLidas(int $chatId, string $papelLeitor): void
    {
        $sql = "UPDATE MENSAGEM SET lida = 1
                WHERE chat_id = :chat_id
                  AND lida = 0
                  AND (remetente_perfil IS NULL OR remetente_perfil != :papel)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
        $stmt->bindValue(':papel', $papelLeitor, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Total de mensagens não lidas endereçadas a quem consulta NAQUELE papel específico
     * ('adotante' ou 'protetor') — usado no badge da navbar. Precisa ser por papel, e não só
     * por usuario_id, porque a mesma conta pode ter chats como adotante E como protetor/ONG,
     * e o badge deve refletir só o que é relevante pro chapéu atualmente calçado.
     */
    // Usado por: header.php
    public function contarNaoLidasPorPapel(int $usuarioId, string $papel): int
    {
        $colunaUsuario = $papel === 'adotante' ? 'ad.usuario_id' : 'p.usuario_id';

        $sql = "SELECT COUNT(*)
                FROM MENSAGEM m
                INNER JOIN CHAT c ON c.chat_id = m.chat_id
                INNER JOIN SOLICITACAO_ADOCAO s ON s.solicitacao_id = c.solicitacao_id
                INNER JOIN ANIMAL an ON an.animal_id = s.animal_id
                INNER JOIN ADOTANTE ad ON ad.adotante_id = s.adotante_id
                INNER JOIN PROTETOR p ON p.protetor_id = an.protetor_id
                WHERE m.lida = 0
                  AND (m.remetente_perfil IS NULL OR m.remetente_perfil != :papel)
                  AND {$colunaUsuario} = :usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':papel', $papel, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // Usado por: ChatService — decide se mostra a tela de celebração "CãoNectou!" (só na
    // primeira vez que qualquer uma das partes abre um chat sem nenhuma mensagem ainda)
    public function existeAlgumaMensagem(int $chatId): bool
    {
        $sql = "SELECT 1 FROM MENSAGEM WHERE chat_id = :chat_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}
