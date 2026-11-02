<?php
$fotosGaleria = $fotosGaleria ?? [];
$galeriaInicial = [];
foreach ($fotosGaleria as $foto) {
    $caminho = $foto['caminho_foto'];
    $galeriaInicial[] = ['id'=>(int)$foto['foto_id'], 'src'=>preg_match('#^https?://#i', $caminho) ? $caminho : URL_BASE . '/' . ltrim($caminho, '/'), 'principal'=>(bool)$foto['foto_principal']];
}
if (!empty($old['foto_principal_id'])) {
    foreach ($galeriaInicial as &$fotoInicial) $fotoInicial['principal'] = $fotoInicial['id'] === (int)$old['foto_principal_id'];
    unset($fotoInicial);
}
if (is_string($old['foto_cortada'] ?? null) && str_starts_with($old['foto_cortada'], 'data:image/')) {
    foreach ($galeriaInicial as &$fotoInicial) $fotoInicial['principal'] = false;
    unset($fotoInicial);
    $galeriaInicial[] = ['id'=>null,'src'=>$old['foto_cortada'],'principal'=>true];
}
foreach (array_slice(is_array($old['fotos_galeria'] ?? null) ? $old['fotos_galeria'] : [], 0, 9) as $fotoPendente) {
    if (is_string($fotoPendente) && strlen($fotoPendente) <= 1400000 && str_starts_with($fotoPendente, 'data:image/')) {
        $galeriaInicial[] = ['id'=>null,'src'=>$fotoPendente,'principal'=>false];
    }
}
?>
<section id="galeria-animal" class="pet-gallery" aria-label="Fotos do animal">
    <div class="pet-gallery-heading"><h2>Fotos do animal</h2><span id="galeria-contador">0 / 10</span></div>
    <p class="pet-gallery-help">Adicione as fotos no +, ajuste cada recorte e escolha a principal.</p>
    <div class="pet-gallery-stage">
        <div class="pet-gallery-hero">
            <img id="galeria-destaque" alt="Prévia da foto selecionada" hidden>
            <div id="galeria-vazia"><span aria-hidden="true">+</span><p>A primeira foto será a principal</p></div>
            <span id="galeria-badge" class="pet-gallery-badge" hidden>Principal</span>
            <div id="galeria-acoes" class="pet-gallery-actions" hidden>
                <button type="button" id="galeria-principal">Tornar principal</button>
                <button type="button" id="galeria-editar">Recortar</button>
                <button type="button" id="galeria-remover">Remover</button>
            </div>
        </div>
        <button type="button" id="galeria-adicionar" class="pet-gallery-add" aria-label="Adicionar fotos do animal"><span aria-hidden="true">+</span><span>Adicionar<br>fotos</span></button>
    </div>
    <div id="galeria-previas" class="pet-gallery-rail" aria-label="Fila de fotos"></div>
    <p id="galeria-aviso" class="pet-gallery-help" role="status" aria-live="polite"></p>
    <input id="fotos-galeria-arquivos" type="file" multiple accept="image/jpeg,image/png,image/webp" hidden>
    <input type="hidden" name="foto_cortada" id="foto_cortada_base64" value="">
    <input type="hidden" name="foto_principal_id" id="foto-principal-id" value="0">
    <div id="galeria-inputs"></div>
    <script type="application/json" id="galeria-inicial"><?= json_encode($galeriaInicial, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
</section>
<dialog id="galeria-recorte" class="pet-gallery-dialog" aria-labelledby="galeria-recorte-titulo">
    <h2 id="galeria-recorte-titulo">Ajustar foto</h2>
    <p id="galeria-recorte-posicao">Arraste a imagem ou use o zoom.</p>
    <div class="pet-gallery-crop"><img id="galeria-imagem" alt="Imagem para recortar"></div>
    <div class="pet-gallery-crop-actions"><button type="button" id="galeria-zoom-menos" aria-label="Diminuir zoom">−</button><button type="button" id="galeria-zoom-mais" aria-label="Aumentar zoom">+</button><button type="button" id="galeria-pular">Cancelar foto</button><button type="button" id="galeria-confirmar">Usar foto</button></div>
</dialog>
<link rel="stylesheet" href="<?= e(asset('assets/css/animal-galeria.css')) ?>">
<script src="<?= e(asset('assets/js/animal-galeria.js')) ?>" defer></script>
