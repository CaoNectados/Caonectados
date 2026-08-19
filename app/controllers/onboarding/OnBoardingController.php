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
        }

        $uriAtual = $this->getUriLimpa();

        // Rotas liberadas durante o fluxo de onboarding / edição
        $rotasPermitidas = [
            '/aguardando-aprovacao',
            '/onboarding/aguardando-aprovacao',
            '/onboarding/salvar-adotante',
            '/onboarding/salvar-protetor',
            '/onboarding/especies-ativas',
            '/onboarding/protetor',
            '/onboarding/ong',
            '/logout'
        ];

        if (in_array($uriAtual, $rotasPermitidas, true)) {
            return;
        }

        if ($this->onboardingService->usuarioJaPossuiPerfil((int)$usuarioId)) {
            $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
            $validado = $_SESSION['validado'] ?? false;
            $recusado = $_SESSION['recusado'] ?? false;

            if (in_array($tipoPerfil, ['protetor', 'ong'], true)) {
                if (!$validado || $recusado) {
                    if ($uriAtual !== '/aguardando-aprovacao') {
                        $this->redirect('/aguardando-aprovacao');
                    }
                    return;
                }
            }

            if ($uriAtual !== '/feed') {
                $this->redirect('/feed');
            }
        }
    }

    public function ong(): void
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $regioes = $this->regiaoRepo->buscarTodas();
        $dadosExistentes = $this->onboardingService->obterDadosPreenchidosProtetor($usuarioId);

        $this->view('onboarding/protetor_onboarding', [
            'titulo'        => 'Cadastro de ONG',
            'regioes'       => $regioes,
            'tipo_perfil'   => 'cnpj',
            'modoEdicao'    => !empty($dadosExistentes),
            'dadosProtetor' => $dadosExistentes ?? []
        ]);
    }

    public function protetor(): void
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        $regioes = $this->regiaoRepo->buscarTodas();
        $dadosExistentes = $this->onboardingService->obterDadosPreenchidosProtetor($usuarioId);

        $this->view('onboarding/protetor_onboarding', [
            'titulo'        => 'Cadastro de Protetor',
            'regioes'       => $regioes,
            'tipo_perfil'   => 'cpf',
            'modoEdicao'    => !empty($dadosExistentes),
            'dadosProtetor' => $dadosExistentes ?? []
        ]);
    }

    public function salvarProtetor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                $this->onboardingService->processarOng($dadosLimpos, $_FILES, (int)$usuarioId);

                $this->json(200, [
                    'status'       => 'sucesso',
                    'mensagem'     => 'Solicitação enviada para análise com sucesso!',
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
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if (!$usuarioId) {
            $this->redirect('/login');
        }

        $this->onboardingService->usuarioJaPossuiPerfil($usuarioId);

        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;
        $recusado = $_SESSION['recusado'] ?? false;

        if ($validado && !$recusado) {
            $_SESSION['boas_vindas_tipo'] = $tipoPerfil;
            $_SESSION['boas_vindas_nome'] = $_SESSION['usuario_nome'] ?? 'Protetor';
            $this->redirect('/feed');
        }

        $dadosProtetor = $this->onboardingService->obterDadosPreenchidosProtetor($usuarioId);
        $motivoRecusa = $_SESSION['motivo_recusa_protetor_' . ($dadosProtetor['protetor_id'] ?? 0)] ?? 'Documentação incompleta ou inconsistente.';

        $this->view('onboarding/aguardando_aprovacao', [
            'titulo'        => 'Status da Solicitação',
            'validado'      => $validado,
            'recusado'      => $recusado,
            'motivoRecusa'  => $motivoRecusa,
            'tipoDocumento' => $dadosProtetor['tipo_documento'] ?? 'cpf'
        ]);
    }
}