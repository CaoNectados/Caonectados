<?php
$tracosSelecionados = isset($old['tracos_presentes'])
    ? (is_array($old['tracos'] ?? []) ? $old['tracos'] ?? [] : [])
    : ($animal ?? null)?->getTracoIds() ?? [];
$tracosSelecionados = array_map('strval', $tracosSelecionados);
?>
<fieldset class="min-w-0 rounded-2xl border border-rosa-2 bg-surface p-4 sm:p-5 dark:border-white/30" aria-describedby="ajuda-tracos">
    <legend class="px-2 font-poppins font-bold text-text-dark">Características do animal</legend>
    <p id="ajuda-tracos" class="mb-3 text-sm text-text-dark">Marque todas as características que você conhece. Deixe em branco o que não souber.</p>
    <input type="hidden" name="tracos_presentes" value="1">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <?php foreach ($tracosDisponiveis as $traco): ?>
        <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-primary/25 bg-background p-3 text-sm text-text-dark hover:border-primary focus-within:ring-2 focus-within:ring-primary dark:border-white/30">
            <input type="checkbox" name="tracos[]" value="<?= (int) $traco['traco_id'] ?>" <?= in_array((string) $traco['traco_id'], $tracosSelecionados, true) ? 'checked' : '' ?> class="h-5 w-5 shrink-0 accent-primary">
            <span class="min-w-0 break-words"><?= e($traco['traco']) ?></span>
        </label>
        <?php endforeach; ?>
    </div>
</fieldset>
