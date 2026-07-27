<?php

namespace app\models;

use PDO;

class Regiao
{
    private int $regiao_id;
    private string $nome_regiao;
    public function getRegiaoId(): int
    {
        return $this->regiao_id;
    }

    public function setRegiaoId(int $id): void
    {
        $this->regiao_id = $id;
    }

    public function getNomeRegiao(): string
    {
        return $this->nome_regiao;
    }

    public function setNomeRegiao(string $nome): void
    {
        $this->nome_regiao = $nome;
    }

}