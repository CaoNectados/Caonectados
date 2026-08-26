<?php
$abaAtual = $abaAtual ?? 'abertas';
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
?>

<div class="space-y-6 pb-10">
    <div class="my-4 text-center">
        <h1 class="text-3xl font-bold font-shantell text-primary">Denúncias</h1>
        <p class="text-xs text-text-muted mt-1">Moderação de denúncias abertas pelos usuários (RF 21)</p>
    </div>

    <div class="grid grid-cols-3 gap-3 max-w-xl mx-auto">
        <a href="<?= $urlBase ?>/admin/denuncias?aba=abertas" class="py-2.5 text-center text-sm font-bold rounded-xl border-2 border-preto dark:border-cinzaMarrom <?= $abaAtual === 'abertas' ? 'bg-erro text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white hover:bg-erro/10' ?>">Abertas</a>
        <a href="<?= $urlBase ?>/admin/denuncias?aba=em_analise" class="py-2.5 text-center text-sm font-bold rounded-xl border-2 border-preto dark:border-cinzaMarrom <?= $abaAtual === 'em_analise' ? 'bg-laranja-1 text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white hover:bg-laranja-1/10' ?>">Em Análise</a>
        <a href="<?= $urlBase ?>/admin/denuncias?aba=resolvidas" class="py-2.5 text-center text-sm font-bold rounded-xl border-2 border-preto dark:border-cinzaMarrom <?= $abaAtual === 'resolvidas' ? 'bg-verdeMusgo text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white hover:bg-verdeMusgo/10' ?>">Resolvidas</a>
    </div>

    <?php if (empty($denuncias)): ?>
        <div class="card-padrao text-center py-16">
            <span class="text-5xl block mb-3 opacity-40">🕊️</span>
            <p class="text-text-muted text-base font-semibold">Nenhuma denúncia nesta categoria.</p>
        </div>
    <?php else: ?>
        <div class="card-padrao overflow-x-auto p-0">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-cinzaMarrom/15 dark:bg-preto2 text-left text-xs uppercase text-text-muted">
                        <th class="px-4 py-3 font-bold">Data</th>
                        <th class="px-4 py-3 font-bold">Denunciante</th>
                        <th class="px-4 py-3 font-bold">Denunciado</th>
                        <th class="px-4 py-3 font-bold">Perfil</th>
                        <th class="px-4 py-3 font-bold">Motivo</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cinzaMarrom/15 dark:divide-preto3">
                    <?php
                        $motivosRotulo = [
                            'maus_tratos' => 'Maus-tratos',
                            'abandono'    => 'Abandono',
                            'fraude'      => 'Fraude',
                            'assedio'     => 'Assédio',
                            'outro'       => 'Outro',
                        ];
                        $statusRotulo = [
                            'aberta'     => ['label' => 'Aberta', 'classe' => 'text-erro'],
                            'em_analise' => ['label' => 'Em Análise', 'classe' => 'text-laranja-1'],
                            'aprovada'   => ['label' => 'Aprovada', 'classe' => 'text-sucesso'],
                            'reprovada'  => ['label' => 'Reprovada', 'classe' => 'text-text-muted'],
                            'arquivada'  => ['label' => 'Arquivada', 'classe' => 'text-text-muted'],
                        ];
                    ?>
                    <?php foreach ($denuncias as $d): ?>
                        <?php $status = $statusRotulo[$d['status_denuncia']] ?? ['label' => ucfirst($d['status_denuncia']), 'classe' => 'text-text-muted']; ?>
                        <tr class="hover:bg-rosa-1/10 dark:hover:bg-preto2 transition align-top">
                            <td class="px-4 py-3 text-text-muted whitespace-nowrap"><?= !empty($d['criado_em']) ? date('d/m/Y', strtotime($d['criado_em'])) : '-' ?></td>
                            <td class="px-4 py-3 text-text-dark dark:text-white"><?= htmlspecialchars($d['denunciante_nome'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-text-dark dark:text-white"><?= htmlspecialchars($d['denunciado_nome'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-text-muted"><?= htmlspecialchars(ucfirst($d['perfil_denunciado'])) ?></td>
                            <td class="px-4 py-3 text-text-dark dark:text-white">
                                <span class="font-medium"><?= htmlspecialchars($motivosRotulo[$d['motivo']] ?? ucfirst($d['motivo'])) ?></span>
                                <p class="text-xs text-text-muted mt-0.5 max-w-xs truncate" title="<?= htmlspecialchars($d['descricao']) ?>"><?= htmlspecialchars($d['descricao']) ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-xs <?= $status['classe'] ?>"><?= $status['label'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= $urlBase ?>/admin/denuncias/detalhes?id=<?= (int) $d['denuncia_id'] ?>" class="text-xs font-bold text-primary underline hover:opacity-80">Ver detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
