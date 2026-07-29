<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\database\ConnectionFactory;
use Exception;

class UsuarioController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
            $this->redirect('/login');
        }

        $pdo = ConnectionFactory::getConnection();
        // Inicializar repositório e service aqui quando criá-los
    }

    public function index(): void
    {
        // $usuarios = $this->service->listarTodos();
        
        $this->view('admin/usuarios/index', [
            // 'usuarios' => $usuarios
        ]);
    }
}