<?php

namespace app\models;

use PDO;

class Especie
{
    private int $especie_id;
    private string $nome;


    public function getEspecieId(): int
    {
        return $this->especie_id;
    }

    public function setEspecieId(int $id): void
    {
        $this->especie_id = $id;
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