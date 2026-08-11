<?php

namespace app\models;

class Usuario
{
    private ?int $usuario_id = null;
    private ?int $regiao_id = null;
    private ?string $logradouro = null;
    private ?string $numero = null;
    private ?string $telefone = null;
    private ?string $senha = null;
    private ?string $tipo_atual = null;
    private ?string $perfis_ativos = null;
    private ?string $status_conta = null;
    private ?string $criado_em = null;
    private ?string $email = null;
    private ?string $nome = null;
    private ?string $dt_nasc = null;
    private ?string $deletado_em = null;

    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function setUsuarioId(?int $usuario_id): void { $this->usuario_id = $usuario_id; }

    public function getRegiaoId(): ?int { return $this->regiao_id; }
    public function setRegiaoId(?int $regiao_id): void { $this->regiao_id = $regiao_id; }

    public function getLogradouro(): ?string { return $this->logradouro; }
    public function setLogradouro(?string $logradouro): void { $this->logradouro = $logradouro; }

    public function getNumero(): ?string { return $this->numero; }
    public function setNumero(?string $numero): void { $this->numero = $numero; }

    public function getTelefone(): ?string { return $this->telefone; }
    public function setTelefone(?string $telefone): void { $this->telefone = $telefone; }

    public function getSenha(): ?string { return $this->senha; }
    public function setSenha(?string $senha): void { $this->senha = $senha; }

    public function getTipoAtual(): ?string { return $this->tipo_atual; }
    public function setTipoAtual(?string $tipo_atual): void { $this->tipo_atual = $tipo_atual; }

    public function getPerfisAtivos(): ?string { return $this->perfis_ativos; }
    public function setPerfisAtivos(?string $perfis_ativos): void { $this->perfis_ativos = $perfis_ativos; }

    public function getStatusConta(): ?string { return $this->status_conta; }
    public function setStatusConta(?string $status_conta): void { $this->status_conta = $status_conta; }

    public function getCriadoEm(): ?string { return $this->criado_em; }
    public function setCriadoEm(?string $criado_em): void { $this->criado_em = $criado_em; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getNome(): ?string { return $this->nome; }
    public function setNome(?string $nome): void { $this->nome = $nome; }

    public function getDtNasc(): ?string { return $this->dt_nasc; }
    public function setDtNasc(?string $dt_nasc): void { $this->dt_nasc = $dt_nasc; }

    public function getDeletadoEm(): ?string { return $this->deletado_em; }
    public function setDeletadoEm(?string $deletado_em): void { $this->deletado_em = $deletado_em; }
}