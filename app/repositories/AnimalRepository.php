<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Animal;
use PDO;
use PDOStatement;

class AnimalRepository extends BaseRepository
{
    /** Apenas dados públicos do anúncio; não inclui contatos ou documentos privados. */
    public function buscarPostParaDenuncia(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT a.animal_id, a.nome, a.protetor_id,
            r.nome AS raca_nome, e.nome AS especie_nome, p.nome_fantasia
            FROM ANIMAL a JOIN PROTETOR p ON p.protetor_id=a.protetor_id
            JOIN USUARIO u ON u.usuario_id=p.usuario_id
            JOIN RACA r ON r.raca_id=a.raca_id JOIN ESPECIE e ON e.especie_id=r.especie_id
            WHERE a.animal_id=? AND a.deletado_em IS NULL AND a.status<>'desativado'
            AND p.validado=1 AND p.deletado_em IS NULL
            AND u.status_conta='ativo' AND u.deletado_em IS NULL
            AND FIND_IN_SET(IF(p.tipo_documento='cnpj','ong','protetor'),u.perfis_ativos)>0");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorId(int $id): ?Animal
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome,
            fa.caminho_foto AS foto_principal
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
        WHERE a.animal_id = :animal_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':animal_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) return null;
        $animal = $this->mapAnimal($row);
        $tracos = new TracoRepository($this->db);
        $animal->setTracos($tracos->buscarPorAnimais([$id])[$id] ?? []);
        if (!$animal->getTracos() && $animal->getComportamento()) {
            $ids = $tracos->idsDoComportamento($animal->getComportamento());
            $animal->setTracos(array_values(array_filter($tracos->listar(), fn($t) => in_array((int) $t['traco_id'], $ids, true))));
        }
        return $animal;
    }

    public function listarComFiltros(string $tipoPerfil, int $protetorId, string $status = 'todos'): array
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome,
            fa.caminho_foto AS foto_principal
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
        WHERE (a.deletado_em IS NULL OR a.status = 'desativado')";

        $params = [];

        if ($tipoPerfil !== 'administrador') {
            $sql .= " AND a.protetor_id = :protetor_id";
            $params[':protetor_id'] = $protetorId;
        }

