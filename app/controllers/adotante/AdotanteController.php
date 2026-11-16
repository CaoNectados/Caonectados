<?php

namespace app\controllers\adotante;

use app\core\Controller;

class AdotanteController extends Controller
{
  public function __construct()
    {
        $this->autenticacaoRequired(['adotante']);
    }
}


