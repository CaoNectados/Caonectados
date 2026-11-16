<?php

namespace app\controllers\admin;

use app\services\AnimalAdminService;
use DomainException;
use Throwable;

class AnimalController extends AdminBaseController
{
    private AnimalAdminService $service;

    public function __construct()
    {
        parent::__construct();
        $this->validarCsrf();
        $this->service = new AnimalAdminService();
    }

    public function index(): void
    {
        $busca = $_GET['busca'] ?? '';
        $status = $_GET['status'] ?? 'todos';
        $pagina = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT);
        try {
            if (!is_string($busca) || !is_string($status) || $pagina === false) {
                throw new DomainException('Filtros inválidos.');
            }
            $dados = $this->service->listar($busca, $status, $pagina);
            $this->view('admin/animais', $dados + [
                'titulo' => 'Gerenciar Animais', 'busca' => $busca, 'status' => $status,
            ]);
        } catch (Throwable $e) {
            $this->redirecionarComMensagem('erro',
                $e instanceof DomainException ? $e->getMessage() : 'Não foi possível carregar os animais.',
                '/admin/dashboard');
        }
    }

    public function desativar(): void
    {
        $this->alterar(false);
    }

    public function reativar(): void
    {
        $this->alterar(true);
    }

    private function alterar(bool $reativar): void
    {
        try {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id === null) throw new DomainException('Animal inválido.');
            $this->service->alterarDisponibilidade($id, (int) $_SESSION['usuario_id'], $reativar);
            $this->redirecionarComMensagem('sucesso',
                $reativar ? 'Animal reativado. O anúncio segue o status das solicitações em análise.' : 'Animal desativado. O anúncio saiu do catálogo.',
                '/admin/animais');
        } catch (Throwable $e) {
            $this->redirecionarComMensagem('erro',
                $e instanceof DomainException ? $e->getMessage() : 'Não foi possível atualizar o animal.',
                '/admin/animais');
        }
    }
}

