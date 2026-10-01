<?php
namespace app\services;
use app\database\ConnectionFactory;
use DomainException;
use Throwable;

/** RF14: decisão manual e reversível, independente do bloqueio global. */
class ClassificacaoProtetorService
{
    public function classificar(int $usuarioId, int $adminId, bool $inadimplente, string $motivo): void
    {
        if (trim($motivo) === '' || mb_strlen($motivo) > 2000) throw new DomainException('Informe motivo de até 2000 caracteres.');
        $db=ConnectionFactory::getConnection();
        if (!$db->query("SHOW COLUMNS FROM PROTETOR LIKE 'inadimplente'")->fetch()) throw new DomainException('Classificação disponível após aplicação da migração pós-banca.');
        $db->beginTransaction();
        try {
            $stmt=$db->prepare('SELECT * FROM USUARIO WHERE usuario_id=? FOR UPDATE');
            $stmt->execute([$adminId]);
            $admin=$stmt->fetch();
            if (!$admin || $admin['tipo_atual'] !== 'administrador' || $admin['status_conta'] !== 'ativo') throw new DomainException('Administrador obrigatório.');
            $stmt=$db->prepare('SELECT * FROM PROTETOR WHERE usuario_id=? ORDER BY protetor_id DESC LIMIT 1 FOR UPDATE');
            $stmt->execute([$usuarioId]);
            $p=$stmt->fetch();
            if (!$p || !$p['validado'] || $p['deletado_em']) throw new DomainException('Protetor aprovado não encontrado.');
            $stmt=$db->prepare('UPDATE PROTETOR SET inadimplente=?,motivo_inadimplencia=? WHERE protetor_id=?');
            $stmt->execute([(int)$inadimplente,trim($motivo),$p['protetor_id']]);
            $stmt=$db->prepare('INSERT INTO HISTORICO_CLASSIFICACAO_PROTETOR (protetor_id,admin_id,inadimplente,motivo) VALUES (?,?,?,?)');
            $stmt->execute([$p['protetor_id'],$adminId,(int)$inadimplente,trim($motivo)]);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
