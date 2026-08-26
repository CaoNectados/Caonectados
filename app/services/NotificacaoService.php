<?php

namespace app\services;

use app\repositories\NotificacaoRepository;
use Exception;

/**
 * RF 11 (UC 06) / RN 06. Ponto único de disparo de notificações — nenhum outro service/
 * controller deve chamar NotificacaoRepository::criar() diretamente; todo mundo passa por
 * aqui, tanto pelos métodos genéricos (leitura/listagem) quanto pelos "notificarX" específicos
 * de cada evento do sistema (um por cenário do RN 06, pra manter o texto da mensagem num único
 * lugar por caso de uso).
 */
class NotificacaoService
{
    private NotificacaoRepository $repo;

    public function __construct(?NotificacaoRepository $repo = null)
    {
        $this->repo = $repo ?? new NotificacaoRepository();
    }

    // ===================== Leitura (UC 06 — tela /notificacoes e badge da navbar) =====================

    // Usado por: NotificacaoController::index() e carregarMais()
    public function listarPagina(int $usuarioId, int $limite, int $offset): array
    {
        return $this->repo->listarPorUsuario($usuarioId, $limite, $offset);
    }

    // Usado por: header.php — badge do sininho
    public function contarNaoLidas(int $usuarioId): int
    {
        return $this->repo->contarNaoLidas($usuarioId);
    }

    /**
     * Marca como lida e devolve a notificação, já validando posse — só o dono pode marcar/abrir
     * a própria notificação. Usado antes de resolver o redirecionamento (tipo + referencia_id).
     */
    // Usado por: NotificacaoController::abrir()
    public function abrir(int $notificacaoId, int $usuarioId): array
    {
        $notificacao = $this->repo->buscarPorId($notificacaoId);
        if (!$notificacao) {
            throw new Exception('Notificação não encontrada.');
        }

        if ((int) $notificacao['usuario_id'] !== $usuarioId) {
            throw new Exception('Você não tem permissão para acessar esta notificação.');
        }

        if (!$notificacao['lida']) {
            $this->repo->marcarComoLida($notificacaoId);
            $notificacao['lida'] = 1;
        }

        return $notificacao;
    }

    /**
     * Resolve pra onde a notificação leva o usuário, a partir do tipo + referencia_id. O papel
     * ativo (tipo_perfil da sessão) decide a rota de 'solicitacao' porque adotante e protetor
     * têm telas diferentes pro mesmo evento (o adotante não tem uma página de detalhe por
     * solicitação, só a lista). Retorna caminho relativo — quem chama prefixa com URL_BASE.
     */
    // Usado por: NotificacaoController::abrir()
    public static function resolverRotaDestino(string $tipoNotificacao, ?int $referenciaId, string $tipoPerfilAtual): string
    {
        if ($tipoNotificacao === 'mensagem' && $referenciaId) {
            return '/chats/conversa?id=' . $referenciaId;
        }

        if ($tipoNotificacao === 'solicitacao' && $referenciaId) {
            return in_array($tipoPerfilAtual, ['protetor', 'ong'], true)
                ? '/solicitacoes/detalhes?id=' . $referenciaId
                : '/minhas-solicitacoes';
        }

        // RF 18/21: denúncia mudou de status -> avisa quem denunciou, leva pro acompanhamento.
        if ($tipoNotificacao === 'denuncia' && $referenciaId) {
            return '/minhas-denuncias';
        }

        // RF 19: sanção aplicada -> leva direto pro formulário de contestação já com a
        // advertência certa selecionada (RF 17).
        if ($tipoNotificacao === 'advertencia' && $referenciaId) {
            return '/contestar?advertencia_id=' . $referenciaId;
        }

        // RF 22: contestação decidida -> leva pro acompanhamento das contestações.
        if ($tipoNotificacao === 'contestacao' && $referenciaId) {
            return '/minhas-contestacoes';
        }

        return '/perfil';
    }

