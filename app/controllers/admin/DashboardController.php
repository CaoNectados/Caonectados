<?php

namespace app\controllers\admin;

use app\repositories\ProtetorRepository;
use app\repositories\UsuarioRepository;

class DashboardController extends AdminBaseController
{
    // Usado por: rota GET /admin/dashboard
    public function index()
    {
        $protetorRepo = new ProtetorRepository();
        $usuarioRepo = new UsuarioRepository();

        $this->view('admin/dashboard', [
            'titulo' => 'Dashboard',
            'stats'  => [
                'cadastros_pendentes'       => $protetorRepo->contarPendentes(),
                'usuarios_ativos'           => $usuarioRepo->contarAtivos(),
            ],
        ]);
    }
}
