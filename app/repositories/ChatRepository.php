<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 13 (UC 03/03.1). RN 09: um CHAT só existe vinculado a uma SOLICITACAO_ADOCAO aprovada.
 * RN 10: o status vira 'encerrado'/'arquivado' se a solicitação for cancelada depois ou se
 * alguma das partes encerrar manualmente.
 */
class ChatRepository extends BaseRepository
{
    private const SELECT_BASE = "
        SELECT
            c.chat_id, c.solicitacao_id, c.status, c.criado_em,
            s.data_finalizacao AS data_vinculo,
            an.animal_id, an.nome AS animal_nome, fa.caminho_foto AS animal_foto,
            ad.usuario_id AS adotante_usuario_id, ua.nome AS adotante_nome, ad.foto_perfil AS adotante_foto,
            p.protetor_id, p.usuario_id AS protetor_usuario_id, p.nome_fantasia, pag.foto_perfil AS protetor_foto
        FROM CHAT c
        INNER JOIN SOLICITACAO_ADOCAO s ON s.solicitacao_id = c.solicitacao_id
        INNER JOIN ANIMAL an ON an.animal_id = s.animal_id
        INNER JOIN ADOTANTE ad ON ad.adotante_id = s.adotante_id
        INNER JOIN USUARIO ua ON ua.usuario_id = ad.usuario_id
        INNER JOIN PROTETOR p ON p.protetor_id = an.protetor_id
        LEFT JOIN PAGINA pag ON pag.protetor_id = p.protetor_id
        LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = an.animal_id AND fa.foto_principal = 1
    ";

    // Usado por: SolicitacaoAdocaoService::aprovar() (RN 09)
    public function criarParaSolicitacao(int $solicitacaoId): int
    {
        $sql = "INSERT INTO CHAT (solicitacao_id, status) VALUES (:solicitacao_id, 'ativo')";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':solicitacao_id', $solicitacaoId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    // Usado por: SolicitacaoAdocaoService::registrarDevolucao() (RN 10 — encerra o chat quando
    // a adoção é desfeita)
    public function buscarPorSolicitacaoId(int $solicitacaoId): ?array
    {
        $sql = "SELECT chat_id, solicitacao_id, status FROM CHAT WHERE solicitacao_id = :solicitacao_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':solicitacao_id', $solicitacaoId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Junta tudo que a tela de conversa/celebração ("CãoNectou!") precisa pra exibir os dois
     * lados do vínculo, e também é a base da checagem de RBAC (adotante_usuario_id /
     * protetor_usuario_id) — só quem aparece nessas duas colunas pode acessar o chat.
     */
    // Usado por: ChatService::obterChatParaUsuario()
    public function buscarDetalhado(int $chatId): ?array
    {
        $sql = self::SELECT_BASE . " WHERE c.chat_id = :chat_id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Filtra pelo PAPEL específico ('adotante' ou 'protetor'), não por "usuario_id em qualquer
     * lado" — uma conta com usuario_id igual nos dois lados (ex.: admin de teste com perfil de
     * adotante E de protetor/ONG) só deve ver, em cada momento, os chats do chapéu que está
     * calçado agora (ver ChatService::papelParaPerfil()).
     */
    // Usado por: ChatService::listarConversas()
    public function listarPorPapel(int $usuarioId, string $papel): array
    {
        $coluna = $papel === 'adotante' ? 'ad.usuario_id' : 'p.usuario_id';

        $sql = self::SELECT_BASE . "
            WHERE {$coluna} = :usuario_id
            ORDER BY c.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: ChatController::encerrar() (RN 10 — encerramento manual)
    public function atualizarStatus(int $chatId, string $status): bool
    {
        $sql = "UPDATE CHAT SET status = :status WHERE chat_id = :chat_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
