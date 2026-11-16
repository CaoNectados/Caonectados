<?php

namespace app\services;

use app\database\ConnectionFactory;
use DomainException;
use PDO;
use Throwable;

/** RF16/RN23: classificação administrativa auditável, independente do bloqueio. */
class ClassificacaoProtetorService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? ConnectionFactory::getConnection();
    }

    public function consultarParaAdmin(int $usuarioId, int $adminId, int $pagina = 1): array
    {
        $this->exigirAdministrador($adminId);
        if ($usuarioId < 1 || $pagina < 1) throw new DomainException('Consulta inválida.');
        $stmt = $this->db->prepare('SELECT p.* FROM PROTETOR p
            JOIN USUARIO u ON u.usuario_id = p.usuario_id
            WHERE p.usuario_id = ? AND u.deletado_em IS NULL
            ORDER BY p.protetor_id DESC LIMIT 1');
        $stmt->execute([$usuarioId]);
        $protetor = $stmt->fetch();
        if (!$protetor) throw new DomainException('Perfil não encontrado.');

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM HISTORICO_CLASSIFICACAO_PROTETOR WHERE protetor_id = ?');
        $stmt->execute([$protetor['protetor_id']]);
        $total = (int) $stmt->fetchColumn();
        $paginas = max(1, (int) ceil($total / 8));
        $pagina = min($pagina, $paginas);
        $stmt = $this->db->prepare('SELECT h.historico_id, h.inadimplente, h.motivo, h.criado_em,
                COALESCE(u.nome, \'Administrador\') AS administrador
            FROM HISTORICO_CLASSIFICACAO_PROTETOR h
            LEFT JOIN USUARIO u ON u.usuario_id = h.admin_id
            WHERE h.protetor_id = :id
            ORDER BY h.criado_em DESC, h.historico_id DESC LIMIT 8 OFFSET :offset');
        $stmt->bindValue(':id', (int) $protetor['protetor_id'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($pagina - 1) * 8, PDO::PARAM_INT);
        $stmt->execute();
        return ['protetor' => $protetor, 'historico' => $stmt->fetchAll(),
            'totalHistorico' => $total, 'paginaHistorico' => $pagina, 'paginasHistorico' => $paginas];
    }

    /** O controller fornece exclusivamente o ID da sessão, nunca um ID da requisição. */
    public function resumoDoProprioPerfil(int $usuarioId): ?array
    {
        $stmt = $this->db->prepare('SELECT p.inadimplente, p.motivo_inadimplencia,
            (SELECT h.criado_em FROM HISTORICO_CLASSIFICACAO_PROTETOR h
             WHERE h.protetor_id = p.protetor_id ORDER BY h.criado_em DESC, h.historico_id DESC LIMIT 1) AS atualizado_em
            FROM PROTETOR p WHERE p.usuario_id = ? AND p.validado = 1 AND p.deletado_em IS NULL
            ORDER BY p.protetor_id DESC LIMIT 1');
        $stmt->execute([$usuarioId]);
        return $stmt->fetch() ?: null;
    }

    public function classificar(int $usuarioId, int $adminId, bool $inadimplente, string $motivo): void
    {
        $motivo = trim($motivo);
        if ($motivo === '' || mb_strlen($motivo) > 2000) {
            throw new DomainException('Informe uma justificativa de até 2.000 caracteres.');
        }
        if ($usuarioId < 1) throw new DomainException('Perfil inválido.');
        $this->db->beginTransaction();
        try {
            $this->exigirAdministrador($adminId, true);
            $stmt = $this->db->prepare('SELECT p.* FROM PROTETOR p
                JOIN USUARIO u ON u.usuario_id = p.usuario_id
                WHERE p.usuario_id = ? AND u.deletado_em IS NULL
                ORDER BY p.protetor_id DESC LIMIT 1 FOR UPDATE');
            $stmt->execute([$usuarioId]);
            $protetor = $stmt->fetch();
            if (!$protetor || !$protetor['validado'] || $protetor['deletado_em']) {
                throw new DomainException('Somente perfis aprovados podem receber uma classificação.');
            }
            $stmt = $this->db->prepare('UPDATE PROTETOR SET inadimplente = ?, motivo_inadimplencia = ? WHERE protetor_id = ?');
            $stmt->execute([(int) $inadimplente, $motivo, $protetor['protetor_id']]);
            $stmt = $this->db->prepare('INSERT INTO HISTORICO_CLASSIFICACAO_PROTETOR
                (protetor_id, admin_id, inadimplente, motivo) VALUES (?, ?, ?, ?)');
            $stmt->execute([$protetor['protetor_id'], $adminId, (int) $inadimplente, $motivo]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function exigirAdministrador(int $adminId, bool $bloquear = false): void
    {
        $sql = "SELECT usuario_id FROM USUARIO WHERE usuario_id = ? AND tipo_atual = 'administrador'
            AND status_conta = 'ativo' AND deletado_em IS NULL";
        if ($bloquear) $sql .= ' FOR UPDATE';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
        if (!$stmt->fetch()) throw new DomainException('Ação exclusiva de administrador ativo.');
    }
}
