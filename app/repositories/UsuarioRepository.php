<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Usuario;
use PDO;

class UsuarioRepository extends BaseRepository
{
    public function atualizarOnboarding(Usuario $usuario): bool
    {
        $sql = "UPDATE USUARIO 
                SET nome = :nome, 
                    regiao_id = :regiao_id, 
                    tipo_perfil = :tipo_perfil 
                WHERE usuario_id = :usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':regiao_id', $usuario->getRegiaoId(), $usuario->getRegiaoId() ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':tipo_perfil', $usuario->getTipoAtual(), PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuario->getUsuarioId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function salvarNovoUsuario(Usuario $usuario): int
    {
        $sql = "INSERT INTO USUARIO (email, senha) VALUES (:email, :senha)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':senha', $usuario->getSenha(), PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $sql = "SELECT * FROM USUARIO WHERE email = :email AND deletado_em IS NULL LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados === false ? null : $this->mapUsuario($dados);
    }

    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM USUARIO WHERE deletado_em IS NULL ORDER BY criado_em DESC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapUsuario($row), $rows);
    }

    public function inativar(int $usuarioId): void
    {
        $sql = "UPDATE USUARIO SET status_conta = 'inativo', deletado_em = CURRENT_TIMESTAMP WHERE usuario_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function atualizar(Usuario $usuario): void
    {
        $sql = "UPDATE USUARIO 
                SET nome = :nome, 
                    email = :email, 
                    status_conta = :status, 
                    tipo_perfil = :tipo 
                WHERE usuario_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $usuario->getStatusConta(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo', $usuario->getTipoAtual(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $usuario->getUsuarioId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function buscarPorId(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM USUARIO WHERE usuario_id = :id AND deletado_em IS NULL LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados ?: null;
    }

    public function atualizarPerfil(array $dados): bool
    {
        $sql = "UPDATE USUARIO 
                SET nome = :nome, 
                    telefone = :telefone, 
                    dt_nasc = :dt_nasc, 
                    regiao_id = :regiao_id 
                WHERE usuario_id = :usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $dados['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $dados['telefone'], PDO::PARAM_STR);
        $stmt->bindValue(':dt_nasc', $dados['dt_nasc'], empty($dados['dt_nasc']) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':regiao_id', $dados['regiao_id'], !empty($dados['regiao_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':usuario_id', $dados['usuario_id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function atualizarDadosGerais(int $usuarioId, string $nome, string $telefone, string $email, ?string $senhaHash, ?int $regiaoId): bool
    {
        if ($senhaHash) {
            $sql = "UPDATE USUARIO 
                    SET nome = :nome, 
                        telefone = :telefone, 
                        email = :email, 
                        senha = :senha, 
                        regiao_id = :regiao_id 
                    WHERE usuario_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':senha', $senhaHash, PDO::PARAM_STR);
        } else {
            $sql = "UPDATE USUARIO 
                    SET nome = :nome, 
                        telefone = :telefone, 
                        email = :email, 
                        regiao_id = :regiao_id 
                    WHERE usuario_id = :id";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':regiao_id', $regiaoId, $regiaoId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function mapUsuario(array $row): Usuario
    {
        $usuario = new Usuario();
        $usuario->setUsuarioId((int) $row['usuario_id']);
        $usuario->setRegiaoId(isset($row['regiao_id']) ? (int) $row['regiao_id'] : null);
        $usuario->setNome($row['nome'] ?? null);
        $usuario->setEmail($row['email'] ?? null);
        $usuario->setSenha($row['senha'] ?? null);
        $usuario->setTelefone($row['telefone'] ?? null);
        $usuario->setTipoPerfil($row['tipo_perfil'] ?? null);
        $usuario->setStatusConta($row['status_conta'] ?? null);
        $usuario->setDtNasc($row['dt_nasc'] ?? null);
        $usuario->setCriadoEm($row['criado_em'] ?? null);

        return $usuario;
    }
    public function salvarCodigoVerificacao(int $usuarioId, string $codigo, string $expiraEm): void
    {
        $sql = "INSERT INTO CODIGO_VERIFICACAO (usuario_id, codigo, expira_em) VALUES (:usuario_id, :codigo, :expira_em)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindValue(':expira_em', $expiraEm, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Busca um código de verificação válido
     */
    public function buscarCodigoValido(int $usuarioId, string $codigo): ?array
    {
        $sql = "SELECT * FROM CODIGO_VERIFICACAO 
                WHERE usuario_id = :usuario_id 
                  AND codigo = :codigo 
                  AND usado = FALSE 
                  AND expira_em >= NOW() 
                ORDER BY codigo_id DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados ?: null;
    }

    /**
     * Marca um código como utilizado
     */
    public function marcarCodigoComoUsado(int $codigoId): void
    {
        $sql = "UPDATE CODIGO_VERIFICACAO SET usado = TRUE WHERE codigo_id = :codigo_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo_id', $codigoId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Atualiza a senha do usuário
     */
    public function atualizarSenha(int $usuarioId, string $novaSenhaHash): void
    {
        $sql = "UPDATE USUARIO SET senha = :senha WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':senha', $novaSenhaHash, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
    }
}