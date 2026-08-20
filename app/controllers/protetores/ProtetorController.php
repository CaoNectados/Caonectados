<?php

namespace app\controllers\protetores;

use app\core\Controller;

class ProtetorController extends Controller
{
    // Usado por: (não referenciado atualmente)
    public function __construct()
    {
        $this->autenticacaoRequired(['protetor']);
    }
}