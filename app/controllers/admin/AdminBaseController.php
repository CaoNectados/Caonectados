<?php

namespace app\controllers\admin;

use app\core\Controller;

class AdminBaseController extends Controller
{
    public function __construct()
    {
        $this->autenticacaoRequired(['administrador']);
    }
}