<?php
$solicitacoes = $solicitacoes ?? [];
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$statusLabels = [
    'pendente'   => ['texto' => 'Aguardando análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'em_analise' => ['texto' => 'Em análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'aprovada'   => ['texto' => 'Aprovada 🎉', 'classe' => 'bg-sucesso/15 text-sucesso'],
    'reprovada'  => ['texto' => 'Recusada', 'classe' => 'bg-erro/10 text-erro'],
    'cancelada'  => ['texto' => 'Cancelada', 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'],
];
?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6">
        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minhas Solicitações</h1>
        <p class="text-sm text-text-muted mt-1">Acompanhe seus petiscos enviados</p>
    </div>

    <?php if (empty($solicitacoes)): ?>
        <div class="text-center py-16">
            <span class="text-5xl block mb-3">🐾</span>
            <p class="text-text-muted text-sm">Você ainda não demonstrou interesse em nenhum animal.</p>
            <a href="<?= $urlBase ?>/feed" class="inline-block mt-4 btn-primario text-sm">Ver animais no Feed</a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($solicitacoes as $solic): ?>
                <?php
                    $status = $statusLabels[$solic['status_solicitacao']] ?? ['texto' => $solic['status_solicitacao'], 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'];
                    $foto = !empty($solic['animal_foto']) ? $urlBase . '/' . ltrim($solic['animal_foto'], '/') : null;
                    $podeCancel = in_array($solic['status_solicitacao'], ['pendente', 'em_analise'], true);
                ?>
                <div class="flex items-center gap-3 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl p-3 shadow-sm">
                    <?php if ($foto): ?>
                        <img src="<?= htmlspecialchars($foto) ?>" alt="" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    <?php else: ?>
                        <div class="w-14 h-14 rounded-xl bg-rosa-1 dark:bg-preto2 flex items-center justify-center text-xl shrink-0">🐾</div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-text-dark dark:text-white truncate"><?= htmlspecialchars($solic['animal_nome']) ?></p>
                        <p class="text-xs text-text-muted truncate"><?= htmlspecialchars($solic['nome_fantasia'] ?? 'Protetor independente') ?></p>
                        <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full <?= $status['classe'] ?>"><?= htmlspecialchars($status['texto']) ?></span>
                        <?php if ($solic['status_solicitacao'] === 'reprovada' && !empty($solic['justificativa_recusa'])): ?>
                            <p class="text-[11px] text-text-muted mt-1">Motivo: <?= htmlspecialchars($solic['justificativa_recusa']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if ($podeCancel): ?>
                        <form id="form-cancelar-<?= (int) $solic['solicitacao_id'] ?>" method="POST" action="<?= $urlBase ?>/minhas-solicitacoes/cancelar">
                            <input type="hidden" name="id" value="<?= (int) $solic['solicitacao_id'] ?>">
                            <button type="button"
                                    onclick="abrirModalConfirmacao('Cancelar solicitação', 'Tem certeza que deseja cancelar esta solicitação?', function () { document.getElementById('form-cancelar-<?= (int) $solic['solicitacao_id'] ?>').submit(); }, 'Cancelar solicitação', 'Voltar')"
                                    class="text-xs font-bold text-erro underline hover:opacity-80 shrink-0">Cancelar</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
