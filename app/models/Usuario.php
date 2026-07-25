<?php

namespace app\models;

use app\database\ConnectionFactory;
use PDO;
use PDOException;

class Usuario
{
    private ?int $usuario_id = null;
    private ?int $regiao_id = null;
    private ?string $telefone = null;
    private ?string $senha = null;
    private ?string $tipo_atual = null;
    private ?string $tipo_perfil = null;
    private ?string $status_conta = null;
    private ?string $email = null;
    private ?string $nome = null;
    private ?string $num_morada = null;
    private ?string $obs_casa = null;
    private ?string $dt_nasc = null;
    private ?string $cpf = null;
    private ?string $criado_em = null;
    private ?string $deletado_em = null;

    // ==========================================
    // GETTERS E SETTERS
    // ==========================================
    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function setUsuarioId(?int $usuario_id): void { $this->usuario_id = $usuario_id; }

    public function getRegiaoId(): ?int { return $this->regiao_id; }
    public function setRegiaoId(?int $regiao_id): void { $this->regiao_id = $regiao_id; }

    public function getTelefone(): ?string { return $this->telefone; }
    public function setTelefone(?string $telefone): void { $this->telefone = $telefone; }

    public function getSenha(): ?string { return $this->senha; }
    public function setSenha(?string $senha): void { $this->senha = $senha; }

    public function getTipoAtual(): ?string { return $this->tipo_perfil ?? $this->tipo_atual; }
    public function setTipoAtual(?string $tipo_atual): void
    {
        $this->tipo_atual = $tipo_atual;
    }

    public function getTipoPerfil(): ?string { return $this->tipo_perfil ?? $this->tipo_atual; }
    public function setTipoPerfil(?string $tipo_perfil): void
    {
        $this->tipo_perfil = $tipo_perfil;
    }

    public function getStatusConta(): ?string { return $this->status_conta; }
    public function setStatusConta(?string $status_conta): void { $this->status_conta = $status_conta; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getNome(): ?string { return $this->nome; }
    public function setNome(?string $nome): void { $this->nome = $nome; }

    public function getNumMorada(): ?string { return $this->num_morada; }
    public function setNumMorada(?string $num_morada): void { $this->num_morada = $num_morada; }

    public function getObsCasa(): ?string { return $this->obs_casa; }
    public function setObsCasa(?string $obs_casa): void { $this->obs_casa = $obs_casa; }

    public function getDtNasc(): ?string { return $this->dt_nasc; }
    public function setDtNasc(?string $dt_nasc): void { $this->dt_nasc = $dt_nasc; }

    public function getCpf(): ?string { return $this->cpf; }
    public function setCpf(?string $cpf): void { $this->cpf = $cpf; }

    public function getCriadoEm(): ?string { return $this->criado_em; }
    public function setCriadoEm(?string $criado_em): void { $this->criado_em = $criado_em; }

    public function getDeletadoEm(): ?string { return $this->deletado_em; }
    public function setDeletadoEm(?string $deletado_em): void { $this->deletado_em = $deletado_em; }

    
    public function findByEmail(string $email): ?object
    {
        $conexao = ConnectionFactory::getConnection();

        $sql = 'SELECT * FROM USUARIO WHERE email = :email LIMIT 1';
        
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $usuario = (object) $dados;
        $usuario->tipo_atual = $dados['tipo_perfil'] ?? null;
        $usuario->tipo_perfil = $dados['tipo_perfil'] ?? null;

        return $usuario;
    }

    /**
     * Cadastro inicial: salva apenas e-mail e senha. 
     * Demais campos obrigatórios do banco recebem temporariamente valores nulos ou vazios 
     * para não quebrar o banco antes do onboarding.
     */
    public function create(array $dados): int|false
    {
        try {
            $conexao = ConnectionFactory::getConnection();

            // Ajustado para inserir apenas o essencial no primeiro momento
            $sql = 'INSERT INTO USUARIO (email, senha, nome, cpf, telefone, num_morada, tipo_perfil) '
                 . 'VALUES (:email, :senha, :nome, :cpf, :telefone, :num_morada, :tipo_perfil)';

            $stmt = $conexao->prepare($sql);

            $stmt->bindValue(':email', $dados['email']);
            $stmt->bindValue(':senha', $dados['senha']);
            // Preenche temporariamente campos obrigatórios do banco que virão no onboarding
            $stmt->bindValue(':nome', $dados['nome'] ?? 'Pendente');
            $stmt->bindValue(':cpf', $dados['cpf'] ?? '000.000.000-00');
            $stmt->bindValue(':telefone', $dados['telefone'] ?? '(00) 00000-0000');
            $stmt->bindValue(':num_morada', $dados['num_morada'] ?? 'S/N');
            $stmt->bindValue(':tipo_perfil', $dados['tipo_perfil'] ?? 'usuario');

            if ($stmt->execute()) {
                return (int) $conexao->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {
            if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Atualiza os dados do usuário durante o fluxo de Onboarding
     */
    public function updateOnboarding(int $usuarioId, array $dados): bool
    {
        try {
            $conexao = ConnectionFactory::getConnection();

            $sql = 'UPDATE USUARIO SET 
                        nome = :nome, 
                        telefone = :telefone, 
                        cpf = :cpf, 
                        num_morada = :num_morada, 
                        tipo_perfil = :tipo_perfil,
                        regiao_id = :regiao_id,
                        dt_nasc = :dt_nasc,
                        obs_casa = :obs_casa
                    WHERE usuario_id = :usuario_id';

            $stmt = $conexao->prepare($sql);

            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':telefone', $dados['telefone']);
            $stmt->bindValue(':cpf', $dados['cpf']);
            $stmt->bindValue(':num_morada', $dados['num_morada']);
            $stmt->bindValue(':tipo_perfil', $dados['tipo_perfil']);
            $stmt->bindValue(':regiao_id', $dados['regiao_id'] ?? null);
            $stmt->bindValue(':dt_nasc', $dados['dt_nasc'] ?? null);
            $stmt->bindValue(':obs_casa', $dados['obs_casa'] ?? null);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true) {
                throw $e;
            }
            return false;
        }
    }
}