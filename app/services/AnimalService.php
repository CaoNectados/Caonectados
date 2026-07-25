<?php

namespace app\services;

use app\models\Animais;
use app\repositories\AnimaisRepository;

class AnimaisService
{
    private AnimaisRepository $animaisRepository;

    public function __construct(AnimaisRepository $animaisRepository)
    {
        $this->animaisRepository = $animaisRepository;
    }

    public function cadastrarAnimal(Animais $animal): void
    {
    }

    public function atualizarStatus(Animais $animal): void
    {
    }

    public function desativarAnimal(Animais $animal): void
    {
    }

    public function reativarAnimal(Animais $animal): void
    {
    }
}