<?php

namespace app\services;

use app\models\Denuncias;
use app\repositories\DenunciasRepository;

class DenunciasService
{
    private DenunciasRepository $denunciasRepository;

    public function __construct(DenunciasRepository $denunciasRepository)
    {
        $this->denunciasRepository = $denunciasRepository;
    }

    public function abrirDenuncia(Denuncias $denuncia): void
    {
    }

    public function aprovarDenuncia(Denuncias $denuncia): void
    {
    }

    public function reprovarDenuncia(Denuncias $denuncia): void
    {
    }

    public function colocarEmAnalise(Denuncias $denuncia): void
    {
    }
}