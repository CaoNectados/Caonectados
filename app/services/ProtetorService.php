<?php

namespace app\services;

use app\models\Protetores;
use app\repositories\ProtetoresRepository;

class ProtetoresService
{
    private ProtetoresRepository $protetoresRepository;

    public function __construct(ProtetoresRepository $protetoresRepository)
    {
        $this->protetoresRepository = $protetoresRepository;
    }

    public function aprovarCadastro(Protetores $protetor): void
    {
    }

    public function reprovarCadastro(Protetores $protetor): void
    {
    }
}