<?php

namespace app\controllers\admin;

class SolicitacaoProtetorController extends AdminBaseController
{
    public function index()
    {
        // View de validação de cadastros (ONGs e Protetores)
        $this->view('admin/validacao_cadastros', [
            'titulo' => 'Tela de Validação de Cadastros'
        ]);
    }
}