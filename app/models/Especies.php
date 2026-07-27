<?php

namespace app\models;

class Especies
{
    private ?int $id = null;
    private string $nome = '';
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

    public function isAtivo(): bool
    {
        return $this->ativo;
    }
    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }
}
