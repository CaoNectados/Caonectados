<?php

namespace app\repositories;

use app\models\Usuario;
use PDO;

class UsuarioRepository
{
    public function atualizarOnboarding(Usuario $usuario, PDO $pdo): bool
    {
        $sql = "UPDATE USUARIOS 
                SET nome = :nome, 
                    regiao_id = :regiao_id, 
                    tipo_atual = :tipo_atual 
                WHERE usuario_id = :usuario_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':regiao_id', $usuario->getRegiaoId());
        $stmt->bindValue(':tipo_atual', $usuario->getTipoAtual());
        $stmt->bindValue(':usuario_id', $usuario->getUsuarioId());

        return $stmt->execute();
    }

      public function salvarNovoUsuario(Usuario $usuario, PDO $pdo): int
    {
        $sql = "INSERT INTO USUARIOS (email, senha) VALUES (:email, :senha)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':senha', $usuario->getSenha());
        $stmt->execute();
        
        return (int)$pdo->lastInsertId();
    }

    public function buscarPorEmail(string $email, PDO $pdo): ?Usuario
    {
        $sql = "SELECT * FROM USUARIOS WHERE email = :email AND deletado_em IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $usuario = new Usuario();
        $usuario->setUsuarioId($dados['usuario_id']);
        $usuario->setEmail($dados['email']);
        $usuario->setSenha($dados['senha']);
        $usuario->setTipoAtual($dados['tipo_atual']);
        $usuario->setStatusConta($dados['status_conta']);
        
        return $usuario;
    }
}