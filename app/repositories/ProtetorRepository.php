<?php

namespace app\repositories;

use app\models\Protetor;
use PDO;

class ProtetorRepository
{
    public function salvar(Protetor $protetor, PDO $pdo): int
    {
        $sql = "INSERT INTO PROTETOR (usuario_id, validado, codigo_documento, tipo_documento, nome_fantasia, comprovante_documento) 
                VALUES (:usuario_id, :validado, :codigo_documento, :tipo_documento, :nome_fantasia, :comprovante_documento)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $protetor->getUsuarioId(), PDO::PARAM_INT);
        $stmt->bindValue(':validado', $protetor->getValidado() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_documento', $protetor->getCodigoDocumento());
        $stmt->bindValue(':tipo_documento', $protetor->getTipoDocumento());
        $stmt->bindValue(':nome_fantasia', $protetor->getNomeFantasia());
        $stmt->bindValue(':comprovante_documento', $protetor->getComprovanteDocumento());

        $stmt->execute();
        
        return (int) $pdo->lastInsertId();
    }

    public function buscarPorUsuarioId(int $usuarioId, PDO $pdo): ?array
    {
        $sql = "SELECT p.*, pg.foto_perfil FROM PROTETOR p 
                LEFT JOIN PAGINA pg ON p.protetor_id = pg.protetor_id 
                WHERE p.usuario_id = :usuario_id AND p.deletado_em IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados ?: null;
    }

    public function atualizarProtetor(int $usuarioId, string $codigoDocumento, bool $alterouDocumento, PDO $pdo): bool
    {
        // Se alterou o CNPJ/CPF, o status da conta volta para 'pendente' e desmarca a validação
        if ($alterouDocumento) {
            $sql = "UPDATE PROTETOR p JOIN USUARIO u ON p.usuario_id = u.usuario_id 
                    SET p.codigo_documento = :codigo_documento, p.validado = 0, u.status_conta = 'pendente' 
                    WHERE p.usuario_id = :usuario_id";
        } else {
            $sql = "UPDATE PROTETOR SET codigo_documento = :codigo_documento WHERE usuario_id = :usuario_id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':codigo_documento', $codigoDocumento);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }

   

    public function salvarPagina(int $protetorId, ?string $descricao, ?string $fotoPerfil, ?string $chavePix, PDO $pdo): bool
    {
        $sql = "INSERT INTO PAGINA (protetor_id, descricao, foto_perfil, chave_pix) 
                VALUES (:protetor_id, :descricao, :foto_perfil, :chave_pix)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':foto_perfil', $fotoPerfil);
        $stmt->bindValue(':chave_pix', $chavePix);

        return $stmt->execute();
    }

    public function salvarRedeSocial(int $protetorId, string $link, string $tipo, PDO $pdo): bool
    {
        $sql = "INSERT INTO REDE (protetor_id, link_rede, tipo_rede) 
                VALUES (:protetor_id, :link_rede, :tipo_rede)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->bindValue(':link_rede', $link);
        $stmt->bindValue(':tipo_rede', strtolower($tipo));

        return $stmt->execute();
    }
   public function buscarPaginaPorProtetorId(int $protetorId, PDO $pdo): ?array
    {
        $sql = "SELECT * FROM PAGINA WHERE protetor_id = :protetor_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

   

   
}