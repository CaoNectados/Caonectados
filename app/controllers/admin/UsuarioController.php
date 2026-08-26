<?php

namespace app\controllers\admin;

use app\core\Controller;
use app\services\UsuarioAdminService;
use Exception;

/**
 * Gestão administrativa de usuários: listagem/filtro, detalhes, ativação/desativação da
 * conta ou de um perfil específico, e classificação manual de inadimplência (RN 15).
 */
class UsuarioController extends Controller
{
    private UsuarioAdminService $adminService;

    public function __construct()
    {
        $this->autenticacaoRequired(['administrador']);
        $this->adminService = new UsuarioAdminService();
    }

    /** Lista os usuários da plataforma, filtráveis por busca/status/perfil. Usado pela rota GET /admin/gerenciar-usuarios. */
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

    /** Endpoint AJAX que retorna os detalhes de um usuário em JSON. Usado pela rota GET /admin/usuarios/detalhes. */
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

    /** Ativa ou desativa a conta de um usuário. Usado pela rota POST /admin/usuarios/alterar-status. */
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

    /** Ativa ou desativa um perfil específico (adotante/protetor/ong) de um usuário multi-perfil. Usado pela rota POST /admin/usuarios/alterar-status-perfil. */
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

    /**
     * Cria uma nova conta de Administrador. Só é alcançável por quem já está logado como
     * administrador (autenticacaoRequired() do construtor), o que garante a regra de que um
     * admin só pode ser criado por outro admin. Usado pela rota POST /admin/usuarios/criar-administrador.
     */
    public function criarAdministrador(): void
    {
        try {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = (string) ($_POST['senha'] ?? '');
            $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

            $mensagem = $this->adminService->criarAdministrador($nome, $email, $senha, $confirmarSenha);
            $this->json(200, ['status' => 'sucesso', 'mensagem' => $mensagem]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Classifica manualmente um protetor como inadimplente (RN 15), aplicando a sanção padrão. Usado pela rota POST /admin/usuarios/classificar-inadimplente. */
    public function classificarInadimplente(): void
    {
        try {
            $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
            $motivo = (string) ($_POST['motivo'] ?? '');
            $adminLogadoId = (int) $_SESSION['usuario_id'];

            if ($usuarioId <= 0) {
                throw new Exception("Parâmetros inválidos.");
            }

            $mensagem = $this->adminService->classificarProtetorInadimplente($usuarioId, $motivo, $adminLogadoId);
            $this->json(200, ['status' => 'sucesso', 'mensagem' => $mensagem]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
}
