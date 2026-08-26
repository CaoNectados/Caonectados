<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * RF 08/RF 09 (fluxo de adoção). Status possíveis (ENUM da coluna status_solicitacao):
 * pendente, em_analise, aprovada, reprovada, cancelada.
 */
class SolicitacaoAdocaoRepository extends BaseRepository
{
    // Usado por: SolicitacaoAdocaoService::solicitarAdocao() (UC 15)
    public function criar(int $adotanteId, int $animalId): int
    {
        $sql = "INSERT INTO SOLICITACAO_ADOCAO (adotante_id, animal_id, status_solicitacao)
                VALUES (:adotante_id, :animal_id, 'pendente')";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':adotante_id', $adotanteId, PDO::PARAM_INT);
        $stmt->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Junta ANIMAL + ADOTANTE + USUARIO — usado tanto pra checagem de posse (protetor dono do
     * animal / adotante dono da solicitação) quanto pra montar a tela de detalhes da triagem
     * (UC 18), que mostra dados de moradia/convivência do adotante junto da solicitação.
     */
    // Usado por: SolicitacaoAdocaoService (todas as ações de triagem/cancelamento)
    public function buscarDetalhado(int $solicitacaoId): ?array
    {
        $sql = "SELECT
                    s.solicitacao_id, s.adotante_id, s.animal_id, s.status_solicitacao,
                    s.data_solicitacao, s.justificativa_recusa, s.data_finalizacao,
                    a.nome AS animal_nome, a.protetor_id, a.status AS animal_status,
                    fa.caminho_foto AS animal_foto,
                    ad.usuario_id AS adotante_usuario_id,
                    ad.tipo_moradia, ad.tamanho_interno_moradia, ad.detalhes AS adotante_detalhes,
                    u.nome AS adotante_nome, u.email AS adotante_email, u.dt_nasc AS adotante_dt_nasc,
                    r.nome_regiao AS adotante_regiao
                FROM SOLICITACAO_ADOCAO s
                INNER JOIN ANIMAL a ON a.animal_id = s.animal_id
                INNER JOIN ADOTANTE ad ON ad.adotante_id = s.adotante_id
                INNER JOIN USUARIO u ON u.usuario_id = ad.usuario_id
                LEFT JOIN REGIAO r ON r.regiao_id = u.regiao_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE s.solicitacao_id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $solicitacaoId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    // Usado por: SolicitacaoAdocaoController (tela "Minhas Solicitações" do Adotante — UC 15)
    public function listarPorAdotante(int $adotanteId): array
    {
        $sql = "SELECT
                    s.solicitacao_id, s.animal_id, s.status_solicitacao, s.data_solicitacao,
                    s.justificativa_recusa, s.data_finalizacao,
                    a.nome AS animal_nome, a.protetor_id, a.status AS animal_status,
                    p.nome_fantasia,
                    fa.caminho_foto AS animal_foto
                FROM SOLICITACAO_ADOCAO s
                INNER JOIN ANIMAL a ON a.animal_id = s.animal_id
                INNER JOIN PROTETOR p ON p.protetor_id = a.protetor_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE s.adotante_id = :adotante_id
                ORDER BY s.data_solicitacao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':adotante_id', $adotanteId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Painel do Protetor/ONG (UC 18). $aba agrupa 'pendente' e 'em_analise' numa única aba
     * "Pendentes" — colocar em análise é uma ação de triagem dentro da mesma fila, não um
     * status que sai dela (só aprovar/recusar tira o pedido da aba de pendentes).
     */
    // Usado por: SolicitacaoAdocaoController::painel() (UC 18)
    public function listarRecebidasPorProtetor(int $protetorId, string $aba = 'pendentes'): array
    {
        $sql = "SELECT
                    s.solicitacao_id, s.adotante_id, s.animal_id, s.status_solicitacao,
                    s.data_solicitacao, s.justificativa_recusa, s.data_finalizacao,
                    a.nome AS animal_nome,
                    fa.caminho_foto AS animal_foto,
                    u.nome AS adotante_nome
                FROM SOLICITACAO_ADOCAO s
                INNER JOIN ANIMAL a ON a.animal_id = s.animal_id
                INNER JOIN ADOTANTE ad ON ad.adotante_id = s.adotante_id
                INNER JOIN USUARIO u ON u.usuario_id = ad.usuario_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE a.protetor_id = :protetor_id";

        if ($aba === 'aprovadas') {
            $sql .= " AND s.status_solicitacao = 'aprovada'";
        } elseif ($aba === 'recusadas') {
            $sql .= " AND s.status_solicitacao = 'reprovada'";
        } else {
            $sql .= " AND s.status_solicitacao IN ('pendente', 'em_analise')";
        }

        $sql .= " ORDER BY s.data_solicitacao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: SolicitacaoAdocaoService::solicitarAdocao() — RN 02 (máx. 10 petiscos/dia)
    public function contarSolicitacoesHoje(int $adotanteId): int
    {
        $sql = "SELECT COUNT(*) FROM SOLICITACAO_ADOCAO
                WHERE adotante_id = :adotante_id AND DATE(data_solicitacao) = CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':adotante_id', $adotanteId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * RN 17: um mesmo adotante não pode ter duas manifestações simultâneas ativas
     * (pendente/em_analise/aprovada) pro mesmo animal — mesma regra já aplicada no Feed
     * (FeedRepository) e na Pesquisa (PesquisaRepository), replicada aqui pra validar no
     * momento exato da criação da solicitação.
     */
    // Usado por: SolicitacaoAdocaoService::solicitarAdocao()
    public function existeSolicitacaoAtivaPara(int $adotanteId, int $animalId): bool
    {
        $sql = "SELECT 1 FROM SOLICITACAO_ADOCAO
                WHERE adotante_id = :adotante_id AND animal_id = :animal_id
                  AND status_solicitacao IN ('pendente', 'em_analise', 'aprovada')
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':adotante_id', $adotanteId, PDO::PARAM_INT);
        $stmt->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * RN 08: ao aprovar uma solicitação, todas as OUTRAS pendentes/em_analise pro mesmo animal
     * (de outros adotantes) são canceladas automaticamente. Retorna as linhas afetadas (antes
     * de cancelar) pra quem chamou poder notificar cada adotante e registrar o histórico.
     */
    // Usado por: SolicitacaoAdocaoService::aprovar()
    public function listarOutrasPendentesParaAnimal(int $animalId, int $solicitacaoIdAprovada): array
    {
        $sql = "SELECT s.solicitacao_id, s.adotante_id, s.status_solicitacao, ad.usuario_id AS adotante_usuario_id
                FROM SOLICITACAO_ADOCAO s
                INNER JOIN ADOTANTE ad ON ad.adotante_id = s.adotante_id
                WHERE s.animal_id = :animal_id
                  AND s.solicitacao_id != :solicitacao_id
                  AND s.status_solicitacao IN ('pendente', 'em_analise')";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmt->bindValue(':solicitacao_id', $solicitacaoIdAprovada, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: SolicitacaoAdocaoService (toda transição de status — pendente/em_analise/
    // aprovada/reprovada/cancelada), sempre em conjunto com HistoricoSolicitacaoRepository::registrar()
    public function atualizarStatus(int $solicitacaoId, string $status, ?string $justificativaRecusa = null, bool $finalizar = false): bool
    {
        $sql = "UPDATE SOLICITACAO_ADOCAO
                SET status_solicitacao = :status,
                    justificativa_recusa = COALESCE(:justificativa, justificativa_recusa),
                    data_finalizacao = " . ($finalizar ? "CURRENT_TIMESTAMP" : "data_finalizacao") . "
                WHERE solicitacao_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':justificativa', $justificativaRecusa, $justificativaRecusa === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':id', $solicitacaoId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
