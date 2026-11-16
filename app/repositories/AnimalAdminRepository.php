<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

class AnimalAdminRepository extends BaseRepository
{
    public function listar(string $busca, string $status, int $pagina): array
    {
        $where = '1=1';
        $params = [];
        if ($status !== 'todos') {
            $where .= ' AND a.status = :status';
            $params[':status'] = $status;
        }
        if ($busca !== '') {
            $where .= ' AND (a.nome LIKE :animal OR p.nome_fantasia LIKE :responsavel OR u.nome LIKE :usuario)';
            foreach (['animal', 'responsavel', 'usuario'] as $campo) {
                $params[':' . $campo] = '%' . $busca . '%';
            }
        }
        $from = "FROM ANIMAL a
            JOIN PROTETOR p ON p.protetor_id = a.protetor_id
            JOIN USUARIO u ON u.usuario_id = p.usuario_id
            JOIN RACA r ON r.raca_id = a.raca_id
            JOIN ESPECIE e ON e.especie_id = r.especie_id
            WHERE $where";
        $count = $this->db->prepare("SELECT COUNT(*) $from");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $paginas = max(1, (int) ceil($total / 12));
        $pagina = min($pagina, $paginas);
        $stmt = $this->db->prepare("SELECT a.animal_id, a.nome, a.status, a.deletado_em,
            COALESCE(NULLIF(p.nome_fantasia, ''), u.nome) AS responsavel,
            p.tipo_documento, r.nome AS raca, e.nome AS especie,
            (SELECT f.caminho_foto FROM FOTO_ANIMAL f WHERE f.animal_id = a.animal_id
             ORDER BY f.foto_principal DESC, f.foto_id LIMIT 1) AS foto
            $from ORDER BY a.criado_em DESC, a.animal_id DESC LIMIT :limite OFFSET :offset");
        foreach ($params as $key => $value) $stmt->bindValue($key, $value, PDO::PARAM_STR);
        $stmt->bindValue(':limite', 12, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($pagina - 1) * 12, PDO::PARAM_INT);
        $stmt->execute();
        return ['animais' => $stmt->fetchAll(), 'total' => $total, 'pagina' => $pagina, 'paginas' => $paginas];
    }
}

