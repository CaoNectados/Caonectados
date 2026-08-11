<?php

namespace app\core;

use RuntimeException;

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

    /**
     * Exige autenticação e, opcionalmente, tipos específicos de perfil.
     * 
     * @param array $perfisPermitidos Ex: ['ong', 'protetor'] ou [] para qualquer usuário logado
     */
    protected function autenticacaoRequired(array $perfisPermitidos = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            $this->redirecionarComMensagem('aviso', 'Você precisa estar logado para acessar esta página.', '/login');
        }

        $tipoUsuario = $_SESSION['tipo_perfil'] ?? 'usuario';

        if (!empty($perfisPermitidos)) {
            if (!in_array($tipoUsuario, $perfisPermitidos, true)) {
                $this->redirecionarComMensagem('erro', 'Você não tem permissão para acessar esta área.', '/feed');
            }
        }

        // NOVA TRAVA: Se for ONG/Protetor e não estiver validado, prende na tela de aguardando aprovação
        if (in_array($tipoUsuario, ['ong', 'protetor'])) {
            $validado = $_SESSION['validado'] ?? false;
            
            $uriAtual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // Permite acessar apenas a página de aguardando aprovação e logout
            $rotasLivres = ['/aguardando-aprovacao', '/onboarding/aguardando-aprovacao', '/logout'];
            $isLivre = false;

            foreach ($rotasLivres as $rota) {
                if (substr($uriAtual, -strlen($rota)) === $rota) {
                    $isLivre = true;
                    break;
                }
            }

            if (!$validado && !$isLivre) {
                $this->redirect('/aguardando-aprovacao');
            }
        }
    }
    /**
     * Redireciona para uma rota salvando o tipo de feedback e a mensagem na sessão.
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
}