<?php

namespace app\models;

class Regiao
{
    private ?int $regiaoId = null;
    private string $nomeRegiao;

    public function __construct() {}

    public function getRegiaoId(): ?int
    {
        return $this->regiaoId;
    }

    public function setRegiaoId(?int $regiaoId): self
    {
        $this->regiaoId = $regiaoId;
        return $this;
    }

    public function getNomeRegiao(): string
    {
        return $this->nomeRegiao;
    }

    public function setNomeRegiao(string $nomeRegiao): self
    {
        $this->nomeRegiao = $nomeRegiao;
        return $this;
    }
}
