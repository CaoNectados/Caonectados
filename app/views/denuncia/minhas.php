<?php
$denuncias = $denuncias ?? [];
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$motivosRotulo = ['maus_tratos' => 'Maus-tratos', 'abandono' => 'Abandono', 'fraude' => 'Fraude', 'assedio' => 'Assédio', 'outro' => 'Outro'];
$statusRotulo = [
    'aberta'     => ['label' => 'Aguardando análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'em_analise' => ['label' => 'Em análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'aprovada'   => ['label' => 'Providências tomadas', 'classe' => 'bg-sucesso/15 text-sucesso'],
    'reprovada'  => ['label' => 'Sem evidências suficientes', 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'],
    'arquivada'  => ['label' => 'Arquivada', 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'],
];
?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minhas Denúncias</h1>
            <p class="text-sm text-text-muted mt-1">Acompanhe o andamento</p>
        </div>
        <a href="<?= $urlBase ?>/denunciar" class="shrink-0 text-xs font-bold text-white bg-erro px-4 py-2 rounded-full shadow hover:opacity-90">+ Nova</a>
    </div>

    <?php if (empty($denuncias)): ?>
        <div class="text-center py-16">
            <span class="text-5xl block mb-3">🕊️</span>
            <p class="text-text-muted text-sm">Você ainda não abriu nenhuma denúncia.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($denuncias as $d): ?>
                <?php $status = $statusRotulo[$d['status_denuncia']] ?? ['label' => ucfirst($d['status_denuncia']), 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted']; ?>
                <div class="bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-text-dark dark:text-white">Contra: <?= htmlspecialchars($d['denunciado_nome']) ?></p>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 <?= $status['classe'] ?>"><?= $status['label'] ?></span>
                    </div>
                    <p class="text-xs text-text-muted mt-1">Motivo: <?= htmlspecialchars($motivosRotulo[$d['motivo']] ?? ucfirst($d['motivo'])) ?></p>
                    <p class="text-xs text-text-muted mt-1 line-clamp-2"><?= htmlspecialchars($d['descricao']) ?></p>
                    <p class="text-[11px] text-text-muted mt-2"><?= date('d/m/Y', strtotime($d['criado_em'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
