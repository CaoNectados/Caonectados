<?php

namespace app\controllers\admin;

use app\repositories\ProtetorRepository;
use app\repositories\UsuarioRepository;
use DateTimeImmutable;

class DashboardController extends AdminBaseController
{
    // Usado por: rota GET /admin/dashboard
    public function index()
    {
        $protetorRepo = new ProtetorRepository();
        $usuarioRepo = new UsuarioRepository();

        $inicioDoMes = (new DateTimeImmutable())->format('Y-m-01 00:00:00');


        $this->view('admin/dashboard', [
            'titulo' => 'Dashboard',
            'stats'  => [
                'cadastros_pendentes'       => $protetorRepo->contarPendentes(),
                'usuarios_ativos'           => $usuarioRepo->contarAtivos(),
            ],
        ]);
    }
}
