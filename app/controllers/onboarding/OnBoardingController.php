<?php

namespace app\controllers\onboarding;

use app\core\Controller;
use app\services\OnboardingService;
use app\repositories\RegiaoRepository;
use app\repositories\EspecieRepository;
use app\repositories\UsuarioRepository;
use app\services\ValidationService;
use Exception;

/**
 * Fluxo de onboarding: escolha e cadastro do primeiro perfil (Adotante ou
 * Protetor/ONG), upgrade para um perfil adicional (RF 20 e seu inverso) e a
 * tela de espera de aprovação do cadastro de Protetor/ONG.
 */
class OnboardingController extends Controller
{
    private OnboardingService $onboardingService;
    private RegiaoRepository $regiaoRepo;
    private EspecieRepository $especieRepo;
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->onboardingService = new OnboardingService();
        $this->regiaoRepo = new RegiaoRepository();
        $this->especieRepo = new EspecieRepository();
        $this->usuarioRepo = new UsuarioRepository();

        $this->autenticacaoRequired();
        $this->verificarSeJaPossuiPerfil();
    }

    /** Exibe a tela de seleção do tipo de perfil a cadastrar. Usado pela rota GET /onboarding. */
    public function index()
    {
        $this->view('onboarding/selecionar_perfil', [
            'titulo'    => 'Selecionar Perfil',
            'descricao' => 'Escolha o tipo de perfil que deseja criar.'
        ]);
    }

    /** Exibe o formulário de cadastro de Adotante. Usado pela rota GET /onboarding/adotante. */
    public function adotante(): void
    {
        $regioes = $this->regiaoRepo->buscarTodas();
        $especies = $this->especieRepo->buscarTodas();

        $this->view('onboarding/adotante_onboarding', [
            'titulo'   => 'Cadastro de Adotante',
            'regioes'  => $regioes,
            'especies' => $especies
        ]);
    }

    /**
     * Persiste o cadastro de Adotante (fluxo original) ou, no caso de RF 20 inverso, o
     * perfil adicional de Adotante de um usuário já Protetor/ONG. Usado pela rota POST /onboarding/salvar-adotante.
     */
    public function salvarAdotante(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                // RF 20 inverso: quem já é Protetor/ONG está pedindo um perfil ADICIONAL de
                // Adotante. Diferente do outro sentido, não existe aprovação aqui (Adotante
                // não passa por validação), mas o onboarding continua sobrescrevendo dados
                // pessoais compartilhados em USUARIO — precisa restaurar do mesmo jeito.
                $tipoAnterior = $_SESSION['tipo_perfil'] ?? 'usuario';
                $ehUpgradeDeProtetorOuOng = in_array($tipoAnterior, ['protetor', 'ong'], true);
                $usuarioOriginal = $ehUpgradeDeProtetorOuOng
                    ? $this->usuarioRepo->buscarPorId((int)$usuarioId)
                    : null;

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                $this->onboardingService->processarAdotante($dadosLimpos, $_FILES, (int)$usuarioId);

                if ($ehUpgradeDeProtetorOuOng && $usuarioOriginal) {
                    $this->onboardingService->restaurarPerfilAtivoOriginal((int)$usuarioId, $usuarioOriginal, $tipoAnterior);
                }

                $this->json(200, [
                    'status'       => 'sucesso',
                    'mensagem'     => $ehUpgradeDeProtetorOuOng
                        ? 'Perfil de Adotante criado com sucesso! Use "Alternar Perfil" para acessá-lo.'
                        : 'Perfil de adotante criado com sucesso!',
                    // TODO: trocar para '/feed' quando o Feed voltar a ser implementado.
                    'redirect_url' => URL_BASE . '/perfil'
                ]);
            } catch (Exception $e) {
                $this->json(200, [
                    'status'   => 'erro',
                    'mensagem' => $e->getMessage()
                ]);
            }
        }
    }

    /** Exibe o formulário de cadastro de ONG (protetor com CNPJ). Usado pela rota GET /onboarding/ong. */
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

    /** Exibe o formulário de cadastro de Protetor (pessoa física, CPF). Usado pela rota GET /onboarding/protetor. */
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

    /**
     * Envia a solicitação de cadastro de Protetor/ONG para análise do administrador
     * (fluxo original), ou, no caso de RF 20, o upgrade de um Adotante já validado. Usado
     * pela rota POST /onboarding/salvar-protetor.
     */
    public function salvarProtetor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                if (!$usuarioId) {
                    throw new Exception("Sessão expirada. Faça login novamente.");
                }

                // RF 20: quem já é Adotante (perfil ativo antes desta requisição) está pedindo
                // um perfil ADICIONAL de Protetor/ONG, não se cadastrando do zero. Precisa
                // continuar navegando como Adotante enquanto a solicitação está pendente.
                $ehUpgradeDeAdotante = ($_SESSION['tipo_perfil'] ?? 'usuario') === 'adotante';
                $usuarioOriginal = $ehUpgradeDeAdotante
                    ? $this->usuarioRepo->buscarPorId((int)$usuarioId)
                    : null;

                $dadosLimpos = ValidationService::sanitizarArray($_POST);
                $this->onboardingService->processarOng($dadosLimpos, $_FILES, (int)$usuarioId);

                if ($ehUpgradeDeAdotante && $usuarioOriginal) {
                    $this->onboardingService->restaurarPerfilAtivoOriginal((int)$usuarioId, $usuarioOriginal, 'adotante');
                }

                $this->json(200, [
                    'status'       => 'sucesso',
                    'mensagem'     => $ehUpgradeDeAdotante
                        ? 'Solicitação para se tornar Protetor/ONG enviada! Acompanhe o status no seu perfil.'
                        : 'Solicitação enviada para análise com sucesso!',
                    'redirect_url' => URL_BASE . ($ehUpgradeDeAdotante ? '/perfil' : '/aguardando-aprovacao')
                ]);
            } catch (Exception $e) {
                $this->json(200, [
                    'status'   => 'erro',
                    'mensagem' => $e->getMessage()
                ]);
            }
        }
    }

    /** Endpoint AJAX que lista as espécies ativas em JSON, usado nos combobox do formulário de onboarding. Usado pela rota GET /onboarding/especies-ativas. */
    public function especiesAtivas(): void
    {
        try {
            $especies = $this->especieRepo->buscarTodas();
            $dados = array_map(function($esp) {
                return [
                    'especie_id' => is_array($esp) ? $esp['especie_id'] : $esp->getEspecieId(),
                    'nome'       => is_array($esp) ? $esp['nome'] : $esp->getNome()
                ];
            }, $especies);

            $this->json(200, [
                'status' => 'sucesso',
                'dados'  => $dados
            ]);
        } catch (Exception $e) {
            $this->json(200, [
                'status'   => 'erro',
                'mensagem' => $e->getMessage()
            ]);
        }
    }

    /** Exibe o status da solicitação de cadastro de Protetor/ONG (pendente/aprovada/recusada). Usado pela rota GET /aguardando-aprovacao. */
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
            // TODO: trocar para '/feed' quando o Feed voltar a ser implementado.
            $this->redirect('/perfil');
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

    /**
     * Guarda de acesso do construtor: bloqueia rotas de onboarding para quem já concluiu o
     * fluxo, e roteia quem está em RF 20 (upgrade de perfil) ou com solicitação de
     * Protetor/ONG pendente/recusada para a tela correta.
     */
    private function verificarSeJaPossuiPerfil(): void
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            $this->redirect('/login');
        }

        $uriAtual = $this->getUriLimpa();

        $rotasSempreLivres = [
            '/onboarding/especies-ativas',
            '/logout'
        ];

        if (in_array($uriAtual, $rotasSempreLivres, true)) {
            return;
        }

        $possuiPerfil = $this->onboardingService->usuarioJaPossuiPerfil((int)$usuarioId);

        if (!$possuiPerfil) {
            return;
        }

        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;
        $recusado = $_SESSION['recusado'] ?? false;

        // Protetor/ONG com solicitação pendente ou recusada: ainda pode reenviar a
        // documentação através de /onboarding/protetor ou /onboarding/ong.
        if (in_array($tipoPerfil, ['protetor', 'ong'], true) && (!$validado || $recusado)) {
            $rotasReenvio = [
                '/aguardando-aprovacao',
                '/onboarding/aguardando-aprovacao',
                '/onboarding/protetor',
                '/onboarding/ong',
                '/onboarding/salvar-protetor'
            ];

            if (in_array($uriAtual, $rotasReenvio, true)) {
                return;
            }

            $this->redirect('/aguardando-aprovacao');
            return;
        }

        // RF 20: Adotante pedindo upgrade para Protetor/ONG. Reaproveita as mesmas rotas/views
        // do onboarding original (seleção de perfil, formulários de protetor/ong e o
        // submit) — a diferença fica só na resposta do controller (ver salvarProtetor()),
        // que mantém a pessoa como Adotante enquanto a solicitação está pendente.
        if ($tipoPerfil === 'adotante') {
            $rotasUpgradeProtetor = [
                '/onboarding',
                '/onboarding/ong',
                '/onboarding/protetor',
                '/onboarding/salvar-protetor'
            ];

            if (in_array($uriAtual, $rotasUpgradeProtetor, true)) {
                $statusConta = $_SESSION['status_conta'] ?? 'pendente';
                if ($statusConta !== 'ativo') {
                    $this->redirecionarComMensagem('erro', 'Sua conta ainda não está verificada para solicitar o perfil de Protetor/ONG.', '/perfil');
                    return;
                }

                $solicitacaoAtual = $this->onboardingService->obterSolicitacaoProtetorAtual((int)$usuarioId);

                // Pendente: já existe solicitação aguardando análise, não deixa abrir o
                // formulário de novo (evitaria duplicar/perder o comprovante já enviado).
                if ($solicitacaoAtual && empty($solicitacaoAtual['deletado_em']) && empty($solicitacaoAtual['validado'])) {
                    $this->redirecionarComMensagem('aviso', 'Você já tem uma solicitação de Protetor/ONG em análise.', '/perfil');
                    return;
                }

                // Aprovada: não faz sentido reabrir o formulário, já pode trocar de perfil.
                if ($solicitacaoAtual && !empty($solicitacaoAtual['validado'])) {
                    $this->redirecionarComMensagem('sucesso', 'Sua solicitação de Protetor/ONG já foi aprovada! Use "Alternar Perfil" para acessá-lo.', '/perfil');
                    return;
                }

                // Sem solicitação ainda, ou recusada (reenvio): libera o formulário.
                return;
            }
        }

        // RF 20 (inverso): Protetor/ONG já validado pedindo também um perfil de Adotante.
        // Sem aprovação envolvida (Adotante não passa por validação), então basta checar se a
        // pessoa já tem esse perfil. Reaproveita a mesma view/formulário do onboarding original.
        if (in_array($tipoPerfil, ['protetor', 'ong'], true)) {
            $rotasUpgradeAdotante = [
                '/onboarding',
                '/onboarding/adotante',
                '/onboarding/salvar-adotante'
            ];

            if (in_array($uriAtual, $rotasUpgradeAdotante, true)) {
                $statusConta = $_SESSION['status_conta'] ?? 'pendente';
                if ($statusConta !== 'ativo') {
                    $this->redirecionarComMensagem('erro', 'Sua conta ainda não está verificada para solicitar o perfil de Adotante.', '/perfil');
                    return;
                }

                if ($this->onboardingService->possuiPerfilAdotante((int)$usuarioId)) {
                    $this->redirecionarComMensagem('aviso', 'Você já tem um perfil de Adotante.', '/perfil');
                    return;
                }

                return;
            }
        }

        // Onboarding concluído (adotante, ou protetor/ong já validado): as rotas de
        // onboarding (inclusive /onboarding, /onboarding/adotante, /onboarding/protetor
        // e /onboarding/ong) ficam bloqueadas e o usuário é enviado de volta ao perfil.
        // TODO: trocar para '/feed' quando o Feed voltar a ser implementado.
        if ($uriAtual !== '/perfil') {
            $this->redirect('/perfil');
        }
    }
}
