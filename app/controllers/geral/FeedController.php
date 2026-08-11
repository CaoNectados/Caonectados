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
        // Exige que o usuário esteja logado para acessar o Feed
        $this->autenticacaoRequired();
        $this->feedRepo = new FeedRepository();
    }

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