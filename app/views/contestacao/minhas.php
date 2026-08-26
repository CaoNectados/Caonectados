<?php
$contestacoes = $contestacoes ?? [];
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$statusRotulo = [
    'pendente'  => ['label' => 'Aguardando análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'aprovada'  => ['label' => 'Aprovada', 'classe' => 'bg-sucesso/15 text-sucesso'],
    'reprovada' => ['label' => 'Reprovada', 'classe' => 'bg-erro/10 text-erro'],
];
?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6">
        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minhas Contestações</h1>
        <p class="text-sm text-text-muted mt-1">Acompanhe o parecer da equipe de moderação</p>
    </div>

    <?php if (empty($contestacoes)): ?>
        <div class="text-center py-16">
            <span class="text-5xl block mb-3">📄</span>
            <p class="text-text-muted text-sm">Você ainda não enviou nenhuma contestação.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($contestacoes as $c): ?>
                <?php $status = $statusRotulo[$c['status']] ?? ['label' => ucfirst($c['status']), 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted']; ?>
                <div class="bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-text-dark dark:text-white">Contestação #<?= (int) $c['contestacao_id'] ?></p>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 <?= $status['classe'] ?>"><?= $status['label'] ?></span>
                    </div>
                    <p class="text-xs text-text-muted mt-1 line-clamp-2"><?= htmlspecialchars($c['justificativa']) ?></p>
                    <?php if (!empty($c['parecer_admin'])): ?>
                        <p class="text-xs text-text-dark dark:text-white mt-2 bg-rosa-1/30 dark:bg-preto2 rounded-lg p-2"><strong>Parecer:</strong> <?= htmlspecialchars($c['parecer_admin']) ?></p>
                    <?php endif; ?>
                    <p class="text-[11px] text-text-muted mt-2"><?= date('d/m/Y', strtotime($c['data_hora'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
