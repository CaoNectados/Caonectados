<?php

namespace app\controllers\admin;

class DashboardController extends AdminBaseController
{
    public function index()
    {
        $this->view('admin/dashboard', [
            'titulo' => 'Dashboard'
        ]);
    }
}