<?php

namespace app\core;

use RuntimeException;
use app\repositories\UsuarioRepository;
use app\repositories\ProtetorRepository;

class Controller
{
    /**
     * Renderiza uma view em app/views/, extraindo $data como variáveis locais.
     * Usado por todo controller do sistema para produzir HTML.
     */
    public function view(string $view, ?array $data = null): void
    {
        if ($data) {
            extract($data);
        }

        $path = __DIR__ . "/../views/$view.php";

        if (file_exists($path)) {
            require_once $path;
        } else {
            throw new RuntimeException("A view solicitada não foi encontrada: {$view}");
        }
    }

    /**
     * Responde a requisição atual como JSON e encerra a execução.
     * Usado pelas rotas AJAX de todo o sistema.
     */
    public function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireciona para uma rota interna (prefixada com URL_BASE) e encerra a execução.
     * Usado por todo controller do sistema.
     */
    public function redirect(string $url): void
    {
        header('Location: ' . URL_BASE . $url);
        exit();
    }

    /**
     * Normaliza a URI da requisição atual (remove o base path do document root e barras
     * duplicadas/finais). Usado por autenticacaoRequired() para comparar contra as listas de
     * rotas livres.
     */
    protected function getUriLimpa(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        if ($basePath === '/') {
            $basePath = '';
        }

        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = preg_replace('#/+#', '/', $uri);

        if (empty($uri) || $uri === '/') {
            return '/';
        }

        return '/' . ltrim(rtrim($uri, '/'), '/');
    }

    /**
     * Guarda de sessão principal: exige login, sincroniza a sessão com o banco, força a
     * conclusão do onboarding, restringe por perfil (RBAC) e bloqueia ONG/Protetor não
     * validado fora das rotas livres. Chamada no construtor de todo controller autenticado.
     */
    protected function autenticacaoRequired(array $perfisPermitidos = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            $this->redirecionarComMensagem('aviso', 'Você precisa estar logado para acessar esta página.', '/login');
        }

        $this->sincronizarSessaoComBanco((int)$_SESSION['usuario_id']);

        $tipoUsuario = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;
        $uriAtual = $this->getUriLimpa();

        // Usuário logado que ainda não concluiu o onboarding (tipo_atual continua 'usuario'):
        // é forçado a completar o fluxo antes de acessar qualquer outra página protegida.
        // As rotas do próprio módulo de onboarding se auto-protegem em OnBoardingController.
        if ($tipoUsuario === 'usuario') {
            $rotasOnboardingLivres = [
                '/onboarding',
                '/onboarding/adotante',
                '/onboarding/ong',
                '/onboarding/protetor',
                '/onboarding/salvar-adotante',
                '/onboarding/salvar-protetor',
                '/onboarding/especies-ativas',
                '/aguardando-aprovacao',
                '/onboarding/aguardando-aprovacao',
                '/raca/json',
                '/admin/raca/json',
                '/logout'
            ];

            if (!in_array($uriAtual, $rotasOnboardingLivres, true)) {
                $this->redirect('/onboarding');
            }
        }

        if (!empty($perfisPermitidos) && !in_array($tipoUsuario, $perfisPermitidos, true)) {
            $this->redirecionarComMensagem('erro', 'Você não tem permissão para acessar esta área.', '/perfil');
        }

