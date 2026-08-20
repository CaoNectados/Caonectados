<?php

namespace app\controllers\protetores;

use app\core\Controller;

class ProtetorController extends Controller
{
    public function __construct()
    {
       
        $this->autenticacaoRequired(['protetor']);
    }
}