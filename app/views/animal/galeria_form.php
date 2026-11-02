<?php $fotosGaleria = $fotosGaleria ?? []; ?>
<section id="galeria-animal" class="w-full rounded-2xl border border-rosa-2 p-4 bg-surface text-text-dark dark:text-white" data-total="<?= count($fotosGaleria) ?>">
    <h2 class="font-shantell text-xl font-bold">Mais fotos do animal</h2>
    <p class="text-sm mb-3">Até 10 fotos no total. Selecione várias e ajuste o recorte de cada uma. A foto principal é escolhida no campo acima.</p>
    <?php if ($fotosGaleria): ?>
    <div class="grid grid-cols-3 gap-2 mb-3">
        <?php foreach ($fotosGaleria as $foto): $urlFoto = preg_match('#^https?://#i', $foto['caminho_foto']) ? $foto['caminho_foto'] : URL_BASE . '/' . ltrim($foto['caminho_foto'], '/'); ?>
        <figure><img src="<?= e($urlFoto) ?>" alt="Foto cadastrada do animal" class="w-full rounded-xl object-cover aspect-[4/5]"><figcaption class="text-xs"><?= $foto['foto_principal'] ? 'Principal atual' : 'Já cadastrada' ?></figcaption></figure>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <label for="fotos-galeria-arquivos" class="block font-semibold mb-2">Adicionar fotos com recorte</label>
    <input id="fotos-galeria-arquivos" type="file" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
    <p id="galeria-aviso" role="status" class="text-sm mt-2"></p>
    <div id="galeria-previas" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3"></div>
    <div id="galeria-inputs">
        <?php foreach (array_slice(is_array($old['fotos_galeria'] ?? null) ? $old['fotos_galeria'] : [], 0, 9) as $fotoPendente): ?>
        <?php if (is_string($fotoPendente) && strlen($fotoPendente) <= 1400000 && str_starts_with($fotoPendente, 'data:image/')): ?>
        <input type="hidden" name="fotos_galeria[]" value="<?= e($fotoPendente) ?>">
        <?php endif; endforeach; ?>
    </div>
</section>
<dialog id="galeria-recorte" class="rounded-3xl p-5 w-full max-w-md bg-white text-gray-900 dark:bg-surface dark:text-white shadow-2xl" style="max-height:90vh">
    <h2 class="font-shantell text-xl font-bold mb-2">Recortar foto</h2>
    <p class="text-sm mb-3">Arraste a foto e use o zoom para ajustar.</p>
    <div style="height:320px;max-height:50vh"><img id="galeria-imagem" alt="Foto para recortar" style="display:block;max-width:100%;max-height:100%"></div>
    <div class="flex gap-3 mt-4">
        <button type="button" id="galeria-pular" class="btn-secundario flex-1">Pular foto</button>
        <button type="button" id="galeria-confirmar" class="btn-primario flex-1">Usar recorte</button>
    </div>
</dialog>
<script src="<?= e(asset('assets/js/animal-galeria.js')) ?>" defer></script>
