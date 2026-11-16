<?php 
namespace app\controllers\protetores;
use app\core\Controller;

class OngController extends Controller
{
    public function __construct()
    {
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