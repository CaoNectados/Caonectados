<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\services\UsuarioAdminService;
use Exception;

class UsuarioController extends Controller
{
    private UsuarioAdminService $adminService;

    public function __construct()
    {
        $this->autenticacaoRequired(['administrador']);
        $this->adminService = new UsuarioAdminService();
    }

    // Usado por: rota GET /admin/gerenciar-usuarios
    public function index(): void
    {
        $filtros = [
            'busca'  => $_GET['busca'] ?? '',
            'status' => $_GET['status'] ?? '',
            'perfil' => $_GET['perfil'] ?? '',
            'pagina' => (int)($_GET['pagina'] ?? 1)
        ];

        $dados = $this->adminService->listarUsuarios($filtros);

        $this->view('admin/gerenciar_usuarios', array_merge($dados, [
            'titulo'  => 'Gerenciar Usuários',
            'filtros' => $filtros
        ]));
    }

    // Usado por: rota GET /admin/usuarios/detalhes
    public function detalhes(): void
    {
        try {
            $usuarioId = (int)($_GET['id'] ?? 0);
            if ($usuarioId <= 0) {
                throw new Exception("ID de usuário inválido.");
            }

            $detalhes = $this->adminService->obterDetalhesUsuario($usuarioId);
            $this->json(200, ['status' => 'sucesso', 'dados' => $detalhes]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    // Usado por: rota POST /admin/usuarios/alterar-status
    public function alterarStatusUsuario(): void
    {
        try {
            $usuarioId = (int)($_POST['usuario_id'] ?? 0);
            $acao = trim($_POST['acao'] ?? '');
            $adminLogadoId = (int)$_SESSION['usuario_id'];

            if ($usuarioId <= 0 || !in_array($acao, ['ativar', 'desativar'], true)) {
                throw new Exception("Parâmetros inválidos.");
            }

            $mensagem = $this->adminService->alterarStatusUsuario($usuarioId, $acao, $adminLogadoId);
            $this->json(200, ['status' => 'sucesso', 'mensagem' => $mensagem]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    // Usado por: rota POST /admin/usuarios/alterar-status-perfil
    public function alterarStatusPerfil(): void
    {
        try {
            $usuarioId = (int)($_POST['usuario_id'] ?? 0);
            $tipoPerfil = trim($_POST['tipo_perfil'] ?? '');
            $acao = trim($_POST['acao'] ?? '');
            $adminLogadoId = (int)$_SESSION['usuario_id'];

            if ($usuarioId <= 0 || empty($tipoPerfil) || !in_array($acao, ['ativar', 'desativar'], true)) {
                throw new Exception("Parâmetros inválidos.");
            }

            $mensagem = $this->adminService->alterarStatusPerfil($usuarioId, $tipoPerfil, $acao, $adminLogadoId);
            $this->json(200, ['status' => 'sucesso', 'mensagem' => $mensagem]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
}