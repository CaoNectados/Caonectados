<?php

namespace app\models;

use PDO;

class Raca
{
    private int $raca_id;
    private int $especie_id;
    private string $nome;


    public function getRacaId(): int
    {
        return $this->raca_id;
    }

    public function setRacaId(int $id): void
    {
        $this->raca_id = $id;
    }

    public function getEspecieId(): int
    {
        return $this->especie_id;
    }

    public function setEspecieId(int $especieId): void
    {
        $this->especie_id = $especieId;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }
}