    // ===================== Disparo — RF 08/09 (adoção, RN 06) =====================

    // Usado por: SolicitacaoAdocaoService::solicitarAdocao()
    public function notificarNovaSolicitacao(int $protetorUsuarioId, string $nomeAdotante, string $nomeAnimal, int $solicitacaoId): void
    {
        $this->repo->criar($protetorUsuarioId, "{$nomeAdotante} demonstrou interesse em adotar {$nomeAnimal}!", $solicitacaoId, 'solicitacao');
    }

    // Usado por: SolicitacaoAdocaoService::colocarEmAnalise()
    public function notificarSolicitacaoEmAnalise(int $adotanteUsuarioId, string $nomeAnimal, int $solicitacaoId): void
    {
        $this->repo->criar($adotanteUsuarioId, "Sua solicitação para adotar {$nomeAnimal} entrou em análise.", $solicitacaoId, 'solicitacao');
    }

    // Usado por: SolicitacaoAdocaoService::recusar()
    public function notificarSolicitacaoRecusada(int $adotanteUsuarioId, string $nomeAnimal, string $justificativa, int $solicitacaoId): void
    {
        $this->repo->criar($adotanteUsuarioId, "Sua solicitação para adotar {$nomeAnimal} foi recusada. Motivo: {$justificativa}", $solicitacaoId, 'solicitacao');
    }

    // Usado por: SolicitacaoAdocaoService::aprovar()
    public function notificarSolicitacaoAprovada(int $adotanteUsuarioId, string $nomeAnimal, int $solicitacaoId): void
    {
        $this->repo->criar($adotanteUsuarioId, "Parabéns! Sua solicitação para adotar {$nomeAnimal} foi aprovada. Você já pode conversar com o protetor pelo chat.", $solicitacaoId, 'solicitacao');
    }

    // Usado por: SolicitacaoAdocaoService::aprovar() — RN 08, um por adotante concorrente cancelado
    public function notificarSolicitacaoCanceladaAutomaticamente(int $adotanteUsuarioId, string $nomeAnimal, int $solicitacaoId): void
    {
        $this->repo->criar($adotanteUsuarioId, "Sua solicitação para adotar {$nomeAnimal} foi cancelada automaticamente: o animal já foi adotado por outro interessado.", $solicitacaoId, 'solicitacao');
    }

    // Usado por: SolicitacaoAdocaoService::cancelarSolicitacao()
    public function notificarSolicitacaoCanceladaPeloAdotante(int $protetorUsuarioId, string $nomeAnimal, int $solicitacaoId): void
    {
        $this->repo->criar($protetorUsuarioId, "O interessado em {$nomeAnimal} cancelou a solicitação de adoção.", $solicitacaoId, 'solicitacao');
    }

    // ===================== Disparo — RF 13 (chat) =====================

    // Usado por: ChatService::enviarMensagem()
    public function notificarNovaMensagem(int $destinatarioId, string $nomeAnimal, int $chatId): void
    {
        $this->repo->criar($destinatarioId, "Nova mensagem sobre {$nomeAnimal}.", $chatId, 'mensagem');
    }

    // Usado por: ChatService::encerrar()
    public function notificarChatEncerrado(int $destinatarioId, string $nomeAnimal, int $chatId): void
    {
        $this->repo->criar($destinatarioId, "A conversa sobre {$nomeAnimal} foi encerrada.", $chatId, 'mensagem');
    }

    // ===================== Disparo — Admin / Sistema =====================

    // Usado por: SolicitacaoService::aprovarSolicitacao() — RN 01, cadastro de ONG/Protetor aprovado
    public function notificarCadastroAprovado(int $usuarioId, string $nomeFantasia, int $protetorId): void
    {
        $this->repo->criar($usuarioId, "Seu cadastro como {$nomeFantasia} foi aprovado! Sua página já está no ar.", $protetorId, 'sistema');
    }

