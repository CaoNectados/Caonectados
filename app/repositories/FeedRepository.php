<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

class FeedRepository extends BaseRepository
{
    /**
     * Busca os animais que estarão visíveis no feed.
     */
    // Usado por: FeedController::index()
    public function buscarAnimaisFeed(int $limite = 10): array
    {
        $sql = "SELECT a.*, r.nome AS raca_nome, p.nome_fantasia, fa.caminho_foto AS foto_principal
                FROM ANIMAL a
                JOIN RACA r ON a.raca_id = r.raca_id
                JOIN PROTETOR p ON a.protetor_id = p.protetor_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE a.status = 'disponivel' AND a.deletado_em IS NULL
                ORDER BY a.criado_em DESC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}