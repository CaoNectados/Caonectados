<?php

namespace app\core;

class Controller
{
    public function view(string $view, ?array $data = null)
    {
        if ($data) {
            extract($data);
        }

        $path = __DIR__ . "/../views/$view.php";

        if (file_exists($path)) {
            require_once $path;
        } else {
            print 'A view solicitada não foi encontrada: ' . $view;
        }
    }

    public function redirect(string $url)
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

        // Verifica se está logado
        if (empty($_SESSION['usuario_id'])) {
            $this->redirecionarComMensagem('aviso', 'Você precisa estar logado para acessar esta página.', '/login');
        }

        // Se informou perfis específicos, verifica se o usuário tem a permissão
        if (!empty($perfisPermitidos)) {
            $tipoUsuario = $_SESSION['tipo_conta'] ?? 'usuario';

            if (!in_array($tipoUsuario, $perfisPermitidos)) {
                $this->redirecionarComMensagem('erro', 'Você não tem permissão para acessar esta área.', '/home');
            }
        }
    }

    /**
     * Redireciona para uma rota salvando o tipo de feedback e a mensagem na sessão.
     *
     * @param string $tipo          'erro' | 'aviso' | 'sucesso' | 'informativo'
     * @param string $mensagem      Texto a ser exibido no modal
     * @param string $rota          Rota para onde o usuário será redirecionado
     * @param string|null $erroDetalhado Mensagem técnica do erro / Exception (opcional)
     */
    protected function redirecionarComMensagem(string $tipo, string $mensagem, string $rota, ?string $erroDetalhado = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Se o ambiente for de desenvolvimento (DEV_ENVIRONMENT) e houver erro detalhado
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