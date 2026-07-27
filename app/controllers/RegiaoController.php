<?php

namespace app\controllers;

use app\core\Controller;
use app\repositories\RegiaoRepository;
use app\database\ConnectionFactory;
use Exception;

class RegiaoController extends Controller
{
    private RegiaoRepository $regiaoRepo;

    public function __construct()
    {
        $this->regiaoRepo = new RegiaoRepository();
    }

    /**
     * Endpoint para requisições AJAX/Fetch do JavaScript
     * Rota sugerida: /regioes/json
     */
    public function buscarJson()
    {
        try {
            $pdo = ConnectionFactory::getConnection();
            $regioes = $this->regiaoRepo->buscarTodas($pdo);
            
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'dados' => $regioes]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar regiões.']);
        }
        exit;
    }
}