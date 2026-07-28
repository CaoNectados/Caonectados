<?php 
namespace app\controllers\protetores;
use app\core\Controller;

class OngController extends Controller
{
    public function __construct()
    {
        // aqui só permita acesso se for ong, vamos usar esse método para proteger as rotas que são restritas
        $this->autenticacaoRequired(['ong']);
    }

    public function dashboard()
    {
        $this->view('ong/dashboard', [
            'titulo' => 'Painel da ONG'
        ]);
    }
}
?>