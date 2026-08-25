<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

/**
 * Busca é diferente por tipo de perfil (RN implícita da tela de Pesquisa): Adotante busca
 * animais disponíveis + ONGs/Protetores; Protetor/ONG busca só os próprios animais;
 * Admin busca usuários e entidades cadastradas na plataforma inteira.
 */
class PesquisaRepository extends BaseRepository
{
    // ===================== ADOTANTE =====================

    /**
     * Mesma regra do Feed (RN 17 via NOT EXISTS, só 'disponivel') — a busca é o catálogo
     * filtrado por texto, não um universo à parte. O termo casa contra nome do animal,
     * raça, espécie, porte/sexo/comportamento (valores crus do ENUM) e a faixa etária
     * calculada (filhote/jovem/adulto/idoso, mesmo critério usado nos relatórios).
     */
    // Usado por: PesquisaController (perfil Adotante)
    public function buscarAnimaisDisponiveisPorTermo(string $termo, int $adotanteId, int $limite = 24): array
    {
        $sql = "SELECT
                    a.animal_id, a.protetor_id, a.nome, a.dt_nasc, a.porte, a.sexo,
                    r.nome AS raca_nome, e.nome AS especie_nome, p.nome_fantasia,
                    fa.caminho_foto AS foto_principal
                FROM ANIMAL a
                INNER JOIN RACA r ON r.raca_id = a.raca_id
                INNER JOIN ESPECIE e ON e.especie_id = r.especie_id
                INNER JOIN PROTETOR p ON p.protetor_id = a.protetor_id AND p.deletado_em IS NULL
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE a.status = 'disponivel'
                  AND a.deletado_em IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM SOLICITACAO_ADOCAO sa
                      WHERE sa.animal_id = a.animal_id
                        AND sa.adotante_id = :adotante_id
                        AND sa.status_solicitacao IN ('pendente', 'em_analise', 'aprovada')
                  )
                  AND (
                        a.nome LIKE :termo1
                     OR r.nome LIKE :termo2
                     OR e.nome LIKE :termo3
                     OR a.porte LIKE :termo4
                     OR a.sexo LIKE :termo5
                     OR a.comportamento LIKE :termo6
                     OR p.nome_fantasia LIKE :termo7
                     OR (CASE
                            WHEN a.dt_nasc IS NULL THEN ''
                            WHEN TIMESTAMPDIFF(MONTH, a.dt_nasc, CURDATE()) < 12 THEN 'filhote'
                            WHEN TIMESTAMPDIFF(YEAR, a.dt_nasc, CURDATE()) BETWEEN 1 AND 3 THEN 'jovem'
                            WHEN TIMESTAMPDIFF(YEAR, a.dt_nasc, CURDATE()) BETWEEN 4 AND 7 THEN 'adulto'
                            ELSE 'idoso'
                         END) LIKE :termo8
                  )
                ORDER BY a.criado_em DESC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':adotante_id', $adotanteId, PDO::PARAM_INT);
        $curinga = "%{$termo}%";
        foreach (range(1, 8) as $i) {
            $stmt->bindValue(":termo{$i}", $curinga, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: PesquisaController (perfil Adotante) — ONGs/Protetores validados cujo nome bate
    public function buscarOngsPorTermo(string $termo, int $limite = 8): array
    {
        $sql = "SELECT p.protetor_id, p.nome_fantasia, p.tipo_documento, pag.foto_perfil
                FROM PROTETOR p
                LEFT JOIN PAGINA pag ON pag.protetor_id = p.protetor_id
                WHERE p.validado = 1
                  AND p.deletado_em IS NULL
                  AND p.nome_fantasia LIKE :termo
                ORDER BY p.nome_fantasia ASC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':termo', "%{$termo}%", PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===================== PROTETOR / ONG =====================

    // Usado por: PesquisaController (perfil Protetor/ONG) — só os próprios animais
    public function buscarAnimaisDoProtetorPorTermo(string $termo, int $protetorId, int $limite = 24): array
    {
        $sql = "SELECT
                    a.animal_id, a.nome, a.status, a.porte,
                    r.nome AS raca_nome,
                    fa.caminho_foto AS foto_principal
                FROM ANIMAL a
                INNER JOIN RACA r ON r.raca_id = a.raca_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE a.protetor_id = :protetor_id
                  AND a.deletado_em IS NULL
                  AND (a.nome LIKE :termo1 OR r.nome LIKE :termo2 OR a.status LIKE :termo3)
                ORDER BY a.criado_em DESC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $curinga = "%{$termo}%";
        $stmt->bindValue(':termo1', $curinga, PDO::PARAM_STR);
        $stmt->bindValue(':termo2', $curinga, PDO::PARAM_STR);
        $stmt->bindValue(':termo3', $curinga, PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===================== ADMIN =====================

    // Usado por: PesquisaController (perfil Admin) — usuários por nome, e-mail ou ID
    public function buscarUsuariosPorTermoAdmin(string $termo, int $limite = 15): array
    {
        $ehNumerico = ctype_digit($termo);

        $sql = "SELECT usuario_id, nome, email, tipo_atual, status_conta
                FROM USUARIO
                WHERE deletado_em IS NULL
                  AND (nome LIKE :termo1 OR email LIKE :termo2" . ($ehNumerico ? " OR usuario_id = :id" : "") . ")
                ORDER BY usuario_id DESC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $curinga = "%{$termo}%";
        $stmt->bindValue(':termo1', $curinga, PDO::PARAM_STR);
        $stmt->bindValue(':termo2', $curinga, PDO::PARAM_STR);
        if ($ehNumerico) {
            $stmt->bindValue(':id', (int) $termo, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: PesquisaController (perfil Admin) — ONGs/Protetores em qualquer status
    // (diferente de buscarOngsPorTermo(), que só mostra validados pro Adotante)
    public function buscarProtetoresPorTermoAdmin(string $termo, int $limite = 15): array
    {
        $ehNumerico = ctype_digit($termo);

        $sql = "SELECT p.protetor_id, p.nome_fantasia, p.validado, p.tipo_documento, u.email
                FROM PROTETOR p
                INNER JOIN USUARIO u ON u.usuario_id = p.usuario_id
                WHERE p.deletado_em IS NULL
                  AND (p.nome_fantasia LIKE :termo1 OR u.email LIKE :termo2" . ($ehNumerico ? " OR p.protetor_id = :id" : "") . ")
                ORDER BY p.protetor_id DESC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $curinga = "%{$termo}%";
        $stmt->bindValue(':termo1', $curinga, PDO::PARAM_STR);
        $stmt->bindValue(':termo2', $curinga, PDO::PARAM_STR);
        if ($ehNumerico) {
            $stmt->bindValue(':id', (int) $termo, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
