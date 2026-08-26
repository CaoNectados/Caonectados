<?php
$denuncia = $denuncia ?? [];
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$motivosRotulo = ['maus_tratos' => 'Maus-tratos', 'abandono' => 'Abandono', 'fraude' => 'Fraude', 'assedio' => 'Assédio', 'outro' => 'Outro'];
$status = $denuncia['status_denuncia'];
$finalizada = in_array($status, ['aprovada', 'reprovada', 'arquivada'], true);
?>

<div class="min-h-screen bg-background text-text-dark p-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-2xl bg-surface rounded-3xl md:rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20">

        <div class="flex items-center justify-between gap-4 mb-6 pb-2 border-b border-cinzaMarrom/20">
            <h2 class="font-shantell text-2xl font-bold text-text-dark">Detalhes da Denúncia</h2>
            <a href="<?= $urlBase ?>/admin/denuncias" class="px-4 py-2 border-2 border-preto rounded-xl text-xs font-bold text-text-dark hover:bg-preto hover:text-white transition">Voltar &rsaquo;</a>
        </div>

        <div class="space-y-4 divide-y divide-cinzaMarrom/20 mb-8">
            <div class="pt-3 first:pt-0">
                <h4 class="font-bold text-sm">Denunciante</h4>
                <p class="text-xs text-text-muted"><?= htmlspecialchars($denuncia['denunciante_nome']) ?></p>
            </div>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Denunciado</h4>
                <p class="text-xs text-text-muted"><?= htmlspecialchars($denuncia['denunciado_nome']) ?> (<?= htmlspecialchars($denuncia['denunciado_email']) ?>) — perfil: <?= htmlspecialchars(ucfirst($denuncia['perfil_denunciado'])) ?></p>
            </div>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Motivo</h4>
                <p class="text-xs text-text-muted"><?= htmlspecialchars($motivosRotulo[$denuncia['motivo']] ?? ucfirst($denuncia['motivo'])) ?></p>
            </div>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Descrição do ocorrido</h4>
                <p class="text-xs text-text-muted whitespace-pre-line"><?= htmlspecialchars($denuncia['descricao']) ?></p>
            </div>
            <?php if (!empty($denuncia['solicitacao_id']) || !empty($denuncia['chat_id'])): ?>
                <div class="pt-3">
                    <h4 class="font-bold text-sm">Contexto vinculado</h4>
                    <p class="text-xs text-text-muted">
                        <?php if (!empty($denuncia['solicitacao_id'])): ?>Solicitação de adoção #<?= (int) $denuncia['solicitacao_id'] ?><?php endif; ?>
                        <?php if (!empty($denuncia['chat_id'])): ?> · Chat #<?= (int) $denuncia['chat_id'] ?><?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Data</h4>
                <p class="text-xs text-text-muted"><?= date('d/m/Y \à\s H:i', strtotime($denuncia['criado_em'])) ?></p>
            </div>
        </div>

        <?php if (!$finalizada): ?>
            <div class="space-y-3 pt-4 border-t border-cinzaMarrom/20">
                <?php if ($status === 'aberta'): ?>
                    <form method="POST" action="<?= $urlBase ?>/admin/denuncias/em-analise">
                        <input type="hidden" name="id" value="<?= (int) $denuncia['denuncia_id'] ?>">
                        <button type="submit" class="w-full py-2.5 bg-surface hover:bg-rosa-1/30 text-text-dark font-bold text-sm rounded-2xl border-2 border-preto transition">Colocar em Análise</button>
                    </form>
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <form id="form-reprovar-denuncia" method="POST" action="<?= $urlBase ?>/admin/denuncias/reprovar">
                        <input type="hidden" name="id" value="<?= (int) $denuncia['denuncia_id'] ?>">
                        <button type="button"
                                onclick="abrirModalConfirmacao('Reprovar denúncia', 'Reprovar e arquivar esta denúncia?', function () { document.getElementById('form-reprovar-denuncia').submit(); }, 'Reprovar', 'Cancelar')"
                                class="w-full py-3 bg-cinzaMarrom/40 hover:bg-cinzaMarrom/60 text-text-dark font-bold text-sm rounded-2xl border-2 border-preto transition">Reprovar / Arquivar</button>
                    </form>

                    <button type="button" onclick="document.getElementById('form-aprovar-denuncia').classList.toggle('hidden')" class="w-full py-3 bg-erro hover:opacity-90 text-white font-bold text-sm rounded-2xl border-2 border-preto transition">Aprovar e Aplicar Sanção</button>
                </div>

                <form id="form-aprovar-denuncia" method="POST" action="<?= $urlBase ?>/admin/denuncias/aprovar" class="hidden mt-2 p-4 bg-white dark:bg-preto1 border-2 border-preto dark:border-cinzaMarrom rounded-2xl space-y-3">
                    <input type="hidden" name="id" value="<?= (int) $denuncia['denuncia_id'] ?>">

                    <div>
                        <label class="block text-xs font-bold mb-1">Peso da advertência</label>
                        <select name="peso_status" class="input-padrao text-sm w-full">
                            <option value="leve">Leve</option>
                            <option value="media">Média</option>
                            <option value="grave">Grave</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1">Válida até (opcional)</label>
                        <input type="date" name="data_fim" class="input-padrao text-sm w-full">
                    </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="bloquear_conta" value="1" class="w-4 h-4">
                        <span class="text-xs font-bold">Bloquear a conta do denunciado imediatamente (RN 14/15)</span>
                    </label>

                    <button type="submit" class="w-full py-2.5 bg-erro text-white font-bold text-sm rounded-xl hover:opacity-90 transition">Confirmar Aprovação</button>
                </form>
            </div>
        <?php else: ?>
            <?php
                $mensagens = [
                    'aprovada'  => ['texto' => 'Esta denúncia foi aprovada e a sanção foi aplicada.', 'classe' => 'bg-sucesso/10 border-sucesso/30 text-sucesso'],
                    'reprovada' => ['texto' => 'Esta denúncia foi reprovada/arquivada.', 'classe' => 'bg-cinzaMarrom/10 border-cinzaMarrom/30 text-text-muted'],
                    'arquivada' => ['texto' => 'Esta denúncia foi arquivada.', 'classe' => 'bg-cinzaMarrom/10 border-cinzaMarrom/30 text-text-muted'],
                ];
                $msg = $mensagens[$status] ?? ['texto' => 'Esta denúncia já foi finalizada.', 'classe' => 'bg-cinzaMarrom/10 border-cinzaMarrom/30 text-text-muted'];
            ?>
            <div class="p-4 rounded-2xl text-center border <?= $msg['classe'] ?>">
                <p class="font-bold text-sm"><?= htmlspecialchars($msg['texto']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
