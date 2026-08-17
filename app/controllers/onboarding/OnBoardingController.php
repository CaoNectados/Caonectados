<?php

namespace app\controllers\onboarding;

use app\core\Controller;
use app\services\OnboardingService;
use app\repositories\RegiaoRepository;
use app\repositories\EspecieRepository;
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
    public function index()
    {
        $this->view('onboarding/selecionar_perfil', [
            'titulo'    => 'Selecionar Perfil',
            'descricao' => 'Escolha o tipo de perfil que deseja criar.'
        ]);
    }
    private function verificarSeJaPossuiPerfil()
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if ($usuarioId) {
            // O serviço de checagem deve delegar a busca ao repositório
            if ($this->onboardingService->usuarioJaPossuiPerfil($usuarioId)) {
                if ($_SERVER['REQUEST_URI'] !== '/feed' && $_SERVER['REQUEST_URI'] !== '/') {
                    $this->redirect('/feed');
                    exit;
                }
            }
        } else {
            $this->redirect('/login');
            exit;
        }
    }

    public function adotante()
    {
        // Repositories buscam a conexão via BaseRepository automaticamente
        $regioes = $this->regiaoRepo->buscarTodas();
        $especies = $this->especieRepo->buscarTodas();

        $this->view('onboarding/adotante_onboarding', [
            'titulo'   => 'Cadastro de Adotante',
            'regioes'  => $regioes,
            'especies' => $especies
        ]);
    }

    public function ong()
    {
        $regioes = $this->regiaoRepo->buscarTodas();

        $this->view('onboarding/protetor_onboarding', [
            'titulo'      => 'Cadastro de ONG',
            'regioes'     => $regioes,
            'tipo_perfil' => 'cnpj'
        ]);
    }

    public function protetor()
    {
        $regioes = $this->regiaoRepo->buscarTodas();

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

                $this->json(200, [
                    'status'       => 'sucesso',
                    'mensagem'     => 'Cadastro enviado para análise com sucesso!',
                    'redirect_url' => URL_BASE . '/aguardando-aprovacao'
                ]);
            } catch (Exception $e) {
                $this->json(200, [
                    'status'   => 'erro',
                    'mensagem' => $e->getMessage()
                ]);
            }
        }
    }

    public function salvarAdotante()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['usuario_logado']->usuario_id ?? null;

                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                ValidationService::validarCamposObrigatorios($dadosLimpos, ['regiao_id', 'nome_usuario']);

                $this->onboardingService->processarAdotante($dadosLimpos, $_FILES, $usuarioId);

                $_SESSION['boas_vindas_nome'] = $dadosLimpos['nome_usuario'];
                $_SESSION['boas_vindas_tipo'] = 'adotante';

                $this->json(200, [
                    'status'       => 'sucesso',
                    'mensagem'     => 'Seu perfil foi criado com sucesso!',
                    'redirect_url' => URL_BASE . '/feed'
                ]);
            } catch (Exception $e) {
                $this->json(200, [
                    'status'   => 'erro',
                    'mensagem' => $e->getMessage()
                ]);
            }
        }
    }

  public function aguardandoAprovacao()
    {
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;

        // Se o usuário atual for um adotante, ou se a ONG dele JÁ FOI validada, não tem porque ele estar nesta tela.
        if ($tipoPerfil === 'adotante' || $validado === true || $validado === 1) {
            $this->redirect('/feed');
        }

        $this->view('onboarding/aguardando_aprovacao', [
            'titulo' => 'Aguardando Aprovação'
        ]);
    }
}


