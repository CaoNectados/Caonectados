<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\repositories\FeedRepository;
use Exception;

class FeedController extends Controller
{
    private FeedRepository $feedRepo;

    // Usado por: instanciado ao acessar a rota /feed
    public function __construct()
    {
        // Exige que o usuário esteja logado para acessar o Feed
        $this->autenticacaoRequired();
        $this->feedRepo = new FeedRepository();
    }

    // Usado por: rota GET /feed (atualmente comentada em public/index.php, mantida para reativação futura)
    public function feed(): void
    {
        try {
            $animais = $this->feedRepo->buscarAnimaisFeed();

            $this->view('feed/feed', [
                'titulo'  => 'Feed de Adoção',
                'animais' => $animais
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar o feed de publicação.', '/home', $e->getMessage());
        }
    }
}