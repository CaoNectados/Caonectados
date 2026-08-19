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

    private function verificarSeJaPossuiPerfil(): void
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        if (!$usuarioId) {
            $this->redirect('/login');
            exit;
        }

        $uriAtual = $this->getUriLimpa();

        // Se o usuário estiver tentando salvar algo ou já estiver na tela de aguardando, NÃO VERIFICA
        $rotasPermitidas = [
            '/aguardando-aprovacao',
            '/onboarding/aguardando-aprovacao',
            '/onboarding/salvar-adotante',
            '/onboarding/salvar-protetor',
            '/onboarding/especies-ativas'
        ];

        if (in_array($uriAtual, $rotasPermitidas, true)) {
            return;
        }

        // Se o usuário já preencheu o formulário (tem registro nas tabelas)
        if ($this->onboardingService->usuarioJaPossuiPerfil((int)$usuarioId)) {
            $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
            $validado = $_SESSION['validado'] ?? false;

            // 1. É protetor e está pendente? Manda pra tela de espera (se já não estiver lá)
            if (in_array($tipoPerfil, ['protetor', 'ong'], true) && ($validado === false || $validado === 0 || $validado === '0')) {
                if ($uriAtual !== '/aguardando-aprovacao' && $uriAtual !== '/onboarding/aguardando-aprovacao') {
                    $this->redirect('/aguardando-aprovacao');
                    exit;
                }
                return;
            }

            // 2. É adotante ou protetor aprovado? Manda pro feed (se já não estiver lá)
            if ($uriAtual !== '/feed' && $uriAtual !== '/') {
                $this->redirect('/feed');
                exit;
            }
        }
    }

    public function especiesAtivasJson(): void
    {
        $especies = $this->especieRepo->buscarAtivas();
        $this->json(200, [
            'status' => 'sucesso',
            'dados'  => $especies
        ]);
    }

    public function adotante(): void
    {
        $regioes = $this->regiaoRepo->buscarTodas();
        $especies = $this->especieRepo->buscarAtivas();

        $this->view('onboarding/adotante_onboarding', [
            'titulo'   => 'Cadastro de Adotante',
            'regioes'  => $regioes,
            'especies' => $especies
        ]);
    }

    public function ong(): void
    {
        $regioes = $this->regiaoRepo->buscarTodas();

        $this->view('onboarding/protetor_onboarding', [
            'titulo'      => 'Cadastro de ONG',
            'regioes'     => $regioes,
            'tipo_perfil' => 'cnpj'
        ]);
    }

    public function protetor(): void
    {
        $regioes = $this->regiaoRepo->buscarTodas();

        $this->view('onboarding/protetor_onboarding', [
            'titulo'      => 'Cadastro de Protetor',
            'regioes'     => $regioes,
            'tipo_perfil' => 'cpf'
        ]);
    }

    public function salvarAdotante(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['usuario_logado']->usuario_id ?? null;

                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                ValidationService::validarCamposObrigatorios($dadosLimpos, ['regiao_id', 'nome_usuario']);

                $especiesSelecionadas = $_POST['preferencias_especie'] ?? [];
                if (!empty($especiesSelecionadas)) {
                    $especiesAtivas = $this->especieRepo->buscarAtivas();
                    $idsAtivos = array_map(fn($e) => (int)$e['especie_id'], $especiesAtivas);

                    foreach ($especiesSelecionadas as $espId) {
                        if (!in_array((int)$espId, $idsAtivos, true)) {
                            throw new Exception("Uma das espécies selecionadas não está mais ativa no sistema. Por favor, atualize suas opções.");
                        }
                    }
                }

                $this->onboardingService->processarAdotante($dadosLimpos, $_FILES, (int)$usuarioId);

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

    public function salvarProtetor(): void
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
                $documentoLimpo = preg_replace('/[^0-9]/', '', (string)$documentoBruto);

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
                $this->onboardingService->processarOng($dadosLimpos, $_FILES, (int)$usuarioId);

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

    public function aguardandoAprovacao(): void
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        if (!$usuarioId) {
            $this->redirect('/login');
            exit;
        }

        // Garante que o status na sessão é o mesmo do banco de dados (recarrega os dados do Protetor)
        $this->onboardingService->usuarioJaPossuiPerfil((int)$usuarioId);

        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;

        // Se for adotante ou se o protetor/ONG JÁ FOI aprovado (1/true), manda direto para o feed
        if ($tipoPerfil === 'adotante' || $validado === true || $validado === 1 || $validado === '1') {
            $this->redirect('/feed');
            exit;
        }

        // Caso contrário, renderiza a tela de espera normalmente SEM REDIRECIONAR MAIS NADA
        $this->view('onboarding/aguardando_aprovacao', [
            'titulo' => 'Aguardando Aprovação'
        ]);
    }
}