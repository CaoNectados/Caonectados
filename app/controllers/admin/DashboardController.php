<?php

namespace app\controllers\admin;

class DashboardController extends AdminBaseController
{
    // Usado por: rota GET /admin/dashboard
    public function index()
    {
        $this->view('admin/dashboard', [
            'titulo' => 'Dashboard'
        ]);
    }
}