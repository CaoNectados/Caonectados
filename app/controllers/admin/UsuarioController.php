<?php

namespace app\controllers\admin;

use app\services\UsuarioAdminService;
use Exception;

class UsuarioController extends AdminBaseController
{
    private UsuarioAdminService $adminService;

    public function __construct()
    {
        parent::__construct();
        $this->validarCsrf();
        $this->adminService = new UsuarioAdminService();
    }

    public function classificacao(): void
    {
        $id = filter_var($_POST['usuario_id'] ?? $_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $this->redirecionarComMensagem('erro', 'Perfil inválido.', '/admin/gerenciar-usuarios');
        }
        $service = new \app\services\ClassificacaoProtetorService();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $valor=$_POST['inadimplente'] ?? '';
                $motivo = $_POST['motivo'] ?? '';
                if (!in_array($valor,['0','1'],true) || !is_string($motivo)) throw new \DomainException('Classificação ou justificativa inválida.');
                $service->classificar($id,(int)$_SESSION['usuario_id'],$valor === '1',$motivo);
                $this->redirecionarComMensagem('sucesso','Classificação registrada no histórico.','/admin/protetores/classificacao?id='.$id);
            } catch (\Throwable $e) {
                $this->redirecionarComMensagem('erro',$e instanceof \DomainException ? $e->getMessage() : 'Não foi possível registrar a classificação.','/admin/protetores/classificacao?id='.$id);
            }
        }
        try {
            $pagina = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT);
            if ($pagina === false) throw new \DomainException('Página inválida.');
            $dados = $service->consultarParaAdmin($id, (int) $_SESSION['usuario_id'], $pagina);
            $this->view('admin/classificacao_protetor', $dados + ['titulo'=>'Classificação do protetor','usuarioId'=>$id]);
        } catch (\Throwable $e) {
            $this->redirecionarComMensagem('erro', $e instanceof \DomainException ? $e->getMessage() : 'Não foi possível consultar a classificação.', '/admin/gerenciar-usuarios');
        }
    }

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
