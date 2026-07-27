<?php

namespace app\controllers;

use app\core\Controller;
use app\services\OnboardingService;
use app\repositories\RegiaoRepository;
use app\repositories\EspecieRepository;
use app\database\ConnectionFactory;
use app\services\ValidationService;
use Exception;

class OnboardingController extends Controller
{
    private OnboardingService $onboardingService;
    private RegiaoRepository $regiaoRepo;
    private EspecieRepository $especieRepo;

    public function __construct()
    {
        $this->onboardingService = new OnboardingService();
        $this->regiaoRepo = new RegiaoRepository();
        $this->especieRepo = new EspecieRepository();

        $this->autenticacaoRequired(); 
    }

    public function index()
    {
        $this->view('onboarding/selecionar_perfil', [
            'titulo'    => 'Selecionar Perfil',
            'descricao' => 'Escolha o tipo de perfil que deseja criar.',
        ]);
    }

    // Fluxo do Adotante/Tutor
    public function tutor()
    {
        $pdo = ConnectionFactory::getConnection();
        $regioes = $this->regiaoRepo->buscarTodas($pdo);
        $especies = $this->especieRepo->buscarTodas($pdo);

        $this->view('onboarding/tutor_onboarding', [
            'titulo'   => 'Cadastro de Tutor',
            'regioes'  => $regioes,
            'especies' => $especies
        ]);
    }


    public function ong()
    {
        $pdo = ConnectionFactory::getConnection();
        $regioes = $this->regiaoRepo->buscarTodas($pdo);

        $this->view('onboarding/protetor_onboarding', [
            'titulo'      => 'Cadastro de ONG',
            'regioes'     => $regioes,
            'tipo_perfil' => 'cnpj' 
        ]);
    }

    public function protetor()
    {
        $pdo = ConnectionFactory::getConnection();
        $regioes = $this->regiaoRepo->buscarTodas($pdo);

        $this->view('onboarding/protetor_onboarding', [
            'titulo'      => 'Cadastro de Protetor',
            'regioes'     => $regioes,
            'tipo_perfil' => 'cpf' 
        ]);
    }

    public function salvarProtetor()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['usuario_logado']->usuario_id ?? null;

                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                // 1. Sanitizar todos os dados para evitar ataques XSS
                $dadosLimpos = ValidationService::sanitizarArray($_POST);

                // 2. Verificar se os campos essenciais estão preenchidos
                ValidationService::validarCamposObrigatorios($dadosLimpos, ['nome_fantasia', 'cnpj_cpf', 'regiao_id']);

                // 3. Validar se o documento é válido matematicamente
                $tipoDoc = $dadosLimpos['tipo_documento'] ?? 'cpf';
                $documento = $dadosLimpos['cnpj_cpf'];

                if ($tipoDoc === 'cnpj' && !ValidationService::validarCnpj($documento)) {
                    throw new Exception("O CNPJ informado é inválido.");
                } elseif ($tipoDoc === 'cpf' && !ValidationService::validarCpf($documento)) {
                    throw new Exception("O CPF informado é inválido.");
                }

                // Passamos $dadosLimpos ao invés de $_POST cru para o Service
                $this->onboardingService->processarOng($dadosLimpos, $_FILES, $usuarioId);

                $this->redirect('/aguardando-aprovacao');
            } catch (Exception $e) {
                // Se qualquer validação falhar, o usuário é jogado de volta com a mensagem de erro
                $this->redirecionarComMensagem('erro', $e->getMessage(), '/onboarding', $e->getMessage());
            }
        }
    }

    public function salvarTutor()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['usuario_logado']->usuario_id ?? null;

                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                // 1. Sanitizar dados contra scripts maliciosos
                $dadosLimpos = ValidationService::sanitizarArray($_POST);

                // 2. Validar campos obrigatórios do Tutor
                ValidationService::validarCamposObrigatorios($dadosLimpos, ['regiao_id', 'nome_usuario']);

                // Passamos os dados já higienizados para o Service
                $this->onboardingService->processarTutor($dadosLimpos, $_FILES, $usuarioId);

                $this->redirecionarComMensagem('sucesso', 'Cadastro de tutor realizado com sucesso!', '/home');
            } catch (Exception $e) {
                $this->redirecionarComMensagem('erro', $e->getMessage(), '/onboarding/tutor', $e->getMessage());
            }
        }
    }

    public function aguardandoAprovacao()
    {
        // Se já estiver aprovado, não tem porque ver essa tela, manda pra home
        $statusConta = $_SESSION['status_conta'] ?? 'pendente';
        if ($statusConta === 'ativo' || $statusConta === 'aprovado') {
            $this->redirect('/');
        }

        $this->view('onboarding/aguardando_aprovacao', [
            'titulo' => 'Aguardando Aprovação'
        ]);
    }
}