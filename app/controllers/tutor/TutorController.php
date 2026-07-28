<?php

namespace app\controllers\tutor;

use app\core\Controller;

class TutoresController extends Controller
{
  public function __construct()
    {
        // aqui só permita acesso se for ong, vamos usar esse método para proteger as rotas que são restritas
        $this->autenticacaoRequired(['tutor']);
    }
}