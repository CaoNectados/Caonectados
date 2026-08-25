<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

class FeedRepository extends BaseRepository
{
    /**
     * Catálogo de animais disponíveis para adoção (RF 10 / UC 14 e UC 14.1).
     *
     * Regras aplicadas nesta única query (evita N+1 — JOIN + subconsulta correlacionada):
     * - Só animais com status 'disponivel' e nem o animal nem o protetor estão desativados.
     * - RN 17: exclui animais para os quais o adotante já tem uma solicitação ATIVA
     *   (pendente/em_analise/aprovada) — via NOT EXISTS, sem carregar as solicitações em PHP.
     * - Se o adotante tem preferências definidas (porte/sexo/espécie/raça, vindas do
     *   detalhes JSON do onboarding/edição de perfil), calcula um score de compatibilidade
     *   e ordena por ele primeiro; animais empatados (inclusive quando não há preferência
     *   nenhuma, score sempre 0) saem em ordem "aleatória" estável — ver comentário na
     *   clausulaOrdemEstavel() sobre por que não uso RAND(seed) puro.
     */
    // Usado por: FeedController::index() e carregarMais()
    public function buscarFeed(int $adotanteId, array $preferencias, array $filtros, int $seed, int $offset, int $limite): array
    {
        $params = [':adotante_id' => $adotanteId];

        $scoreSql = $this->montarScorePreferencia($preferencias, $params);
        $whereSql = $this->montarFiltros($filtros, $params);

        $sql = "SELECT
                    a.animal_id, a.protetor_id, a.raca_id, a.nome, a.dt_nasc, a.sexo, a.porte,
                    a.status, a.descricao, a.vacinado, a.castrado, a.comportamento, a.criado_em,
                    r.nome AS raca_nome, r.especie_id,
                    e.nome AS especie_nome,
                    p.nome_fantasia,
                    reg.nome_regiao,
                    fa.caminho_foto AS foto_principal,
                    ($scoreSql) AS score_preferencia
                FROM ANIMAL a
                INNER JOIN RACA r ON r.raca_id = a.raca_id
                INNER JOIN ESPECIE e ON e.especie_id = r.especie_id
                INNER JOIN PROTETOR p ON p.protetor_id = a.protetor_id AND p.deletado_em IS NULL
                INNER JOIN USUARIO u ON u.usuario_id = p.usuario_id
                LEFT JOIN REGIAO reg ON reg.regiao_id = u.regiao_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                WHERE a.status = 'disponivel'
                  AND a.deletado_em IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM SOLICITACAO_ADOCAO sa
                      WHERE sa.animal_id = a.animal_id
                        AND sa.adotante_id = :adotante_id
                        AND sa.status_solicitacao IN ('pendente', 'em_analise', 'aprovada')
                  )
                  $whereSql
                ORDER BY score_preferencia DESC, " . $this->clausulaOrdemEstavel() . "
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }
        $stmt->bindValue(':seed', $seed, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Usado por: FeedController — carrega todas as fotos (carrossel) dos animais de UMA
    // página do feed em uma única query (WHERE IN), evitando 1 query por card.
    public function buscarFotosPorAnimais(array $animalIds): array
    {
        if (empty($animalIds)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($animalIds) as $indice => $id) {
            $chave = ":id{$indice}";
            $placeholders[] = $chave;
            $params[$chave] = (int) $id;
        }

        $sql = "SELECT foto_id, animal_id, caminho_foto, foto_principal
                FROM FOTO_ANIMAL
                WHERE animal_id IN (" . implode(',', $placeholders) . ")
                ORDER BY foto_principal DESC, foto_id ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, PDO::PARAM_INT);
        }
        $stmt->execute();

        $porAnimal = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $porAnimal[(int) $linha['animal_id']][] = $linha;
        }

        return $porAnimal;
    }

    // Usado por: buscarFeed() (uso interno) — monta a expressão de score de compatibilidade
    // com base nas preferências do adotante. Cada preferência vazia soma 0 (CASE sempre falso
    // com WHERE 1=0), então quem não preencheu nada cai tudo empatado em score 0 — normal.
    private function montarScorePreferencia(array $preferencias, array &$params): string
    {
        $partes = [];

        $partes[] = $this->clausulaInOuFalso('a.porte', $preferencias['porte'] ?? [], 'pref_porte', $params, PDO::PARAM_STR);
        $partes[] = $this->clausulaInOuFalso('a.sexo', $preferencias['sexo'] ?? [], 'pref_sexo', $params, PDO::PARAM_STR);
        $partes[] = $this->clausulaInOuFalso('r.especie_id', $preferencias['especie_id'] ?? [], 'pref_especie', $params, PDO::PARAM_INT);
        $partes[] = $this->clausulaInOuFalso('a.raca_id', $preferencias['raca_id'] ?? [], 'pref_raca', $params, PDO::PARAM_INT);

        return implode(' + ', array_map(fn(string $c) => "(CASE WHEN $c THEN 1 ELSE 0 END)", $partes));
    }

    // Usado por: buscarFeed() (uso interno) — filtros avançados do UC 14.1
    private function montarFiltros(array $filtros, array &$params): string
    {
        $sql = '';

        if (!empty($filtros['porte'])) {
            $sql .= " AND a.porte = :f_porte";
            $params[':f_porte'] = $filtros['porte'];
        }
        if (!empty($filtros['sexo'])) {
            $sql .= " AND a.sexo = :f_sexo";
            $params[':f_sexo'] = $filtros['sexo'];
        }
        if (isset($filtros['castrado']) && $filtros['castrado'] !== '') {
            $sql .= " AND a.castrado = :f_castrado";
            $params[':f_castrado'] = (int) $filtros['castrado'];
        }
        if (isset($filtros['vacinado']) && $filtros['vacinado'] !== '') {
            $sql .= " AND a.vacinado = :f_vacinado";
            $params[':f_vacinado'] = (int) $filtros['vacinado'];
        }
        if (!empty($filtros['regiao_id'])) {
            $sql .= " AND u.regiao_id = :f_regiao_id";
            $params[':f_regiao_id'] = (int) $filtros['regiao_id'];
        }
        if (!empty($filtros['especie_id'])) {
            $sql .= " AND r.especie_id = :f_especie_id";
            $params[':f_especie_id'] = (int) $filtros['especie_id'];
        }
        if (!empty($filtros['raca_id'])) {
            $sql .= " AND a.raca_id = :f_raca_id";
            $params[':f_raca_id'] = (int) $filtros['raca_id'];
        }
        if (!empty($filtros['protetor_id'])) {
            $sql .= " AND a.protetor_id = :f_protetor_id";
            $params[':f_protetor_id'] = (int) $filtros['protetor_id'];
        }

        return $sql;
    }

    // Usado por: montarScorePreferencia() (uso interno) — gera "coluna IN (:p0,:p1,...)" pra
    // uma lista de valores, ou a constante falsa "1=0" quando a lista está vazia (PDO não
    // aceita bind direto de array em IN(), por isso os placeholders numerados).
    private function clausulaInOuFalso(string $coluna, array $valores, string $prefixo, array &$params, int $tipoPdo): string
    {
        $valores = array_values(array_filter($valores, fn($v) => $v !== null && $v !== ''));
        if (empty($valores)) {
            return '1 = 0';
        }

        $placeholders = [];
        foreach ($valores as $indice => $valor) {
            $chave = ":{$prefixo}_{$indice}";
            $placeholders[] = $chave;
            $params[$chave] = $tipoPdo === PDO::PARAM_INT ? (int) $valor : (string) $valor;
        }

        return "$coluna IN (" . implode(',', $placeholders) . ")";
    }

    /**
     * "Aleatório" estável entre páginas: em vez de ORDER BY RAND(seed) — que reembaralha a
     * cada execução porque a ordem de varredura das linhas antes do ORDER BY não é garantida,
     * podendo repetir/pular animais entre uma página e outra do scroll infinito — usa um hash
     * determinístico de (animal_id, seed). Pro MESMO seed, o MESMO animal_id sempre cai na
     * mesma posição relativa, então LIMIT/OFFSET em requisições separadas não se desalinha;
     * seeds diferentes (uma por sessão de navegação no feed) embaralham a ordem normalmente.
     */
    // Usado por: buscarFeed() (uso interno). O placeholder :seed é bindado separadamente em
    // buscarFeed() (com PDO::PARAM_INT explícito), não faz parte do array $params genérico.
    private function clausulaOrdemEstavel(): string
    {
        return "MOD(CRC32(CONCAT(a.animal_id, '-', :seed)), 1000003)";
    }
}
