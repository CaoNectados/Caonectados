<?php

namespace app\controllers;

use app\core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        echo '✅ Sucesso! O sistema de rotas do CãoNectados está funcionando perfeitamente!';
    }
}