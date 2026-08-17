<?php

namespace app\controllers\adotante;

use app\core\Controller;

class AdotanteController extends Controller
{
  public function __construct()
    {
        // aqui só permita acesso se for ong, vamos usar esse método para proteger as rotas que são restritas
        $this->autenticacaoRequired(['adotante']);
    }
}


