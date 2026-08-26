<?php
$contestacoes = $contestacoes ?? [];
$abaAtual = $abaAtual ?? 'pendentes';
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$pesoRotulo = ['leve' => 'Leve', 'media' => 'Média', 'grave' => 'Grave'];
$statusRotulo = [
    'pendente'  => ['label' => 'Pendente', 'classe' => 'text-laranja-1'],
    'aprovada'  => ['label' => 'Aprovada', 'classe' => 'text-sucesso'],
    'reprovada' => ['label' => 'Reprovada', 'classe' => 'text-erro'],
];
?>

<div class="space-y-6 pb-10">
    <div class="my-4 text-center">
        <h1 class="text-3xl font-bold font-shantell text-primary">Contestações</h1>
        <p class="text-xs text-text-muted mt-1">Moderação de contestações de advertência (RF 22)</p>
    </div>

    <div class="grid grid-cols-2 gap-3 max-w-sm mx-auto">
        <a href="<?= $urlBase ?>/admin/contestacoes?aba=pendentes" class="py-2.5 text-center text-sm font-bold rounded-xl border-2 border-preto dark:border-cinzaMarrom <?= $abaAtual === 'pendentes' ? 'bg-laranja-1 text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white hover:bg-laranja-1/10' ?>">Pendentes</a>
        <a href="<?= $urlBase ?>/admin/contestacoes?aba=decididas" class="py-2.5 text-center text-sm font-bold rounded-xl border-2 border-preto dark:border-cinzaMarrom <?= $abaAtual === 'decididas' ? 'bg-verdeMusgo text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white hover:bg-verdeMusgo/10' ?>">Decididas</a>
    </div>

    <?php if (empty($contestacoes)): ?>
        <div class="card-padrao text-center py-16">
            <span class="text-5xl block mb-3 opacity-40">📄</span>
            <p class="text-text-muted text-base font-semibold">Nenhuma contestação nesta categoria.</p>
        </div>
    <?php else: ?>
        <div class="card-padrao overflow-x-auto p-0">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-cinzaMarrom/15 dark:bg-preto2 text-left text-xs uppercase text-text-muted">
                        <th class="px-4 py-3 font-bold">Data</th>
                        <th class="px-4 py-3 font-bold">Usuário</th>
                        <th class="px-4 py-3 font-bold">Peso da Advertência</th>
                        <th class="px-4 py-3 font-bold">Justificativa</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cinzaMarrom/15 dark:divide-preto3">
                    <?php foreach ($contestacoes as $c): ?>
                        <?php $status = $statusRotulo[$c['status']] ?? ['label' => ucfirst($c['status']), 'classe' => 'text-text-muted']; ?>
                        <tr class="hover:bg-rosa-1/10 dark:hover:bg-preto2 transition align-top">
                            <td class="px-4 py-3 text-text-muted whitespace-nowrap"><?= date('d/m/Y', strtotime($c['data_hora'])) ?></td>
                            <td class="px-4 py-3 text-text-dark dark:text-white"><?= htmlspecialchars($c['usuario_nome']) ?></td>
                            <td class="px-4 py-3 text-text-muted"><?= htmlspecialchars($pesoRotulo[$c['peso_status']] ?? $c['peso_status']) ?></td>
                            <td class="px-4 py-3 text-text-dark dark:text-white max-w-xs truncate" title="<?= htmlspecialchars($c['justificativa']) ?>"><?= htmlspecialchars($c['justificativa']) ?></td>
                            <td class="px-4 py-3"><span class="font-bold text-xs <?= $status['classe'] ?>"><?= $status['label'] ?></span></td>
                            <td class="px-4 py-3 text-right"><a href="<?= $urlBase ?>/admin/contestacoes/detalhes?id=<?= (int) $c['contestacao_id'] ?>" class="text-xs font-bold text-primary underline hover:opacity-80">Ver detalhes</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
