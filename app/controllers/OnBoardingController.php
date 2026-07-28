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

        // BLOQUEIO DE ACESSO: Se o usuário já tiver um perfil cadastrado, impede de refazer o onboarding
        $this->verificarSeJaPossuiPerfil();
    }

    private function verificarSeJaPossuiPerfil()
    {
        $usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['usuario_logado']->usuario_id ?? null;

        if ($usuarioId) {
            $pdo = ConnectionFactory::getConnection();
            
            // Verifica se já existe registro como Tutor
            $stmtTutor = $pdo->prepare("SELECT COUNT(*) FROM tutor WHERE usuario_id = ?");
            $stmtTutor->execute([$usuarioId]);
            $temTutor = $stmtTutor->fetchColumn() > 0;

            // Verifica se já existe registro como Protetor/ONG
            $stmtProtetor = $pdo->prepare("SELECT COUNT(*) FROM protetor WHERE usuario_id = ?");
            $stmtProtetor->execute([$usuarioId]);
            $temProtetor = $stmtProtetor->fetchColumn() > 0;

            // Se já tiver qualquer um dos perfis cadastrados, redireciona para a home (ou feed)
            if ($temTutor || $temProtetor) {
                // Evita loop caso ele já esteja tentando acessar a home
                if ($_SERVER['REQUEST_URI'] !== '/feed' && $_SERVER['REQUEST_URI'] !== '/') {
                    $this->redirect('/feed'); // Ou redirecione para '/' se preferir
                    exit;
                }
            }
        }
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

                // 1. Validar campos obrigatórios nos dados brutos
                ValidationService::validarCamposObrigatorios($_POST, ['nome_fantasia', 'cnpj_cpf', 'regiao_id']);

                // 2. Validar CPF ou CNPJ
                $tipoDoc = $_POST['tipo_documento'] ?? 'cpf';
                $documento = $_POST['cnpj_cpf'];

                if ($tipoDoc === 'cnpj') {
                    if (!ValidationService::validarCnpj($documento)) {
                        throw new Exception("O CNPJ informado possui um formato inválido.");
                    }

                    if (!ValidationService::verificarExistenciaCnpjReal($documento)) {
                        throw new Exception("O CNPJ informado não consta como ativo na Receita Federal.");
                    }
                } else {
                    if (!ValidationService::validarCpf($documento)) {
                        throw new Exception("O CPF informado possui um formato inválido.");
                    }
                }

                // 3. Validar links sociais e chave PIX ANTES de sanitizar as strings
                if (!empty($_POST['instagram']) && !ValidationService::validarLinkRedeSocial($_POST['instagram'], 'instagram')) {
                    throw new Exception("O link informado para o Instagram é inválido.");
                }

                if (!empty($_POST['facebook']) && !ValidationService::validarLinkRedeSocial($_POST['facebook'], 'facebook')) {
                    throw new Exception("O link informado para o Facebook é inválido.");
                }

                if (!empty($_POST['chave_pix']) && !ValidationService::validarChavePix($_POST['chave_pix'])) {
                    throw new Exception("A Chave PIX informada não é válida.");
                }

                // 4. Sanitizar os dados apenas ao final antes de enviar para o banco
                $dadosLimpos = ValidationService::sanitizarArray($_POST);

                $this->onboardingService->processarOng($dadosLimpos, $_FILES, $usuarioId);

                $this->redirect('/aguardando-aprovacao');
            } catch (Exception $e) {
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

                // 3. DISPARA O MODAL DE BOAS-VINDAS NO FEED
                $_SESSION['boas_vindas_nome'] = $dadosLimpos['nome_usuario'];
                $_SESSION['boas_vindas_tipo'] = 'adotante';

                $this->redirect('/feed');
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