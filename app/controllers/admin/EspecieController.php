<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\repositories\EspecieRepository;
use app\database\ConnectionFactory;
use Exception;

class EspecieController extends Controller
{
    private EspecieRepository $especieRepo;

    public function __construct()
    {
        $this->especieRepo = new EspecieRepository();
    }

    /**
     * Endpoint para requisições AJAX/Fetch do JavaScript
     * Rota sugerida: /especies/json
     */
    public function buscarJson()
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $especies = $this->especieRepo->buscarTodas($pdo);
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $especies]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar espécies.']);
        }
        exit;
    }
}