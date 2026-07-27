<?php

namespace app\repositories;

use app\models\Usuario;
use PDO;

class UsuarioRepository
{
    public function atualizarOnboarding(Usuario $usuario, PDO $pdo): bool
    {
        $sql = "UPDATE USUARIO 
                SET nome = :nome, 
                    regiao_id = :regiao_id, 
                    tipo_perfil = :tipo_perfil 
                WHERE usuario_id = :usuario_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':regiao_id', $usuario->getRegiaoId());
        $stmt->bindValue(':tipo_perfil', $usuario->getTipoAtual());
        $stmt->bindValue(':usuario_id', $usuario->getUsuarioId());

        return $stmt->execute();
    }

      public function salvarNovoUsuario(Usuario $usuario, PDO $pdo): int
    {
        $sql = "INSERT INTO USUARIO (email, senha) VALUES (:email, :senha)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':senha', $usuario->getSenha());
        $stmt->execute();
        
        return (int)$pdo->lastInsertId();
    }

    public function buscarPorEmail(string $email, PDO $pdo): ?Usuario
    {
        $sql = "SELECT * FROM USUARIO WHERE email = :email AND deletado_em IS NULL";
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
        $usuario->setTipoAtual($dados['tipo_perfil']);
        $usuario->setStatusConta($dados['status_conta']);
        
        return $usuario;
    }


    // Busca todos os usuários ativos, bloqueados ou pendentes (ignora os deletados)
    public function buscarTodos(\PDO $pdo): array
    {
        $sql = "SELECT * FROM USUARIO WHERE deletado_em IS NULL ORDER BY criado_em DESC";
        $stmt = $pdo->query($sql);
        
        $usuarios = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $usuario = new \app\models\Usuario();
            $usuario->setUsuarioId($row['usuario_id']);
            $usuario->setNome($row['nome']);
            $usuario->setEmail($row['email']);
            $usuario->setStatusConta($row['status_conta']);
            $usuario->setTipoAtual($row['tipo_perfil']);
            $usuarios[] = $usuario;
        }
        return $usuarios;
    }

    // Soft Delete: Marca o usuário como inativo e registra a data da exclusão
    public function inativar(int $usuarioId, \PDO $pdo): void
    {
        $sql = "UPDATE USUARIO SET status_conta = 'inativo', deletado_em = CURRENT_TIMESTAMP WHERE usuario_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $usuarioId]);
    }

    // Atualização Geral pelo Admin
    public function atualizar(\app\models\Usuario $usuario, \PDO $pdo): void
    {
        $sql = "UPDATE USUARIO SET nome = :nome, email = :email, status_conta = :status, tipo_perfil = :tipo WHERE usuario_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome'   => $usuario->getNome(),
            'email'  => $usuario->getEmail(),
            'status' => $usuario->getStatusConta(),
            'tipo'   => $usuario->getTipoAtual(),
            'id'     => $usuario->getUsuarioId()
        ]);
    }
}