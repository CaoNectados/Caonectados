<?php

namespace app\models;

#[\AllowDynamicProperties]
class Animal
{
    private ?int $animalId = null;
    private int $protetorId;
    private int $racaId;
    private string $nome;
    private ?string $dtNasc = null;
    private string $sexo;
    private string $porte;
    private string $status;
    private ?string $descricao = null;
    private bool $vacinado = false;
    private bool $castrado = false;
    private ?string $comportamento = null;
    private ?string $historicoSaude = null;
    private ?string $criadoEm = null;
    private ?string $deletadoEm = null;
    private ?string $atualizadoEm = null;
    private ?string $racaNome = null;


    public function __construct() {}

    public function getAnimalId(): ?int
    {
        return $this->animalId;
    }

    public function setAnimalId(?int $animalId): self
    {
        $this->animalId = $animalId;
        return $this;
    }

    public function getProtetorId(): int
    {
        return $this->protetorId;
    }

    public function setProtetorId(int $protetorId): self
    {
        $this->protetorId = $protetorId;
        return $this;
    }

    public function getRacaId(): int
    {
        return $this->racaId;
    }

    public function setRacaId(int $racaId): self
    {
        $this->racaId = $racaId;
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

    public function getDtNasc(): ?string
    {
        return $this->dtNasc;
    }

    public function setDtNasc(?string $dtNasc): self
    {
        $this->dtNasc = $dtNasc;
        return $this;
    }

    public function getSexo(): string
    {
        return $this->sexo;
    }

    public function setSexo(string $sexo): self
    {
        $this->sexo = $sexo;
        return $this;
    }

    public function getPorte(): string
    {
        return $this->porte;
    }

    public function setPorte(string $porte): self
    {
        $this->porte = $porte;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function isVacinado(): bool
    {
        return $this->vacinado;
    }

    public function setVacinado(bool $vacinado): self
    {
        $this->vacinado = $vacinado;
        return $this;
    }

    public function isCastrado(): bool
    {
        return $this->castrado;
    }

    public function setCastrado(bool $castrado): self
    {
        $this->castrado = $castrado;
        return $this;
    }

    public function getComportamento(): ?string
    {
        return $this->comportamento;
    }

    public function setComportamento(?string $comportamento): self
    {
        $this->comportamento = $comportamento;
        return $this;
    }

    public function getHistoricoSaude(): ?string
    {
        return $this->historicoSaude;
    }

    public function setHistoricoSaude(?string $historicoSaude): self
    {
        $this->historicoSaude = $historicoSaude;
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

    public function getDeletadoEm(): ?string
    {
        return $this->deletadoEm;
    }

    public function setDeletadoEm(?string $deletadoEm): self
    {
        $this->deletadoEm = $deletadoEm;
        return $this;
    }

    public function getAtualizadoEm(): ?string
    {
        return $this->atualizadoEm;
    }

    public function setAtualizadoEm(?string $atualizadoEm): self
    {
        $this->atualizadoEm = $atualizadoEm;
        return $this;
    }

    public function getRacaNome(): ?string
    {
        return $this->racaNome;
    }

    public function setRacaNome(?string $racaNome): void
    {
        $this->racaNome = $racaNome;
    }
}
