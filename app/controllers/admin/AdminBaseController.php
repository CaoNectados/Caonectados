<?php

namespace app\controllers\admin;

use app\core\Controller;

class AdminBaseController extends Controller
{
    // Base comum das telas e ações administrativas para exigir sessão com perfil Admin.
    public function __construct()
    {
        $this->autenticacaoRequired(['administrador']);
    }
}
