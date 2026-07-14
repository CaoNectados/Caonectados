<?php

namespace app\controllers;

use app\core\Controller;

/**
 * Controller da página inicial.
 * Responsabilidade única: receber a requisição e devolver a View.
 * (Página institucional/estática — não há regra de negócio, logo não há Service.)
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'titulo'    => 'Home',
            'descricao' => 'Plataforma de adoção de animais da tríplice fronteira. '
                         . 'Conectamos pets que precisam de um lar com humanos dispostos a dar muito amor.',
        ]);
    }
}
