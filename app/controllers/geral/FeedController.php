<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\repositories\FeedRepository;
use Exception;

class FeedController extends Controller
{
    private FeedRepository $feedRepo;

    public function __construct()
    {
        // Garante que o usuário está autenticado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
            exit;
        }

        $this->feedRepo = new FeedRepository();
    }

    public function feed()
    {      
        try {

            $this->view('feed/feed', [
                'titulo'  => 'Feed de Adoção'
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar o feed.', '/home', $e->getMessage());
        }
    }
}