        if ($status !== 'todos' && !empty($status)) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY a.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row) => $this->mapAnimal($row), $rows);
    }

    /** Lista animais adotados com o nome e a foto pública do adotante da solicitação aprovada. */
    public function listarAdotadosComAdotante(int $protetorId): array
    {
        $sql = "SELECT a.animal_id, a.protetor_id, a.raca_id, a.nome, a.dt_nasc, a.sexo,
                    a.porte, a.status, a.descricao, a.vacinado, a.castrado, a.comportamento,
                    a.historico_saude, a.criado_em, a.deletado_em, a.atualizado_em,
                    r.nome AS raca_nome, fa.caminho_foto AS foto_principal,
                    u.nome AS adotante_nome, ad.foto_perfil AS adotante_foto
                FROM ANIMAL a
                LEFT JOIN RACA r ON r.raca_id = a.raca_id
                LEFT JOIN FOTO_ANIMAL fa ON fa.animal_id = a.animal_id AND fa.foto_principal = 1
                LEFT JOIN (
                    SELECT animal_id, MAX(solicitacao_id) AS solicitacao_id
                    FROM SOLICITACAO_ADOCAO
                    WHERE status_solicitacao = 'aprovada'
                    GROUP BY animal_id
                ) ultima ON ultima.animal_id = a.animal_id
                LEFT JOIN SOLICITACAO_ADOCAO s ON s.solicitacao_id = ultima.solicitacao_id
                LEFT JOIN ADOTANTE ad ON ad.adotante_id = s.adotante_id
                LEFT JOIN USUARIO u ON u.usuario_id = ad.usuario_id
                WHERE a.protetor_id = :protetor_id
                    AND a.status = 'adotado' AND a.deletado_em IS NULL
                ORDER BY a.atualizado_em DESC, a.animal_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function (array $row): array {
            $adotante = [
                'nome' => $row['adotante_nome'] ?? null,
                'foto_perfil' => $row['adotante_foto'] ?? null,
            ];
            return ['animal' => $this->mapAnimal($row), 'adotante' => $adotante];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function buscarPorProtetor(int $protetorId): array
    {
        $sql = "SELECT
            a.animal_id,
            a.protetor_id,
            a.raca_id,
            a.nome,
            a.dt_nasc,
            a.sexo,
            a.porte,
            a.status,
            a.descricao,
            a.vacinado,
            a.castrado,
            a.comportamento,
            a.historico_saude,
            a.criado_em,
            a.deletado_em,
            a.atualizado_em,
            rc.nome AS raca_nome
        FROM ANIMAL a
        LEFT JOIN RACA rc ON a.raca_id = rc.raca_id
        WHERE a.protetor_id = :protetor_id
        ORDER BY a.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapAnimal($row), $rows);
    }

    public function cadastrarAnimal(Animal $animal): int
    {
        return $this->transacaoStatus(function () use ($animal): int {
            if ($animal->getStatus() === 'em_analise') {
                throw new \DomainException('O animal entra em análise por uma solicitação de adoção.');
            }
            $sql = "INSERT INTO ANIMAL (
                protetor_id,
                raca_id,
                nome,
                dt_nasc,
                sexo,
                porte,
                status,
                descricao,
                vacinado,
                castrado,
                comportamento,
                historico_saude,
                atualizado_em
            ) VALUES (
                :protetor_id,
                :raca_id,
                :nome,
                :dt_nasc,
                :sexo,
                :porte,
                :status,
                :descricao,
                :vacinado,
                :castrado,
                :comportamento,
                :historico_saude,
                CURRENT_TIMESTAMP
            )";

            $stmt = $this->db->prepare($sql);
            $this->bindAnimalValues($stmt, $animal);
            $stmt->execute();

            $id = (int) $this->db->lastInsertId();
            $this->salvarTracos($id, $animal);
            $this->registrarStatus($id, null, $animal->getStatus());
            $this->sincronizarDesativacao($id, $animal->getStatus());
            return $id;
        });
    }

    public function editarAnimal(Animal $animal): bool
    {
        return $this->transacaoStatus(function () use ($animal): bool {
            $atual = $this->bloquearAnimal($animal->getAnimalId());
            if ((int) $atual['protetor_id'] !== $animal->getProtetorId()) {
                throw new \DomainException('O responsável pelo animal não pode ser alterado.');
            }
            $this->validarTransicaoManual($animal->getAnimalId(), $atual['status'], $animal->getStatus());
            $sql = "UPDATE ANIMAL SET
                protetor_id = :protetor_id,
                raca_id = :raca_id,
                nome = :nome,
                dt_nasc = :dt_nasc,
                sexo = :sexo,
                porte = :porte,
                status = :status,
                descricao = :descricao,
                vacinado = :vacinado,
                castrado = :castrado,
                comportamento = :comportamento,
                historico_saude = :historico_saude,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE animal_id = :animal_id";

            $stmt = $this->db->prepare($sql);
            $this->bindAnimalValues($stmt, $animal);
            $stmt->bindValue(':animal_id', $animal->getAnimalId(), PDO::PARAM_INT);

            $ok = $stmt->execute();
            if ($animal->getTracoIds() !== null) $this->salvarTracos((int) $animal->getAnimalId(), $animal);
            $this->registrarStatus($animal->getAnimalId(), $atual['status'], $animal->getStatus());
            $this->sincronizarDesativacao($animal->getAnimalId(), $animal->getStatus());
            return $ok;
        });
    }

    public function alterarStatus(int $id, string $status): bool
    {
        return $this->transacaoStatus(function () use ($id, $status): bool {
            $atual = $this->bloquearAnimal($id);
            $this->validarTransicaoManual($id, $atual['status'], $status);
            $this->persistirStatus($id, $atual['status'], $status);
            return true;
        });
    }

    public function excluirLogico(int $id): bool
    {
        return $this->alterarStatus($id, 'desativado');
    }

    public function reativarAnimal(int $id): bool
    {
        return $this->transacaoStatus(function () use ($id): bool {
            $atual = $this->bloquearAnimal($id);
            if (!in_array($atual['status'], ['adotado', 'desativado'], true)) {
                throw new \DomainException('Somente animais adotados ou desativados podem ser reativados.');
            }
            $novo = $this->temSolicitacao($id, ['em_analise']) ? 'em_analise' : 'disponivel';
            $this->persistirStatus($id, $atual['status'], $novo);
            return true;
        });
    }

    private function salvarTracos(int $id, Animal $animal): void
    {
        $repo = new TracoRepository($this->db);
        $repo->salvar($id, $animal->getTracoIds() ?? $repo->idsDoComportamento($animal->getComportamento()));
    }

    private function transacaoStatus(callable $operacao)
    {
        $propria = !$this->db->inTransaction();
        if ($propria) $this->db->beginTransaction();
        try {
            $resultado = $operacao();
            if ($propria) $this->db->commit();
            return $resultado;
        } catch (\Throwable $e) {
            if ($propria && $this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function bloquearAnimal(int $id): array
    {
        $stmt = $this->db->prepare('SELECT status, protetor_id FROM ANIMAL WHERE animal_id = ? FOR UPDATE');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: throw new \DomainException('Animal não encontrado.');
    }

    private function temSolicitacao(int $id, array $estados): bool
    {
        $marcadores = implode(',', array_fill(0, count($estados), '?'));
        $stmt = $this->db->prepare("SELECT solicitacao_id FROM SOLICITACAO_ADOCAO WHERE animal_id = ? AND status_solicitacao IN ($marcadores) LIMIT 1 FOR UPDATE");
        $stmt->execute([$id, ...$estados]);
        return (bool) $stmt->fetchColumn();
    }

    private function validarTransicaoManual(int $id, string $anterior, string $novo): void
    {
        if (!in_array($novo, ['disponivel', 'em_analise', 'adotado', 'desativado'], true)) {
            throw new \DomainException('Selecione um status válido.');
        }
        if ($anterior === $novo) return;
        if (in_array($anterior, ['adotado', 'desativado'], true) && $novo !== 'desativado') {
            throw new \DomainException('Use Reativar animal na listagem para disponibilizá-lo novamente.');
        }
        if ($novo === 'em_analise') {
            throw new \DomainException('Coloque uma solicitação de adoção em análise para alterar este estado.');
        }
        if ($novo === 'adotado' && $this->temSolicitacao($id, ['pendente', 'em_analise', 'aprovada'])) {
            throw new \DomainException('Confirme a adoção pela solicitação do adotante escolhido. Os demais pedidos serão cancelados automaticamente.');
        }
        if ($novo === 'disponivel' && $this->temSolicitacao($id, ['em_analise'])) {
            throw new \DomainException('Finalize as solicitações em análise antes de disponibilizar o animal.');
        }
    }

    private function persistirStatus(int $id, string $anterior, string $novo): void
    {
        $stmt = $this->db->prepare('UPDATE ANIMAL SET status = ?, atualizado_em = CURRENT_TIMESTAMP WHERE animal_id = ?');
        $stmt->execute([$novo, $id]);
        $this->sincronizarDesativacao($id, $novo);
        $this->registrarStatus($id, $anterior, $novo);
    }

    private function sincronizarDesativacao(int $id, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE ANIMAL SET deletado_em = IF(? = 'desativado', COALESCE(deletado_em, CURRENT_TIMESTAMP), NULL) WHERE animal_id = ?");
        $stmt->execute([$status, $id]);
    }

    private function registrarStatus(int $id, ?string $anterior, string $novo): void
    {
        if ($anterior === $novo) return;
        $stmt = $this->db->prepare('INSERT INTO HISTORICO_STATUS_ANIMAL (animal_id, status_antigo, status_novo) VALUES (?, ?, ?)');
        $stmt->execute([$id, $anterior, $novo]);
    }

    /**
     * Substitui a foto principal do animal (modelo de foto única).
     */
    public function salvarFotoPrincipal(int $animalId, string $caminhoFoto): void
    {
        $stmtDelete = $this->db->prepare("DELETE FROM FOTO_ANIMAL WHERE animal_id = :animal_id AND foto_principal = 1");
        $stmtDelete->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmtDelete->execute();

        $stmtInsert = $this->db->prepare(
            "INSERT INTO FOTO_ANIMAL (animal_id, caminho_foto, foto_principal) VALUES (:animal_id, :caminho_foto, 1)"
        );
        $stmtInsert->bindValue(':animal_id', $animalId, PDO::PARAM_INT);
        $stmtInsert->bindValue(':caminho_foto', $caminhoFoto, PDO::PARAM_STR);
        $stmtInsert->execute();
    }

    public function listarFotos(int $animalId): array
    {
        $st = $this->db->prepare('SELECT foto_id,caminho_foto,foto_principal FROM FOTO_ANIMAL WHERE animal_id=? ORDER BY foto_principal DESC,foto_id');
        $st->execute([$animalId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function definirFotoPrincipal(int $animalId, int $fotoId): void
    {
        $st = $this->db->prepare('SELECT foto_id FROM FOTO_ANIMAL WHERE animal_id=? AND foto_id=?');
        $st->execute([$animalId, $fotoId]);
        if (!$st->fetchColumn()) throw new \InvalidArgumentException('A foto principal não pertence a este animal.');
        $st = $this->db->prepare('UPDATE FOTO_ANIMAL SET foto_principal = CASE WHEN foto_id=? THEN 1 ELSE 0 END WHERE animal_id=?');
        $st->execute([$fotoId, $animalId]);
    }

    public function adicionarFoto(int $animalId, string $caminho, bool $principal): void
    {
        if ($principal) {
            $st = $this->db->prepare('UPDATE FOTO_ANIMAL SET foto_principal=0 WHERE animal_id=?');
            $st->execute([$animalId]);
        }
        $st = $this->db->prepare('INSERT INTO FOTO_ANIMAL(animal_id,caminho_foto,foto_principal) VALUES(?,?,?)');
        $st->execute([$animalId,$caminho,$principal ? 1 : 0]);
    }

    private function bindAnimalValues(PDOStatement $stmt, Animal $animal): void
    {
        if ($animal->getTracoIds() !== null) {
            $tracos = new TracoRepository($this->db);
            $ids = $tracos->validarSelecao($animal->getTracoIds());
            $animal->setComportamento($tracos->comportamentoResumo($ids));
        }
        $stmt->bindValue(':protetor_id', $animal->getProtetorId(), PDO::PARAM_INT);
        $stmt->bindValue(':raca_id', $animal->getRacaId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $animal->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':dt_nasc', $animal->getDtNasc(), $animal->getDtNasc() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':sexo', $animal->getSexo(), PDO::PARAM_STR);
        $stmt->bindValue(':porte', $animal->getPorte(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $animal->getStatus(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $animal->getDescricao(), $animal->getDescricao() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':vacinado', $animal->isVacinado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':castrado', $animal->isCastrado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':comportamento', $animal->getComportamento(), $animal->getComportamento() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':historico_saude', $animal->getHistoricoSaude(), $animal->getHistoricoSaude() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    private function mapAnimal(array $row): Animal
    {
        $animal = new Animal();
        $animal->setAnimalId((int) $row['animal_id']);
        $animal->setProtetorId((int) $row['protetor_id']);
        $animal->setRacaId((int) $row['raca_id']);
        $animal->setRacaNome($row['raca_nome'] ?? null);
        $animal->setFotoPrincipal($row['foto_principal'] ?? null);
        $animal->setNome($row['nome']);
        $animal->setDtNasc($row['dt_nasc']);
        $animal->setSexo($row['sexo']);
        $animal->setPorte($row['porte']);
        $animal->setStatus($row['status']);
        $animal->setDescricao($row['descricao']);
        $animal->setVacinado((bool) $row['vacinado']);
        $animal->setCastrado((bool) $row['castrado']);
        $animal->setComportamento($row['comportamento']);
        $animal->setHistoricoSaude($row['historico_saude']);
        $animal->setCriadoEm($row['criado_em']);
        $animal->setDeletadoEm($row['deletado_em']);
        $animal->setAtualizadoEm($row['atualizado_em']);

        return $animal;
    }
}
