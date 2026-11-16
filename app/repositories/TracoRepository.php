<?php

namespace app\repositories;

use app\core\BaseRepository;
use InvalidArgumentException;
use PDO;

class TracoRepository extends BaseRepository
{
    public const COMPORTAMENTOS = ['calmo' => 'Calmo', 'ativo' => 'Ativo', 'docil' => 'Dócil', 'arisco' => 'Arisco'];

    public function listar(): array
    {
        return $this->db->query('SELECT traco_id, traco FROM TRACO ORDER BY traco, traco_id')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validarSelecao($valores): array
    {
        if (!is_array($valores) || !array_is_list($valores) || count($valores) > 30) {
            throw new InvalidArgumentException('Selecione até 30 características válidas.');
        }
        $ids = [];
        foreach ($valores as $valor) {
            if ((!is_int($valor) && !is_string($valor)) || !ctype_digit((string) $valor) || (int) $valor < 1) {
                throw new InvalidArgumentException('Característica inválida.');
            }
            $ids[] = (int) $valor;
        }
        $ids = array_values(array_unique($ids));
        if (array_diff($ids, array_map('intval', array_column($this->listar(), 'traco_id')))) {
            throw new InvalidArgumentException('Uma das características selecionadas não está cadastrada.');
        }
        return $ids;
    }

    /** opcao_id guarda "sim" quando o traço está presente; ausência não significa "não". */
    public function salvar(int $animalId, array $ids): void
    {
        $ids = $this->validarSelecao($ids);
        $this->db->prepare('DELETE FROM ANIMAL_TRACO WHERE animal_id = ?')->execute([$animalId]);
        $stmt = $this->db->prepare("INSERT INTO ANIMAL_TRACO (animal_id, traco_id, opcao_id) VALUES (?, ?, 'sim')");
        foreach ($ids as $id) $stmt->execute([$animalId, $id]);
    }

    public function buscarPorAnimais(array $ids): array
    {
        if (!$ids) return [];
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT DISTINCT at.animal_id, t.traco_id, t.traco
            FROM ANIMAL_TRACO at JOIN TRACO t ON t.traco_id = at.traco_id
            WHERE at.animal_id IN ($marcadores) AND at.opcao_id = 'sim' ORDER BY t.traco, t.traco_id");
        $stmt->execute(array_map('intval', $ids));
        $resultado = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $resultado[(int) $linha['animal_id']][] = ['traco_id' => (int) $linha['traco_id'], 'traco' => $linha['traco']];
        }
        return $resultado;
    }

    public function idsDoComportamento(?string $comportamento): array
    {
        $rotulo = self::COMPORTAMENTOS[$comportamento ?? ''] ?? null;
        if ($rotulo === null) return [];
        foreach ($this->listar() as $traco) {
            if ($traco['traco'] === $rotulo) return [(int) $traco['traco_id']];
        }
        return [];
    }

    /** Campo legado é um resumo de compatibilidade; a seleção completa está em ANIMAL_TRACO. */
    public function comportamentoResumo(array $ids): ?string
    {
        foreach ($this->listar() as $traco) {
            if (!in_array((int) $traco['traco_id'], $ids, true)) continue;
            $valor = array_search($traco['traco'], self::COMPORTAMENTOS, true);
            if ($valor !== false) return $valor;
        }
        return null;
    }
}
