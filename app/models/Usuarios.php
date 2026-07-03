<?php

namespace app\models;

class Usuarios
{
    private ?int $usuarioId = null;
    private ?int $regiaoId = null;
    private string $telefone;
    private string $senha;
    private string $tipoPerfil;
    private string $statusConta;
    private ?string $criadoEm = null;
    private string $email;
    private string $nome;
    private string $numMorada;
    private ?string $obsCasa = null;
    private ?string $dtNasc = null;
    private string $cpf;
    private ?string $deletadoEm = null;

    public function __construct()
    {
    }

    public function getUsuarioId(): ?int
    {
        return $this->usuarioId;
    }

    public function setUsuarioId(?int $usuarioId): self
    {
        $this->usuarioId = $usuarioId;
        return $this;
    }

    public function getRegiaoId(): ?int
    {
        return $this->regiaoId;
    }

    public function setRegiaoId(?int $regiaoId): self
    {
        $this->regiaoId = $regiaoId;
        return $this;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): self
    {
        $this->telefone = $telefone;
        return $this;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): self
    {
        $this->senha = $senha;
        return $this;
    }

    public function getTipoPerfil(): string
    {
        return $this->tipoPerfil;
    }

    public function setTipoPerfil(string $tipoPerfil): self
    {
        $this->tipoPerfil = $tipoPerfil;
        return $this;
    }

    public function getStatusConta(): string
    {
        return $this->statusConta;
    }

    public function setStatusConta(string $statusConta): self
    {
        $this->statusConta = $statusConta;
        return $this;
    }

    public function getCriadoEm(): ?string
    {
        return $this->criadoEm;
    }

    public function setCriadoEm(?string $criadoEm): self
    {
        $this->criadoEm = $criadoEm;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    public function getNumMorada(): string
    {
        return $this->numMorada;
    }

    public function setNumMorada(string $numMorada): self
    {
        $this->numMorada = $numMorada;
        return $this;
    }

    public function getObsCasa(): ?string
    {
        return $this->obsCasa;
    }

    public function setObsCasa(?string $obsCasa): self
    {
        $this->obsCasa = $obsCasa;
        return $this;
    }

    public function getDtNasc(): ?string
    {
        return $this->dtNasc;
    }

    public function setDtNasc(?string $dtNasc): self
    {
        $this->dtNasc = $dtNasc;
        return $this;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function getDeletadoEm(): ?string
    {
        return $this->deletadoEm;
    }

    public function setDeletadoEm(?string $deletadoEm): self
    {
        $this->deletadoEm = $deletadoEm;
        return $this;
    }
}