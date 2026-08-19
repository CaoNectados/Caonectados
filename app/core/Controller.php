<?php

namespace app\core;

use RuntimeException;
use app\repositories\UsuarioRepository;
class Controller
{
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

    protected function autenticacaoRequired(array $perfisPermitidos = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            $this->redirecionarComMensagem('aviso', 'Você precisa estar logado para acessar esta página.', '/login');
        }

        $tipoUsuario = $_SESSION['tipo_perfil'] ?? 'usuario';
        $validado = $_SESSION['validado'] ?? false;
        $uriAtual = $this->getUriLimpa();

        if (!empty($perfisPermitidos)) {
            if (!in_array($tipoUsuario, $perfisPermitidos, true)) {
                $this->redirecionarComMensagem('erro', 'Você não tem permissão para acessar esta área.', '/feed');
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
                '/raca/json',
                '/admin/raca/json',
                '/logout'
            ];

            if (!in_array($uriAtual, $rotasLivres, true)) {
                $this->redirect('/aguardando-aprovacao');
            }
        }
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


    public function exigirLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']['id'])) {
            $this->redirect('/login');
        }

        // Validação em tempo de execução: expulsa usuário se desativado após login
        $repo = new UsuarioRepository();
        $usuario = $repo->buscarPorId((int)$_SESSION['usuario']['id']);

        if (!$usuario || (int)$usuario['ativo'] !== 1) {
            unset($_SESSION['usuario'], $_SESSION['perfis'], $_SESSION['perfil_ativo']);
            session_destroy();
            session_start();
            $_SESSION['flash_error'] = 'Sua conta foi desativada durante a sessão.';
            $this->redirect('/login');
        }
    }
}