    // Usado por: SolicitacaoService::recusarSolicitacao() — RN 01, cadastro de ONG/Protetor recusado
    public function notificarCadastroRecusado(int $usuarioId, string $nomeFantasia, string $motivo, int $protetorId): void
    {
        $texto = "Seu cadastro como {$nomeFantasia} foi recusado.";
        if (trim($motivo) !== '') {
            $texto .= " Motivo: {$motivo}";
        }
        $this->repo->criar($usuarioId, $texto, $protetorId, 'sistema');
    }

    // Usado por: UsuarioAdminService::alterarStatusUsuario() — bloqueio/reativação da conta inteira
    public function notificarStatusContaAlterado(int $usuarioId, bool $desativada): void
    {
        $texto = $desativada
            ? 'Sua conta foi desativada por um administrador. Entre em contato com o suporte se acredita que isso é um engano.'
            : 'Sua conta foi reativada. Você já pode acessar a plataforma normalmente.';

        $this->repo->criar($usuarioId, $texto, null, $desativada ? 'advertencia' : 'sistema');
    }

    // Usado por: UsuarioAdminService::alterarStatusPerfil() — bloqueio/reativação de um perfil específico
    public function notificarStatusPerfilAlterado(int $usuarioId, string $nomePerfil, bool $desativado): void
    {
        $texto = $desativado
            ? "Seu perfil de {$nomePerfil} foi desativado por um administrador."
            : "Seu perfil de {$nomePerfil} foi reativado.";

        $this->repo->criar($usuarioId, $texto, null, $desativado ? 'advertencia' : 'sistema');
    }

    // ===================== Disparo — RF 18/19/21 (denúncia + advertência) =====================

    // Usado por: DenunciaService — toda transição de status_denuncia avisa quem denunciou
    public function notificarDenunciaStatusAlterado(int $denuncianteUsuarioId, string $novoStatus, int $denunciaId): void
    {
        $textos = [
            'em_analise' => 'Sua denúncia entrou em análise pela equipe de moderação.',
            'aprovada'   => 'Sua denúncia foi analisada e providências foram tomadas. Obrigado por ajudar a manter a comunidade segura.',
            'reprovada'  => 'Sua denúncia foi analisada e não foram encontradas evidências suficientes para uma penalidade.',
            'arquivada'  => 'Sua denúncia foi arquivada pela equipe de moderação.',
        ];

        $this->repo->criar($denuncianteUsuarioId, $textos[$novoStatus] ?? 'O status da sua denúncia foi atualizado.', $denunciaId, 'denuncia');
    }

    // Usado por: DenunciaService::aprovar() — RN 06, avisa o penalizado com o link pra contestar (RF 17)
    public function notificarAdvertenciaAplicada(int $usuarioId, string $pesoStatus, int $advertenciaId, bool $contaBloqueada): void
    {
        $texto = "Você recebeu uma advertência ({$pesoStatus}) após a análise de uma denúncia.";
        if ($contaBloqueada) {
            $texto .= ' Seu perfil foi bloqueado temporariamente.';
        }
        $texto .= ' Se acredita que isso é um engano, você pode abrir uma contestação.';

        $this->repo->criar($usuarioId, $texto, $advertenciaId, 'advertencia');
    }

    // ===================== Disparo — RF 17/22 (contestação) =====================

    // Usado por: ContestacaoService::decidir() — RN 06, avisa o contestante do parecer
    public function notificarContestacaoDecidida(int $usuarioId, bool $aprovada, int $contestacaoId): void
    {
        $texto = $aprovada
            ? 'Sua contestação foi aprovada! A penalidade foi encerrada e seu perfil está ativo novamente.'
            : 'Sua contestação foi analisada e a penalidade original foi mantida.';

        $this->repo->criar($usuarioId, $texto, $contestacaoId, 'contestacao');
    }
}
