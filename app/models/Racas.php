<?php

namespace app\models;

class Racas
{
    private ?int $id = null;
    private string $nome = '';
    private int $especieId = 0;
    private ?Especies $especies = null;
    private bool $ativo = true;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }
    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getEspecieId(): int
    {
        return $this->especieId;
    }
    public function setEspecieId(int $especieId): void
    {
        $this->especieId = $especieId;
    }

    public function getEspecie(): ?Especies
    {
        return $this->especies;
    }
    public function setEspecie(?Especies $especies): void
    {
        $this->especies = $especies;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }
    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }
}
