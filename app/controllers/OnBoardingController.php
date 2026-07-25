<?php

namespace app\controllers;

use app\core\Controller;
use app\services\OnboardingService;
use Exception;

class OnboardingController extends Controller
{
    private OnboardingService $onboardingService;

    public function __construct()
    {
        $this->onboardingService = new OnboardingService();
        $this->autenticacaoRequired(); // Garante que o usuário tem sessão (email/senha já cadastrados)
    }

    public function index()
    {
        $this->view('onboarding/selecionar_perfil');
    }

    public function adotante()
    {
        $this->view('onboarding/adotante_passos');
    }

    public function ong()
    {
        $this->view('onboarding/ong_passos');
    }

    public function storeAdotante()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_logado']->usuario_id;
                $this->onboardingService->processarAdotante($_POST, $usuarioId);
                $this->redirect('/home');
            } catch (Exception $e) {
                // Log do erro e redirecionamento com mensagem
                $this->redirect('/onboarding/adotante?erro=1');
            }
        }
    }

    public function storeOng()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_logado']->usuario_id;
                $this->onboardingService->processarOng($_POST, $usuarioId);
                $this->redirect('/home');
            } catch (Exception $e) {
                $this->redirect('/onboarding/ong?erro=1');
            }
        }
    }
}