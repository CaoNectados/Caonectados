<?php

namespace app\controllers\admin;

use app\core\Controller;

/**
 * Base para controllers restritos ao administrador. Herdada por DashboardController
 * e SolicitacaoProtetorController.
 */
class AdminBaseController extends Controller
{
    public function __construct()
    {
        $this->autenticacaoRequired(['administrador']);
    }
}
