<?php

namespace app\services;

use app\models\Usuarios;
use app\repositories\UsuariosRepository;

class UsuariosService
{
    private UsuariosRepository $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }

    public function validarCadastro(Usuarios $usuario): void
    {
    }

    public function validarAtualizacao(Usuarios $usuario): void
    {
    }
}