<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Protetor;
use PDO;

class ProtetorRepository extends BaseRepository
{
    public function salvar(Protetor $protetor): int
    {
        $sql = "INSERT INTO PROTETOR (usuario_id, validado, codigo_documento, tipo_documento, nome_fantasia, comprovante_documento) 
                VALUES (:usuario_id, :validado, :codigo_documento, :tipo_documento, :nome_fantasia, :comprovante_documento)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $protetor->getUsuarioId(), PDO::PARAM_INT);
        $stmt->bindValue(':validado', $protetor->getValidado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_documento', $protetor->getCodigoDocumento(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_documento', $protetor->getTipoDocumento(), PDO::PARAM_STR);
        $stmt->bindValue(':nome_fantasia', $protetor->getNomeFantasia(), PDO::PARAM_STR);
        $stmt->bindValue(':comprovante_documento', $protetor->getComprovanteDocumento(), PDO::PARAM_STR);

        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorUsuarioId(int $usuarioId): ?array
    {
        $sql = "SELECT p.*, pg.foto_perfil FROM PROTETOR p 
                LEFT JOIN PAGINA pg ON p.protetor_id = pg.protetor_id 
                WHERE p.usuario_id = :usuario_id AND p.deletado_em IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados ?: null;
    }

    public function atualizarProtetor(int $usuarioId, string $codigoDocumento, bool $alterouDocumento): bool
    {
        if ($alterouDocumento) {
            $sql = "UPDATE PROTETOR p 
                    INNER JOIN USUARIO u ON p.usuario_id = u.usuario_id 
                    SET p.codigo_documento = :codigo_documento, 
                        p.validado = 0, 
                        u.status_conta = 'pendente' 
                    WHERE p.usuario_id = :usuario_id";
        } else {
            $sql = "UPDATE PROTETOR SET codigo_documento = :codigo_documento WHERE usuario_id = :usuario_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo_documento', $codigoDocumento, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function salvarPagina(int $protetorId, ?string $descricao, ?string $fotoPerfil, ?string $chavePix): bool
    {
        $sql = "INSERT INTO PAGINA (protetor_id, descricao, foto_perfil, chave_pix) 
                VALUES (:protetor_id, :descricao, :foto_perfil, :chave_pix)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $descricao, $descricao === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':foto_perfil', $fotoPerfil, $fotoPerfil === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':chave_pix', $chavePix, $chavePix === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function salvarRedeSocial(int $protetorId, string $link, string $tipo): bool
    {
        $sql = "INSERT INTO REDE (protetor_id, link_rede, tipo_rede) 
                VALUES (:protetor_id, :link_rede, :tipo_rede)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->bindValue(':link_rede', $link, PDO::PARAM_STR);
        $stmt->bindValue(':tipo_rede', strtolower($tipo), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function buscarPaginaPorProtetorId(int $protetorId): ?array
    {
        $sql = "SELECT * FROM PAGINA WHERE protetor_id = :protetor_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}