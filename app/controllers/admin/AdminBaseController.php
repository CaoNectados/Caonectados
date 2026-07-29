<?php

namespace app\controllers\admin;

use app\core\Controller;

class AdminBaseController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
            $this->redirect('/login');
            exit;
        }
    }
}