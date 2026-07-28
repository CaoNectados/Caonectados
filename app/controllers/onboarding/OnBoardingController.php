<?php

namespace app\controllers\onboarding;

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
        $this->verificarSeJaPossuiPerfil();
    }

    /**
     * Função auxiliar para retornar respostas em JSON para o AJAX
     */
    private function responderJson(string $status, string $mensagem, ?string $redirectUrl = null)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status'       => $status,
            'mensagem'     => $mensagem,
            'redirect_url' => $redirectUrl
        ]);
        exit;
    }

    private function verificarSeJaPossuiPerfil()
    {
        $usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['usuario_logado']->usuario_id ?? null;
        if ($usuarioId) {
            $pdo = ConnectionFactory::getConnection();

            $stmtTutor = $pdo->prepare("SELECT COUNT(*) FROM TUTOR WHERE usuario_id = ?");
            $stmtTutor->execute([$usuarioId]);
            $temTutor = $stmtTutor->fetchColumn() > 0;

            $stmtProtetor = $pdo->prepare("SELECT COUNT(*) FROM PROTETOR WHERE usuario_id = ?");
            $stmtProtetor->execute([$usuarioId]);
            $temProtetor = $stmtProtetor->fetchColumn() > 0;

            if ($temTutor || $temProtetor) {
                if ($_SERVER['REQUEST_URI'] !== '/feed' && $_SERVER['REQUEST_URI'] !== '/') {
                    $this->redirect('/feed');
                    exit;
                }
            }
        }else {
            $this->redirect('/login');
            exit;
        }
    }

    public function index()
    {
        $this->view('onboarding/selecionar_perfil', [
            'titulo'    => 'Selecionar Perfil',
            'descricao' => 'Escolha o tipo de perfil que deseja criar.',
        ]);
    }

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

                ValidationService::validarCamposObrigatorios($_POST, ['nome_fantasia', 'cnpj_cpf', 'regiao_id']);

                $tipoDoc = $_POST['tipo_documento'] ?? 'cpf';
                $documentoBruto = $_POST['cnpj_cpf'];
                $documentoLimpo = preg_replace('/[^0-9]/', '', $documentoBruto);

                if ($tipoDoc === 'cnpj') {
                    if (strlen($documentoLimpo) !== 14 || !ValidationService::validarCnpj($documentoLimpo)) {
                        throw new Exception("Formato inválido. As ONGs devem informar um CNPJ válido com 14 dígitos.");
                    }
                    if (!ValidationService::verificarExistenciaCnpjReal($documentoLimpo)) {
                        throw new Exception("O CNPJ informado não consta como ativo na Receita Federal.");
                    }
                } else {
                    if (strlen($documentoLimpo) !== 11 || !ValidationService::validarCpf($documentoLimpo)) {
                        throw new Exception("Formato inválido. Protetores Independentes devem informar um CPF válido com 11 dígitos.");
                    }
                }

                if (!empty($_POST['instagram']) && !ValidationService::validarLinkRedeSocial($_POST['instagram'], 'instagram')) {
                    throw new Exception("O link informado para o Instagram é inválido.");
                }

                if (!empty($_POST['facebook']) && !ValidationService::validarLinkRedeSocial($_POST['facebook'], 'facebook')) {
                    throw new Exception("O link informado para o Facebook é inválido.");
                }

                if (!empty($_POST['chave_pix']) && !ValidationService::validarChavePix($_POST['chave_pix'])) {
                    throw new Exception("A Chave PIX informada não é válida.");
                }

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                $this->onboardingService->processarOng($dadosLimpos, $_FILES, $usuarioId);

                // Em caso de sucesso, retorna JSON
                $this->responderJson('sucesso', 'Cadastro enviado para análise com sucesso!', URL_BASE . '/aguardando-aprovacao');
            } catch (Exception $e) {
                // Em caso de erro, retorna JSON
                $this->responderJson('erro', $e->getMessage());
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

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                ValidationService::validarCamposObrigatorios($dadosLimpos, ['regiao_id', 'nome_usuario']);

                $this->onboardingService->processarTutor($dadosLimpos, $_FILES, $usuarioId);

                $_SESSION['boas_vindas_nome'] = $dadosLimpos['nome_usuario'];
                $_SESSION['boas_vindas_tipo'] = 'adotante';

                // Em caso de sucesso, retorna JSON
                $this->responderJson('sucesso', 'Seu perfil foi criado com sucesso!', URL_BASE . '/feed');
            } catch (Exception $e) {
                // Em caso de erro, retorna JSON
                $this->responderJson('erro', $e->getMessage());
            }
        }
    }

    public function aguardandoAprovacao()
    {
        $statusConta = $_SESSION['status_conta'] ?? 'pendente';
        if ($statusConta === 'ativo' || $statusConta === 'aprovado') {
            $this->redirect('/');
        }

        $this->view('onboarding/aguardando_aprovacao', [
            'titulo' => 'Aguardando Aprovação'
        ]);
    }
}
