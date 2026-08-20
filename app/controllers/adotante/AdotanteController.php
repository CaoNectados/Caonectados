<?php

namespace app\controllers\adotante;

use app\core\Controller;

class AdotanteController extends Controller
{
    // Usado por: (não referenciado atualmente)
  public function __construct()
    {
        $this->autenticacaoRequired(['adotante']);
    }
}


