<?php

namespace app\services;

use app\models\Tutores;
use app\repositories\TutoresRepository;

class TutoresService
{
    private TutoresRepository $tutoresRepository;

    public function __construct(TutoresRepository $tutoresRepository)
    {
        $this->tutoresRepository = $tutoresRepository;
    }

    public function validarCadastro(Tutores $tutor): void
    {
    }

    public function atualizarPerfil(Tutores $tutor): void
    {
    }
}