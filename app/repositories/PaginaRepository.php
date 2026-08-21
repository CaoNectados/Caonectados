<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Pagina;
use PDO;

class PaginaRepository extends BaseRepository
{
    // Usado por: PerfilController, PerfilService e OnBoardingService (também usado em views/perfil/perfil.php)
    public function buscarPorProtetorId(int $protetorId): ?array
    {
        $sql = "SELECT * FROM PAGINA WHERE protetor_id = :protetor_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados ?: null;
    }

    // Usado por: OnBoardingService::processarOng() e PerfilService::atualizarPerfil()
    public function salvar(Pagina $pagina): int
    {
        $sql = "INSERT INTO PAGINA (protetor_id, descricao, foto_fundo, foto_perfil, chave_pix)
                VALUES (:protetor_id, :descricao, :foto_fundo, :foto_perfil, :chave_pix)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':protetor_id', $pagina->getProtetorId(), PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $pagina->getDescricao(), $pagina->getDescricao() ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':foto_fundo', $pagina->getFotoFundo(), $pagina->getFotoFundo() ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':foto_perfil', $pagina->getFotoPerfil(), $pagina->getFotoPerfil() ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':chave_pix', $pagina->getChavePix(), $pagina->getChavePix() ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    // Usado por: PerfilService::atualizarPerfil() e OnBoardingService (fluxo de reenvio de protetor)
    public function atualizarPagina(int $protetorId, ?string $descricao, ?string $chavePix, ?string $fotoPerfil, ?string $fotoFundo = null): bool
    {
        $sql = "UPDATE PAGINA
                SET descricao = :descricao,
                    chave_pix = :chave_pix,
                    foto_perfil = COALESCE(:foto_perfil, foto_perfil),
                    foto_fundo = COALESCE(:foto_fundo, foto_fundo)
                WHERE protetor_id = :protetor_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':descricao', $descricao, $descricao ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':chave_pix', $chavePix, $chavePix ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':foto_perfil', $fotoPerfil, $fotoPerfil ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':foto_fundo', $fotoFundo, $fotoFundo ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':protetor_id', $protetorId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}