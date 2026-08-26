<?php
$contestacao = $contestacao ?? [];
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$pesoRotulo = ['leve' => 'Leve', 'media' => 'Média', 'grave' => 'Grave'];
$pendente = $contestacao['status'] === 'pendente';

$caminhoAnexo = null;
if (!empty($contestacao['anexo'])) {
    $caminhoAnexo = $urlBase . '/' . ltrim($contestacao['anexo'], '/');
}
?>

<div class="min-h-screen bg-background text-text-dark p-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-2xl bg-surface rounded-3xl md:rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20">

        <div class="flex items-center justify-between gap-4 mb-6 pb-2 border-b border-cinzaMarrom/20">
            <h2 class="font-shantell text-2xl font-bold text-text-dark">Detalhes da Contestação</h2>
            <a href="<?= $urlBase ?>/admin/contestacoes" class="px-4 py-2 border-2 border-preto rounded-xl text-xs font-bold text-text-dark hover:bg-preto hover:text-white transition">Voltar &rsaquo;</a>
        </div>

        <div class="space-y-4 divide-y divide-cinzaMarrom/20 mb-8">
            <div class="pt-3 first:pt-0">
                <h4 class="font-bold text-sm">Usuário</h4>
                <p class="text-xs text-text-muted"><?= htmlspecialchars($contestacao['usuario_nome']) ?> (<?= htmlspecialchars($contestacao['usuario_email']) ?>)</p>
            </div>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Advertência contestada</h4>
                <p class="text-xs text-text-muted">Peso <?= htmlspecialchars($pesoRotulo[$contestacao['peso_status']] ?? $contestacao['peso_status']) ?> — perfil <?= htmlspecialchars(ucfirst($contestacao['perfil_afetado'])) ?> — status atual: <?= htmlspecialchars(ucfirst($contestacao['advertencia_status'])) ?></p>
            </div>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Justificativa do usuário</h4>
                <p class="text-xs text-text-muted whitespace-pre-line"><?= htmlspecialchars($contestacao['justificativa']) ?></p>
            </div>
            <?php if ($caminhoAnexo): ?>
                <div class="pt-3">
                    <h4 class="font-bold text-sm">Anexo de prova</h4>
                    <a href="<?= htmlspecialchars($caminhoAnexo) ?>" target="_blank" rel="noopener" class="text-xs font-bold text-primary underline hover:opacity-80">Abrir anexo em nova aba</a>
                </div>
            <?php endif; ?>
            <div class="pt-3">
                <h4 class="font-bold text-sm">Data</h4>
                <p class="text-xs text-text-muted"><?= date('d/m/Y \à\s H:i', strtotime($contestacao['data_hora'])) ?></p>
            </div>
            <?php if (!empty($contestacao['parecer_admin'])): ?>
                <div class="pt-3">
                    <h4 class="font-bold text-sm">Parecer já registrado</h4>
                    <p class="text-xs text-text-muted whitespace-pre-line"><?= htmlspecialchars($contestacao['parecer_admin']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($pendente): ?>
            <form method="POST" action="<?= $urlBase ?>/admin/contestacoes/decidir" class="pt-4 border-t border-cinzaMarrom/20 space-y-3">
                <input type="hidden" name="id" value="<?= (int) $contestacao['contestacao_id'] ?>">

                <div>
                    <label for="parecer" class="block text-xs font-bold mb-1">Parecer administrativo</label>
                    <textarea id="parecer" name="parecer" rows="3" required
                              placeholder="Explique a decisão — o usuário verá este texto."
                              class="input-padrao text-sm w-full"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button type="submit" name="decisao" value="reprovar"
                            class="w-full py-3 bg-cinzaMarrom/40 hover:bg-cinzaMarrom/60 text-text-dark font-bold text-sm rounded-2xl border-2 border-preto transition">
                        Reprovar (mantém a penalidade)
                    </button>
                    <button type="submit" name="decisao" value="aprovar"
                            class="w-full py-3 bg-sucesso hover:opacity-90 text-white font-bold text-sm rounded-2xl border-2 border-preto transition">
                        Aprovar (encerra a penalidade)
                    </button>
                </div>
            </form>
        <?php else: ?>
            <?php
                $msg = $contestacao['status'] === 'aprovada'
                    ? ['texto' => 'Contestação aprovada — a penalidade foi encerrada.', 'classe' => 'bg-sucesso/10 border-sucesso/30 text-sucesso']
                    : ['texto' => 'Contestação reprovada — a penalidade original foi mantida.', 'classe' => 'bg-erro/10 border-erro/30 text-erro'];
            ?>
            <div class="p-4 rounded-2xl text-center border <?= $msg['classe'] ?>">
                <p class="font-bold text-sm"><?= htmlspecialchars($msg['texto']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
