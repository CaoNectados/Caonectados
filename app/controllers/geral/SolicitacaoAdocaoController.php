<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\services\SolicitacaoAdocaoService;
use app\repositories\AdotanteRepository;
use app\repositories\ProtetorRepository;
use Exception;

/**
 * RF 08 (UC 15 — Adotante) e RF 09 (UC 18 — Protetor/ONG): coração do fluxo de adoção.
 * O Adotante manifesta interesse ("Dar Petisco!") e acompanha suas solicitações; o
 * Protetor/ONG recebe, tria (em análise/recusa/aprova) as solicitações dos seus animais.
 */
class SolicitacaoAdocaoController extends Controller
{
    private SolicitacaoAdocaoService $service;

    public function __construct()
    {
        $this->autenticacaoRequired(['adotante', 'protetor', 'ong']);
        $this->service = new SolicitacaoAdocaoService();
    }

    // ===================== ADOTANTE (RF 08 / UC 15) =====================

    /** Registra uma manifestação de interesse ("Dar Petisco!"). Usado pelo Feed via AJAX (POST /solicitacoes/criar). */
    public function criar(): void
    {
        try {
            $this->exigirPerfil(['adotante']);
            $this->exigirContaNaoBloqueada();

            $animalId = (int) ($_POST['animal_id'] ?? 0);
            if ($animalId <= 0) {
                throw new Exception('Animal inválido.');
            }

            $adotanteId = $this->obterAdotanteIdAutenticado();
            $usuarioId = (int) $_SESSION['usuario_id'];

            $this->service->solicitarAdocao($adotanteId, $usuarioId, $animalId);

            $this->json(200, [
                'status'   => 'sucesso',
                'mensagem' => 'Petisco enviado! O protetor foi notificado do seu interesse.',
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Lista as solicitações do adotante logado (UC 15 — acompanhamento). Usado pela rota GET /minhas-solicitacoes. */
    public function minhas(): void
    {
        $this->exigirPerfil(['adotante']);

        try {
            $adotanteId = $this->obterAdotanteIdAutenticado();
            $solicitacoes = $this->service->listarMinhasSolicitacoes($adotanteId);

            $this->view('solicitacao/minhas', [
                'titulo'       => 'Minhas Solicitações',
                'solicitacoes' => $solicitacoes,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar suas solicitações.', '/feed', $e->getMessage());
        }
    }

    /** Cancela uma solicitação pendente/em análise do próprio adotante. Usado pela rota POST /minhas-solicitacoes/cancelar. */
    public function cancelar(): void
    {
        try {
            $this->exigirPerfil(['adotante']);

            $solicitacaoId = (int) ($_POST['id'] ?? 0);
            $this->service->cancelarSolicitacao($solicitacaoId, (int) $_SESSION['usuario_id']);

            $this->redirecionarComMensagem('sucesso', 'Solicitação cancelada.', '/minhas-solicitacoes');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível cancelar.', '/minhas-solicitacoes', $e->getMessage());
        }
    }

    // ===================== PROTETOR / ONG (RF 09 / UC 18) =====================

    /** Painel de solicitações recebidas, segmentado por aba. Usado pela rota GET /solicitacoes. */
    public function painel(): void
    {
        $this->exigirPerfil(['protetor', 'ong']);

        try {
            $protetorId = $this->obterProtetorIdAutenticado();
            $aba = $_GET['aba'] ?? 'pendentes';
            if (!in_array($aba, ['pendentes', 'aprovadas', 'recusadas'], true)) {
                $aba = 'pendentes';
            }

            $this->view('solicitacao/painel', [
                'titulo'       => 'Solicitações de Adoção',
                'solicitacoes' => $this->service->listarRecebidas($protetorId, $aba),
                'abaAtual'     => $aba,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar solicitações.', '/perfil', $e->getMessage());
        }
    }

    /** Detalhe de uma solicitação (documentos/dados do adotante) antes de decidir. Usado pela rota GET /solicitacoes/detalhes (UC 18). */
    public function detalhes(): void
    {
        $this->exigirPerfil(['protetor', 'ong']);

        try {
            $solicitacaoId = (int) ($_GET['id'] ?? 0);
            $protetorId = $this->obterProtetorIdAutenticado();

            $solicitacao = $this->service->obterDetalhesParaProtetor($solicitacaoId, $protetorId);

            $this->view('solicitacao/detalhes', [
                'titulo'      => 'Detalhes da Solicitação',
                'solicitacao' => $solicitacao,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Solicitação não encontrada.', '/solicitacoes', $e->getMessage());
        }
    }

    /** Coloca uma solicitação pendente em análise (UC 18.3). Usado pela rota POST /solicitacoes/em-analise. */
    public function colocarEmAnalise(): void
    {
        try {
            $this->exigirPerfil(['protetor', 'ong']);
            $this->exigirContaNaoBloqueada();

            $solicitacaoId = (int) ($_POST['id'] ?? 0);
            $protetorId = $this->obterProtetorIdAutenticado();

            $this->service->colocarEmAnalise($solicitacaoId, $protetorId, (int) $_SESSION['usuario_id']);

            $this->redirecionarComMensagem('sucesso', 'Solicitação colocada em análise.', '/solicitacoes');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível atualizar a solicitação.', '/solicitacoes', $e->getMessage());
        }
    }

    /** Recusa uma solicitação com justificativa obrigatória (UC 18.1 / RN 07). Usado pela rota POST /solicitacoes/recusar. */
    public function recusar(): void
    {
        try {
            $this->exigirPerfil(['protetor', 'ong']);
            $this->exigirContaNaoBloqueada();

            $solicitacaoId = (int) ($_POST['id'] ?? 0);
            $justificativa = (string) ($_POST['justificativa'] ?? '');
            $protetorId = $this->obterProtetorIdAutenticado();

            $this->service->recusar($solicitacaoId, $protetorId, (int) $_SESSION['usuario_id'], $justificativa);

            $this->redirecionarComMensagem('sucesso', 'Solicitação recusada.', '/solicitacoes');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível recusar a solicitação.', '/solicitacoes/detalhes?id=' . (int) ($_POST['id'] ?? 0), $e->getMessage());
        }
    }

    /** Aprova a adoção ("Dar a Patinha!" — UC 18.2). Usado pela rota POST /solicitacoes/aprovar. */
    public function aprovar(): void
    {
        try {
            $this->exigirPerfil(['protetor', 'ong']);
            $this->exigirContaNaoBloqueada();

            $solicitacaoId = (int) ($_POST['id'] ?? 0);
            $protetorId = $this->obterProtetorIdAutenticado();

            $this->service->aprovar($solicitacaoId, $protetorId, (int) $_SESSION['usuario_id']);

            $this->redirecionarComMensagem('sucesso', 'Adoção aprovada! O chat com o adotante já está disponível.', '/solicitacoes?aba=aprovadas');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível aprovar a solicitação.', '/solicitacoes', $e->getMessage());
        }
    }

    /** Registra a devolução de uma adoção aprovada (RN 12/14 — inadimplência do adotante; RN 10 — encerra o chat). Usado pela rota POST /solicitacoes/devolver. */
    public function devolver(): void
    {
        $solicitacaoId = (int) ($_POST['id'] ?? 0);
        try {
            $this->exigirPerfil(['protetor', 'ong']);
            $this->exigirContaNaoBloqueada();

            $protetorId = $this->obterProtetorIdAutenticado();

            $this->service->registrarDevolucao($solicitacaoId, $protetorId, (int) $_SESSION['usuario_id']);

            $this->redirecionarComMensagem('sucesso', 'Devolução registrada. O animal voltou a ficar disponível.', '/solicitacoes?aba=aprovadas');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Não foi possível registrar a devolução.', '/solicitacoes/detalhes?id=' . $solicitacaoId, $e->getMessage());
        }
    }

    // ===================== Uso interno =====================

    /**
     * Bloqueia o perfil fora do escopo do método (ex: protetor tentando criar()) — por
     * método, não pelo construtor, porque este controller atende os dois perfis.
     */
    private function exigirPerfil(array $perfisPermitidos): void
    {
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? '';
        if (!in_array($tipoPerfil, $perfisPermitidos, true)) {
            $this->redirecionarComMensagem('erro', 'Você não tem permissão para acessar esta área.', '/perfil');
        }
    }

    /** Resolve o adotante_id do usuário logado, cacheando na sessão. Usado por criar(), minhas() e cancelar(). */
    private function obterAdotanteIdAutenticado(): int
    {
        if (isset($_SESSION['adotante_id']) && (int) $_SESSION['adotante_id'] > 0) {
            return (int) $_SESSION['adotante_id'];
        }

        $adotante = (new AdotanteRepository())->buscarPorUsuarioId((int) ($_SESSION['usuario_id'] ?? 0));
        if (!$adotante) {
            throw new Exception('Perfil de adotante não encontrado para este usuário.');
        }

        $_SESSION['adotante_id'] = (int) $adotante['adotante_id'];
        return (int) $adotante['adotante_id'];
    }

    /**
     * Resolve o protetor_id do usuário logado, cacheando na sessão. Usado por painel(),
     * detalhes(), colocarEmAnalise(), recusar(), aprovar() e devolver().
     */
    private function obterProtetorIdAutenticado(): int
    {
        if (isset($_SESSION['protetor_id']) && (int) $_SESSION['protetor_id'] > 0) {
            return (int) $_SESSION['protetor_id'];
        }

        $protetor = (new ProtetorRepository())->buscarPorUsuarioId((int) ($_SESSION['usuario_id'] ?? 0));
        if (!$protetor) {
            throw new Exception('Perfil de protetor não encontrado para este usuário.');
        }

        $_SESSION['protetor_id'] = (int) $protetor['protetor_id'];
        return (int) $protetor['protetor_id'];
    }
}
