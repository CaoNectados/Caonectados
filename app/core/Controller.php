<?php

namespace app\core;

use RuntimeException;
use app\repositories\UsuarioRepository;
use app\repositories\ProtetorRepository;
class Controller
{
    protected function validarCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->json(403, ['status' => 'erro', 'mensagem' => 'Sessão do formulário inválida. Recarregue a página.']);
        }
    }

    protected function exigirContaNaoBloqueada(): void
    {
        $this->autenticacaoRequired(['adotante', 'protetor', 'ong']);
    }
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

    public function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function redirect(string $url): void
    {
        header('Location: ' . URL_BASE . $url);
        exit();
    }

    protected function getUriLimpa(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $basePath = parse_url(URL_BASE, PHP_URL_PATH) ?: '';

        if ($basePath === '/') {
            $basePath = '';
        }

        if ($basePath !== '' && ($uri === $basePath || strpos($uri, $basePath . '/') === 0)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = preg_replace('#/+#', '/', $uri);

        if (empty($uri) || $uri === '/') {
            return '/';
        }

        return '/' . ltrim(rtrim($uri, '/'), '/');
    }

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

        if (!empty($perfisPermitidos)) {
            if (!in_array($tipoUsuario, $perfisPermitidos, true)) {
                $this->redirecionarComMensagem('erro', 'Você não tem permissão para acessar esta área.', '/perfil');
            }
        }

       // Se for ONG/Protetor e não estiver validado (0)
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

    // Usado por: redirecionarSeAutenticado()
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


    // Usado por: autenticacaoRequired() — mantém a sessão alinhada com o banco a cada
    // requisição autenticada. A sessão só era preenchida no login (AuthService::iniciarSessao)
    // e nunca era revalidada depois disso, então uma conta/perfil desativado pelo admin (ou
    // editado direto no banco) só tinha efeito quando o usuário deslogava e logava de novo.
    private function sincronizarSessaoComBanco(int $usuarioId): void
    {
        $usuarioRepo = new UsuarioRepository();
        $usuario = $usuarioRepo->buscarPorId($usuarioId);

        $statusInvalido = ['inativo', 'bloqueado', 'rejeitado', 'bloqueada', 'desativado'];
        $statusConta = strtolower((string)($usuario['status_conta'] ?? ''));

        if (!$usuario || in_array($statusConta, $statusInvalido, true)) {
            session_unset();
            session_destroy();
            $this->redirecionarComMensagem('erro', 'Sua conta foi desativada. Entre em contato com o suporte se acredita que isso é um engano.', '/login');
            return;
        }

        $tipoAtual = strtolower((string)($usuario['tipo_atual'] ?? 'usuario'));
        $perfisAtivos = array_values(array_filter(array_map('trim', explode(',', strtolower((string)($usuario['perfis_ativos'] ?? ''))))));

        if (\app\services\PerfilPolicy::ehAdministrador($tipoAtual, $usuario['perfis_ativos'] ?? '')) {
            $tipoAtual = 'administrador';
            $perfisAtivos = ['administrador'];
        }
        $_SESSION['usuario']['is_admin'] = $tipoAtual === 'administrador';
        $_SESSION['perfis'] = array_map(static fn($tipo) => ['id' => $usuarioId, 'tipo' => $tipo], $perfisAtivos);
        $_SESSION['tipo_perfil']   = $tipoAtual;
        $_SESSION['perfis_ativos'] = $perfisAtivos;
        $_SESSION['status_conta']  = $statusConta;

        // Mantém perfil_ativo.tipo alinhado com tipo_atual do banco a cada requisição.
        // perfil_ativo só era escrito no login (AuthService::iniciarSessao) e ao trocar de
        // perfil (PerfilController::alternar) — quem completava o onboarding pela primeira
        // vez tinha tipo_perfil atualizado corretamente, mas perfil_ativo.tipo continuava
        // com o valor 'usuario' do login, e perfil.php/editar.php dão prioridade a
        // perfil_ativo.tipo. Resultado: o perfil aparecia como "incompleto" mesmo depois de
        // concluído o cadastro de adotante/protetor/ong.
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
}
