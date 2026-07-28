<?php

namespace app\services;

use app\models\Protetor;
use app\repositories\ProtetorRepository;

class ProtetoreService
{
    private ProtetorRepository $protetorRepository;

    public function __construct(ProtetorRepository $protetorRepository)
    {
        $this->protetorRepository = $protetorRepository;
    }

    public function aprovarCadastro(Protetor $protetor): void
    {
    }

    public function reprovarCadastro(Protetor $protetor): void
    {
    }
}