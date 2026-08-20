<?php

namespace app\controllers\admin;

use app\core\Controller;

class AdminBaseController extends Controller
{
    // Usado por: DashboardController e SolicitacaoProtetorController (heranca)
    public function __construct()
    {
        $this->autenticacaoRequired(['administrador']);
    }
}