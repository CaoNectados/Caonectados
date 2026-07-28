<?php

namespace app\controllers\protetores;

use app\core\Controller;

class ProtetoresController extends Controller
{
    public function __construct()
    {
       
        $this->autenticacaoRequired(['protetor']);
    }
}