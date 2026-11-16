<?php
$solicitacoes = $solicitacoes ?? [];
require_once __DIR__ . '/../templates/header.php';
echo renderVoltar('/perfil');

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$statusLabels = [
    'pendente'   => ['texto' => 'Enviada', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'em_analise' => ['texto' => 'Em análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'aprovada'   => ['texto' => 'Aprovada', 'classe' => 'bg-sucesso/15 text-sucesso'],
    'reprovada'  => ['texto' => 'Recusada', 'classe' => 'bg-erro/10 text-erro'],
    'cancelada'  => ['texto' => 'Cancelada', 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'],
];

$encontrosCaoNectou = \app\helpers\CaoNectouHelper::dados($solicitacoes, false, (int) $_SESSION['usuario_id']);

$montarFoto = static function (?string $caminho) use ($urlBase): string {
    if (!$caminho) return asset('assets/img/perfil-placeholder.png');
    if (preg_match('#^https?://#i', $caminho)) return $caminho;
    return $urlBase . '/' . ltrim($caminho, '/');
};

?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minhas Solicitações</h1>
            <p class="text-sm text-text-muted mt-1">Acompanhe seus petiscos enviados</p>
        </div>
    </div>

    <?php if (empty($solicitacoes)): ?>
        <div class="text-center py-16">
            <div class="flex justify-center mb-3"><?= renderIconeMenu("patinha.svg", "", "h-12 w-12 text-primary dark:text-white") ?></div>
            <p class="text-text-muted text-sm">Você ainda não demonstrou interesse em nenhum animal.</p>
            <a href="<?= e($urlBase) ?>/feed" class="inline-block mt-4 btn-primario text-sm">Ver animais no Feed</a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($solicitacoes as $solic): ?>
                <?php
                $status = $statusLabels[$solic['status_solicitacao']] ?? ['texto' => $solic['status_solicitacao'], 'classe' => $statusLabels['cancelada']['classe']];
                $foto = !empty($solic['animal_foto']) ? $montarFoto($solic['animal_foto']) : null;
                $podeCancel = in_array($solic['status_solicitacao'], ['pendente', 'em_analise'], true);
                $podeExibirCaoNectou = isset($encontrosCaoNectou[(int) $solic['solicitacao_id']]);
                ?>
                <div class="flex items-center gap-3 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl p-3 shadow-sm">
                    <?php if ($foto): ?>
                        <img src="<?= e($foto) ?>" alt="Foto de <?= e($solic['animal_nome']) ?>" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    <?php else: ?>
                        <div class="w-14 h-14 rounded-xl bg-rosa-1 dark:bg-preto2 flex items-center justify-center shrink-0">
                            <img src="<?= e(asset('assets/img/icone-adotado-patinha-coracao.png')) ?>" alt="" class="w-8 h-8 object-contain">
                        </div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-text-dark dark:text-white truncate"><?= e($solic['animal_nome']) ?></p>
                        <p class="text-xs text-text-muted truncate"><?= e($solic['nome_fantasia'] ?? 'Protetor independente') ?></p>
                        <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full <?= e($status['classe']) ?>"><?= e($status['texto']) ?></span>
                        <?php if ($solic['status_solicitacao'] === 'reprovada' && !empty($solic['justificativa_recusa'])): ?>
                            <p class="text-[11px] text-text-muted mt-1">Motivo: <?= e($solic['justificativa_recusa']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <?php if ($podeExibirCaoNectou): ?>
                            <button type="button" data-caonectou-id="<?= (int) $solic['solicitacao_id'] ?>" class="btn-ver-caonectou rounded-full bg-rosa-1 px-3 py-1.5 text-xs font-bold text-text-dark transition hover:scale-105 dark:bg-primary dark:text-white">CãoNectou!</button>
                        <?php endif; ?>
                        <?php if ($podeCancel): ?>
                            <form id="form-cancelar-<?= (int) $solic['solicitacao_id'] ?>" method="POST" action="<?= e($urlBase) ?>/minhas-solicitacoes/cancelar">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int) $solic['solicitacao_id'] ?>">
                                <button type="button"
                                    onclick="const form = this.closest('form'); abrirModalConfirmacao('Cancelar solicitação', 'Tem certeza que deseja cancelar esta solicitação?', () => form.requestSubmit(), 'Cancelar solicitação', 'Voltar')"
                                    class="text-xs font-bold text-erro underline hover:opacity-80">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../templates/modal_caonectou.php'; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
