<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\repositories\RacaRepository;
use app\database\ConnectionFactory;
use Exception;

class RacaController extends Controller
{
    private RacaRepository $racaRepo;

    public function __construct()
    {
        $this->racaRepo = new RacaRepository();
    }

    /**
     * Endpoint para requisições AJAX/Fetch do JavaScript
     * Rota sugerida: /racas/json?especie_id=1
     */
    public function buscarJson()
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $especieId = filter_input(INPUT_GET, 'especie_id', FILTER_VALIDATE_INT);
            
            if ($especieId) {
                $racas = $this->racaRepo->buscarPorEspecie($pdo, $especieId);
            } else {
                $racas = $this->racaRepo->buscarTodas($pdo);
            }
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $racas]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar raças.']);
        }
        exit;
    }
}