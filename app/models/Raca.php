<?php

namespace app\models;

class Raca
{
    private ?int $id = null;
    private string $nome = '';
    private int $especieId = 0;
    private ?Especie $especie = null;
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

    public function getEspecie(): ?Especie
    {
        return $this->especie;
    }
    public function setEspecie(?Especie $especie): void
    {
        $this->especie = $especie;
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