        if (in_array($tipoUsuario, ['ong', 'protetor'], true) && ($validado === false || $validado === 0 || $validado === '0')) {
            $rotasLivres = [
                '/',
                '/home',
                '/aguardando-aprovacao',
                '/onboarding/aguardando-aprovacao',
                '/onboarding',
                '/onboarding/ong',
                '/onboarding/protetor',
                '/onboarding/salvar-protetor',
                '/onboarding/especies-ativas',
                '/perfil',
                '/perfil/trocar',
                '/perfil/excluir',
                '/raca/json',
                '/admin/raca/json',
                '/logout'
            ];

            if (!in_array($uriAtual, $rotasLivres, true)) {
                $this->redirect('/aguardando-aprovacao');
            }
        }
    }

    /**
     * Guarda de sessão inversa: usada pelas telas públicas de autenticação (login, cadastro,
     * esqueci/redefinir senha) para impedir que um usuário já logado navegue de volta para
     * elas. Redireciona para o painel correspondente ao estado atual da conta.
     */
    // Usado por: AuthController (login, cadastro, esqueci-senha, redefinir-senha)
    protected function redirecionarSeAutenticado(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            return;
        }

        $this->sincronizarSessaoComBanco((int)$_SESSION['usuario_id']);
        $this->redirect($this->resolverDestinoPainel());
    }

    /**
     * Decide pra qual painel redirecionar um usuário já autenticado, a partir do tipo de
     * perfil e do status de validação. Usado por redirecionarSeAutenticado().
     */
    private function resolverDestinoPainel(): string
    {
        $tipoUsuario = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;

        if ($tipoUsuario === 'administrador') {
            return '/admin/dashboard';
        }

        if ($tipoUsuario === 'usuario') {
            return '/onboarding';
        }

        if (in_array($tipoUsuario, ['ong', 'protetor'], true) && ($validado === false || $validado === 0 || $validado === '0')) {
            return '/aguardando-aprovacao';
        }

        return '/perfil';
    }

    /**
     * Grava um feedback flash na sessão (lido pelo modal global em footer.php) e redireciona.
     * Usado por todo controller do sistema para reportar sucesso/erro após uma ação.
     */
    protected function redirecionarComMensagem(string $tipo, string $mensagem, string $rota, ?string $erroDetalhado = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true && !empty($erroDetalhado)) {
            $mensagem .= " <br><br><small class='text-left block text-xs bg-red-100 p-2 rounded border border-red-300 font-mono text-red-800 break-words'><strong>[DEV ERROR]:</strong> " . htmlspecialchars($erroDetalhado) . "</small>";
        }

        $_SESSION['feedback'] = [
            'tipo'     => $tipo,
            'mensagem' => $mensagem
        ];

        $this->redirect($rota);
    }

    /**
     * Realinha a sessão com o banco a cada requisição autenticada (tipo de perfil, perfis
     * ativos, status da conta e validação de ONG/Protetor) — sem isso, uma conta/perfil
     * alterado pelo admin só surtiria efeito no próximo login. Usado por autenticacaoRequired()
     * e redirecionarSeAutenticado().
     */
    private function sincronizarSessaoComBanco(int $usuarioId): void
    {
        $usuarioRepo = new UsuarioRepository();
        $usuario = $usuarioRepo->buscarPorId($usuarioId);

        // 'bloqueado' fica fora desta lista de propósito (RF 16/19, RN 14/15): é uma sanção de
        // moderação que restringe AÇÕES específicas (ver exigirContaNaoBloqueada()), não um
        // logout forçado — o usuário precisa continuar logado pra conseguir contestar (RF 17).
        // 'inativo'/'rejeitado' são desativação administrativa completa e derrubam a sessão.
        $statusInvalido = ['inativo', 'rejeitado', 'bloqueada', 'desativado'];
        $statusConta = strtolower((string)($usuario['status_conta'] ?? ''));

        if (!$usuario || in_array($statusConta, $statusInvalido, true)) {
            session_unset();
            session_destroy();
            $this->redirecionarComMensagem('erro', 'Sua conta foi desativada. Entre em contato com o suporte se acredita que isso é um engano.', '/login');
            return;
        }

        $tipoAtual = strtolower((string)($usuario['tipo_atual'] ?? 'usuario'));
        $perfisAtivos = array_values(array_filter(array_map('trim', explode(',', strtolower((string)($usuario['perfis_ativos'] ?? ''))))));

        $_SESSION['tipo_perfil']   = $tipoAtual;
        $_SESSION['perfis_ativos'] = $perfisAtivos;
        $_SESSION['status_conta']  = $statusConta;

        // perfil_ativo.tipo precisa ficar alinhado com tipo_atual aqui também, não só no login
        // e na troca de perfil — senão quem completa o onboarding continua com perfil_ativo
        // apontando pro 'usuario' antigo, e telas como perfil.php/editar.php (que priorizam
        // perfil_ativo.tipo) mostram o cadastro como incompleto mesmo depois de concluído.
        $_SESSION['perfil_ativo'] = [
            'id'   => $usuarioId,
            'tipo' => $tipoAtual
        ];

        if (in_array($tipoAtual, ['ong', 'protetor'], true)) {
            $protetorRepo = new ProtetorRepository();
            $protetor = $protetorRepo->buscarPorUsuarioId($usuarioId);
            $_SESSION['validado'] = $protetor ? (bool)$protetor['validado'] : false;
        } else {
            $_SESSION['validado'] = true;
        }
    }

    /**
     * RF 16 / RN 14 / RN 15: interceptação de ações críticas pra contas com status_conta =
     * 'bloqueado' (aplicado via DenunciaService::aprovar()/aplicarSancaoDireta()). Chamada no
     * início de toda ação restrita — SolicitacaoAdocaoController (criar/colocarEmAnalise/
     * recusar/aprovar/devolver) e AnimalController (store/update/status/reativar/destroy).
     */
    protected function exigirContaNaoBloqueada(): void
    {
        if (($_SESSION['status_conta'] ?? '') === 'bloqueado') {
            throw new \Exception('Solicitação negada! Seu perfil está bloqueado. Entre em contato com a administração para mais informações.');
        }
    }
}
