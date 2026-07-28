<?php

namespace app\controllers;

use app\core\Controller;
use app\repositories\UsuarioRepository;
use app\services\ValidationService;
use app\database\ConnectionFactory;
use Exception;

class AdminUsuarioController extends Controller
{
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        // 1. Bloqueio Imediato: Só administrador entra aqui!
        if (!isset($_SESSION['tipo_conta']) || $_SESSION['tipo_conta'] !== 'administrador') {
            $this->redirecionarComMensagem('erro', 'Acesso negado. Área restrita a administradores.', '/home');
            exit;
        }

        $this->usuarioRepo = new UsuarioRepository();
    }

    // [R] Read: Lista todos os usuários
    public function index()
    {
        $pdo = ConnectionFactory::getConnection();
        
        try {
            // Busca todos os usuários, exceto os que foram deletados (soft delete)
            $usuarios = $this->usuarioRepo->buscarTodos($pdo);

            $this->view('admin/listar_usuarios', [
                'titulo' => 'Gerenciar Usuários',
                'usuarios' => $usuarios
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar lista de usuários.', '/admin/dashboard', $e->getMessage());
        }
    }

    // View: Exibe o formulário de edição de um usuário específico
    public function editar($params)
    {
        $usuarioId = $params['id'] ?? null;

        if (!$usuarioId) {
            $this->redirecionarComMensagem('erro', 'Usuário não especificado.', '/admin/gerenciar-usuarios');
        }

        $pdo = ConnectionFactory::getConnection();
        
        try {
            // Busca o usuário pelo ID
            $usuario = $this->usuarioRepo->buscarPorId($usuarioId, $pdo);

            if (!$usuario) {
                $this->redirecionarComMensagem('erro', 'Usuário não encontrado.', '/admin/gerenciar-usuarios');
            }

            $this->view('admin/editar_usuario', [
                'titulo' => 'Editar Usuário: ' . $usuario->getNome(),
                'usuario' => $usuario
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar dados do usuário.', '/admin/gerenciar-usuarios', $e->getMessage());
        }
    }

    // [U] Update: Processa os dados enviados pelo formulário de edição
    public function atualizar($params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/gerenciar-usuarios');
        }

        $usuarioId = $params['id'] ?? null;
        $pdo = ConnectionFactory::getConnection();

        try {
            // 1. Sanitizar Dados
            $dadosLimpos = ValidationService::sanitizarArray($_POST);

            // 2. Validações Básicas
            ValidationService::validarCamposObrigatorios($dadosLimpos, ['nome', 'email', 'status_conta', 'tipo_perfil']);

            // 3. Busca o usuário atual para atualizar
            $usuario = $this->usuarioRepo->buscarPorId($usuarioId, $pdo);
            if (!$usuario) throw new Exception("Usuário inválido.");

            // 4. Atualiza as propriedades (Crie os setters no model Usuario se não existirem)
            $usuario->setNome($dadosLimpos['nome']);
            $usuario->setEmail($dadosLimpos['email']);
            $usuario->setStatusConta($dadosLimpos['status_conta']);
            $usuario->setTipoAtual($dadosLimpos['tipo_perfil']);
            // Atualize telefone, cpf, etc., conforme necessário.

            // 5. Salva no banco (Crie ou ajuste o método 'atualizar' no UsuarioRepository)
            $this->usuarioRepo->atualizar($usuario, $pdo);

            $this->redirecionarComMensagem('sucesso', 'Usuário atualizado com sucesso!', '/admin/gerenciar-usuarios');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), "/admin/gerenciar-usuarios/editar/{$usuarioId}");
        }
    }

    // [D] Delete: Soft Delete (Inativação) do usuário
    public function deletar($params)
    {
        $usuarioId = $params['id'] ?? null;
        $pdo = ConnectionFactory::getConnection();

        try {
            $this->usuarioRepo->inativar($usuarioId, $pdo);

            $this->redirecionarComMensagem('sucesso', 'Usuário inativado com sucesso.', '/admin/gerenciar-usuarios');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao inativar usuário.', '/admin/gerenciar-usuarios', $e->getMessage());
        }
    }

    public function aprovarOng($params)
{
    $usuarioId = $params['id'] ?? null;
    $pdo = ConnectionFactory::getConnection();

    try {
        $pdo->beginTransaction();

        // 1. Atualiza o status do usuário no banco para 'ativo'
        $sql = "UPDATE USUARIO SET status_conta = 'ativo' WHERE usuario_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $usuarioId]);

        // 2. MÁGICA AQUI: Insere uma notificação do tipo 'sistema' para o usuário
        $sqlNotif = "INSERT INTO NOTIFICACAO (usuario_id, tipo_notificacao, lida, txt_notificacao) 
                     VALUES (:usuario_id, 'sistema', FALSE, 'Sua conta de ONG/Protetor foi aprovada com sucesso!')";
        $stmtNotif = $pdo->prepare($sqlNotif);
        $stmtNotif->execute(['usuario_id' => $usuarioId]);

        $pdo->commit();

        $this->redirecionarComMensagem('sucesso', 'Cadastro aprovado com sucesso!', '/admin/gerenciar-usuarios');
    } catch (Exception $e) {
        $pdo->rollBack();
        $this->redirecionarComMensagem('erro', 'Erro ao aprovar cadastro.', '/admin/gerenciar-usuarios', $e->getMessage());
    }
}
}