<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\repositories\ProtetorRepository;
use app\services\PaginaService;
use Exception;

/**
 * RF 06 / UC 19 — Manter Páginas dos Protetores/ONGs.
 * Área de gestão (autenticada, dono da página) + visão pública (RN 18/RN 01).
 */
class PaginaController extends Controller
{
    private PaginaService $paginaService;
    private ProtetorRepository $protetorRepo;

    public function __construct()
    {
        $this->paginaService = new PaginaService();
        $this->protetorRepo = new ProtetorRepository();
    }

    /** Exibe a tela de gestão da página do protetor/ONG logado. Usado pela rota GET /pagina-perfil (UC 19). */
    public function editar(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

        try {
            $protetorId = $this->obterProtetorIdAutenticado();
            $dados = $this->paginaService->obterDadosGerenciamento($protetorId);

            $this->view('pagina/editar', [
                'titulo' => 'Minha Página',
                'pagina' => $dados['pagina'],
                'redes'  => $dados['redes'],
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar sua página: ' . $e->getMessage(), '/perfil');
        }
    }

    /** Salva descrição, chave PIX e fotos (perfil/fundo) da página. Usado pela rota POST /pagina-perfil/atualizar (UC 19.1/19.2/19.3). */
    public function atualizar(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

        try {
            $protetorId = $this->obterProtetorIdAutenticado();

            $this->paginaService->atualizarDescricaoEChavePix(
                $protetorId,
                trim($_POST['descricao'] ?? ''),
                trim($_POST['chave_pix'] ?? '')
            );

            $fotoPerfil = $_FILES['foto_perfil'] ?? ($_POST['foto_perfil_cortada'] ?? null);
            if (!empty($fotoPerfil)) {
                $this->paginaService->atualizarFoto($protetorId, 'foto_perfil', $fotoPerfil);
            }

            $fotoFundo = $_FILES['foto_fundo'] ?? ($_POST['foto_fundo_cortada'] ?? null);
            if (!empty($fotoFundo)) {
                $this->paginaService->atualizarFoto($protetorId, 'foto_fundo', $fotoFundo);
            }

            $this->redirecionarComMensagem('sucesso', 'Página atualizada com sucesso!', '/pagina-perfil');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao atualizar página: ' . $e->getMessage(), '/pagina-perfil');
        }
    }

    /** Adiciona uma rede social à página. Usado pela rota POST /pagina-perfil/rede/adicionar (UC 19.2). */
    public function adicionarRede(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

        try {
            $protetorId = $this->obterProtetorIdAutenticado();
            $this->paginaService->adicionarRede(
                $protetorId,
                $_POST['tipo_rede'] ?? '',
                $_POST['link_rede'] ?? ''
            );

            $this->redirecionarComMensagem('sucesso', 'Rede social adicionada!', '/pagina-perfil');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/pagina-perfil');
        }
    }

    /** Remove uma rede social da página. Usado pela rota POST /pagina-perfil/rede/remover (UC 19.2). */
    public function removerRede(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

        try {
            $protetorId = $this->obterProtetorIdAutenticado();
            $redeId = (int) ($_POST['rede_id'] ?? 0);

            $this->paginaService->removerRede($redeId, $protetorId);

            $this->redirecionarComMensagem('sucesso', 'Rede social removida.', '/pagina-perfil');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/pagina-perfil');
        }
    }

    /**
     * Exibe a página pública de um protetor/ONG (RN 18 — conteúdo exibido; RN 01 — só
     * visível se validado). Aberta a qualquer visitante, inclusive anônimo, sem exigir
     * autenticacaoRequired() — assim como /animal/mostrar já é. Usado pela rota GET /pagina?id={protetor_id}.
     */
    public function publica(): void
    {
        $protetorId = (int) ($_GET['id'] ?? 0);

        if ($protetorId <= 0) {
            $this->redirecionarComMensagem('erro', 'Página não encontrada.', '/');
            return;
        }

        $dados = $this->paginaService->obterDadosPublicos($protetorId);

        if ($dados === null) {
            // RN 01: protetor inexistente, não validado ou removido — mesma mensagem nos três
            // casos, pra não revelar se o cadastro existe (evita enumeração de contas).
            $this->redirecionarComMensagem('aviso', 'Esta página não está disponível no momento.', '/');
            return;
        }

        $this->view('pagina/publica', [
            'titulo'      => $dados['protetor']['nome_fantasia'] ?? 'Página',
            'protetor'    => $dados['protetor'],
            'redes'       => $dados['redes'],
            'disponiveis' => $dados['disponiveis'],
            'adotados'    => $dados['adotados'],
        ]);
    }

    /** Resolve o protetor_id do usuário logado. Mesmo padrão de AnimalController::obterProtetorIdAutenticado(). */
    private function obterProtetorIdAutenticado(): int
    {
        if (isset($_SESSION['protetor_id']) && (int) $_SESSION['protetor_id'] > 0) {
            return (int) $_SESSION['protetor_id'];
        }

        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);

        if (!$protetor) {
            throw new Exception('Perfil de protetor não encontrado para este usuário.');
        }

        $_SESSION['protetor_id'] = (int) $protetor['protetor_id'];
        return (int) $protetor['protetor_id'];
    }
}
