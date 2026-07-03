<?php

namespace app\models;

class Protetores
{
    private ?int $protetorId = null;
    private int $usuarioId;
    private bool $validado = false;
    private ?string $dataValidacao = null;
    private string $codigoDocumento;
    private string $tipoDocumento;
    private string $nomeFantasia;

    public function __construct()
    {
    }

    public function getProtetorId(): ?int
    {
        return $this->protetorId;
    }

    public function setProtetorId(?int $protetorId): self
    {
        $this->protetorId = $protetorId;
        return $this;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function setUsuarioId(int $usuarioId): self
    {
        $this->usuarioId = $usuarioId;
        return $this;
    }

    public function isValidado(): bool
    {
        return $this->validado;
    }

    public function setValidado(bool $validado): self
    {
        $this->validado = $validado;
        return $this;
    }

    public function getDataValidacao(): ?string
    {
        return $this->dataValidacao;
    }

    public function setDataValidacao(?string $dataValidacao): self
    {
        $this->dataValidacao = $dataValidacao;
        return $this;
    }

    public function getCodigoDocumento(): string
    {
        return $this->codigoDocumento;
    }

    public function setCodigoDocumento(string $codigoDocumento): self
    {
        $this->codigoDocumento = $codigoDocumento;
        return $this;
    }

    public function getTipoDocumento(): string
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(string $tipoDocumento): self
    {
        $this->tipoDocumento = $tipoDocumento;
        return $this;
    }

    public function getNomeFantasia(): string
    {
        return $this->nomeFantasia;
    }

    public function setNomeFantasia(string $nomeFantasia): self
    {
        $this->nomeFantasia = $nomeFantasia;
        return $this;
    }
}