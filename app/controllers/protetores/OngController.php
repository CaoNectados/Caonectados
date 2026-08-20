<?php 
namespace app\controllers\protetores;
use app\core\Controller;

class OngController extends Controller
{
    // Usado por: (não referenciado atualmente)
    public function __construct()
    {
        $this->autenticacaoRequired(['ong']);
    }

    // Usado por: (não referenciado atualmente)
    public function dashboard()
    {
        $this->view('ong/dashboard', [
            'titulo' => 'Painel da ONG'
        ]);
    }
}
?>