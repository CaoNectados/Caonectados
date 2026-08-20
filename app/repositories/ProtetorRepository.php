<?php

namespace app\repositories;

use app\core\BaseRepository;
use PDO;

class ProtetorRepository extends BaseRepository
{
    public function listarSolicitacoes(string $status = 'pendentes', string $busca = ''): array
    {
        $sql = "SELECT 
                    p.protetor_id,
                    p.usuario_id,
                    p.nome_fantasia,
                    p.tipo_documento,
                    p.codigo_documento,
                    p.data_abertura_cnpj,
                    p.comprovante_documento,
                    p.validado,
                    p.data_validacao,
                    p.criado_em,
                    p.deletado_em,
                    u.nome AS usuario_nome,
                    u.email AS usuario_email,
                    u.telefone AS usuario_telefone,
                    r.nome_regiao,
                    COALESCE(pag.foto_perfil, '') AS foto_perfil,
                    COALESCE(pag.foto_fundo, '') AS foto_fundo,
                    pag.descricao AS pagina_descricao,
                    CASE 
                        WHEN p.validado = 1 THEN 'aprovado'
                        WHEN u.status_conta = 'rejeitado' THEN 'recusado'
                        ELSE 'pendente'
                    END AS status
                FROM PROTETOR p
                INNER JOIN USUARIO u ON p.usuario_id = u.usuario_id
                LEFT JOIN REGIAO r ON u.regiao_id = r.regiao_id
                LEFT JOIN PAGINA pag ON p.protetor_id = pag.protetor_id
                WHERE 1=1";

        if ($status === 'pendentes') {
            $sql .= " AND p.validado = 0 AND p.deletado_em IS NULL";
        } elseif ($status === 'aprovados') {
            $sql .= " AND p.validado = 1 AND p.deletado_em IS NULL";
        } elseif ($status === 'recusados') {
            $sql .= " AND p.deletado_em IS NOT NULL";
        }

        if (!empty($busca)) {
            // Mudamos para usar a mesma chave no SQL e no Bind para não dar conflito
            $sql .= " AND (
                p.nome_fantasia LIKE :busca 
                OR u.nome LIKE :busca 
                OR p.codigo_documento LIKE :busca 
                OR r.nome_regiao LIKE :busca
            )";
        }

        $sql .= " GROUP BY p.protetor_id ORDER BY p.criado_em DESC";

        $stmt = $this->db->prepare($sql);

        if (!empty($busca)) {
            $stmt->bindValue(':busca', "%{$busca}%", PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarDetalhesSolicitacao(int $protetorId): ?array
    {
        $sql = "SELECT 
                    p.protetor_id,
                    p.usuario_id,
                    p.nome_fantasia,
                    p.tipo_documento,
                    p.codigo_documento,
                    p.comprovante_documento,
                    p.criado_em,
                    p.validado,
                    u.nome AS usuario_nome,
                    u.email AS usuario_email,
                    u.telefone AS usuario_telefone,
                    r.nome_regiao,
                    COALESCE(pg.foto_perfil, '') AS foto_perfil,
                    COALESCE(pg.foto_fundo, '') AS foto_fundo,
                    pg.descricao AS pagina_descricao,
                    CASE 
                        WHEN p.validado = 1 THEN 'aprovado'
                        WHEN u.status_conta = 'rejeitado' THEN 'recusado'
                        ELSE 'pendente'
                    END AS status
                FROM PROTETOR p
                INNER JOIN USUARIO u ON p.usuario_id = u.usuario_id
                LEFT JOIN REGIAO r ON u.regiao_id = r.regiao_id
                LEFT JOIN PAGINA pg ON p.protetor_id = pg.protetor_id
                WHERE p.protetor_id = :protetor_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function aprovarSolicitacao(int $protetorId): bool
    {
        $sql = "UPDATE PROTETOR 
                SET validado = 1, 
                    data_validacao = NOW(),
                    deletado_em = NULL 
                WHERE protetor_id = :protetor_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function recusarSolicitacao(int $protetorId): bool
    {
        $sql = "UPDATE PROTETOR 
                SET validado = 0, 
                    deletado_em = NOW() 
                WHERE protetor_id = :protetor_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscarPorUsuarioId(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM PROTETOR WHERE usuario_id = :usuario_id ORDER BY protetor_id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function salvar(object $protetor): int
    {
        $sql = "INSERT INTO PROTETOR (usuario_id, codigo_documento, tipo_documento, nome_fantasia, data_abertura_cnpj, comprovante_documento, validado)
                VALUES (:usuario_id, :codigo_documento, :tipo_documento, :nome_fantasia, :data_abertura_cnpj, :comprovante_documento, 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $protetor->getUsuarioId(), PDO::PARAM_INT);
        $stmt->bindValue(':codigo_documento', $protetor->getCodigoDocumento(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_documento', $protetor->getTipoDocumento(), PDO::PARAM_STR);
        $stmt->bindValue(':nome_fantasia', $protetor->getNomeFantasia(), PDO::PARAM_STR);
        $stmt->bindValue(':data_abertura_cnpj', $protetor->getDataAberturaCnpj(), $protetor->getDataAberturaCnpj() ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':comprovante_documento', $protetor->getComprovanteDocumento(), PDO::PARAM_STR);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    public function atualizarReenvio(int $protetorId, string $nomeFantasia, string $codigoDocumento, ?string $dataAbertura, ?string $comprovante): bool
    {
        $sql = "UPDATE PROTETOR 
                SET nome_fantasia = :nome_fantasia,
                    codigo_documento = :codigo_documento,
                    data_abertura_cnpj = :data_abertura,
                    comprovante_documento = COALESCE(:comprovante, comprovante_documento),
                    validado = 0,
                    data_validacao = NULL,
                    deletado_em = NULL
                WHERE protetor_id = :protetor_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome_fantasia', $nomeFantasia, PDO::PARAM_STR);
        $stmt->bindValue(':codigo_documento', $codigoDocumento, PDO::PARAM_STR);
        $stmt->bindValue(':data_abertura', $dataAbertura, $dataAbertura ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':comprovante', $comprovante, $comprovante ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);

        return $stmt->execute();
    }
    public function buscarPorUsuarioIdCompleto(int $usuarioId): ?array
    {
        $sql = "SELECT 
                    p.*,
                    u.nome AS usuario_nome,
                    u.email AS usuario_email,
                    u.telefone,
                    u.dt_nasc,
                    u.logradouro,
                    u.numero,
                    u.regiao_id,
                    pag.descricao AS pagina_descricao,
                    pag.chave_pix,
                    pag.foto_perfil,
                    pag.foto_fundo
                FROM PROTETOR p
                INNER JOIN USUARIO u ON p.usuario_id = u.usuario_id
                LEFT JOIN PAGINA pag ON p.protetor_id = pag.protetor_id
                WHERE p.usuario_id = :usuario_id
                ORDER BY p.protetor_id DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}
