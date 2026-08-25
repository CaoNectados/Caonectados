<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$pagina = $pagina ?? [];
$redes = $redes ?? [];
$protetorId = (int) ($_SESSION['protetor_id'] ?? 0);

$montarUrlFoto = function (?string $caminho) use ($urlBase): string {
    if (empty($caminho)) {
        return '';
    }
    return strpos($caminho, 'http') === 0 ? $caminho : $urlBase . '/' . ltrim($caminho, '/');
};

$fotoPerfilUrl = $montarUrlFoto($pagina['foto_perfil'] ?? null);
$fotoFundoUrl = $montarUrlFoto($pagina['foto_fundo'] ?? null);

$labelsRede = ['instagram' => 'Instagram', 'facebook' => 'Facebook', 'whatsapp' => 'WhatsApp', 'outro' => 'Outro'];
$iconesRede = ['instagram' => '📷', 'facebook' => '📘', 'whatsapp' => '💬', 'outro' => '🔗'];
$tiposJaUsados = array_column($redes, 'tipo_rede');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<style>
    #modal-cropper-pagina.cropper-redondo .cropper-view-box,
    #modal-cropper-pagina.cropper-redondo .cropper-face {
        border-radius: 50%;
    }
</style>

<div class="max-w-2xl mx-auto pb-16 px-4 sm:px-6">
    <div class="flex items-center justify-between pt-6 mb-6">
        <div>
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minha Página</h1>
            <p class="text-xs text-text-muted">Como sua ONG/Protetor aparece para os adotantes</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <?php if ($protetorId > 0): ?>
                <a href="<?= $urlBase ?>/pagina?id=<?= $protetorId ?>" target="_blank" class="text-xs font-bold text-primary dark:text-roxinhoFofo underline">Ver página pública</a>
            <?php endif; ?>
            <a href="<?= $urlBase ?>/perfil" class="text-xs font-bold text-text-muted underline hover:text-text-dark dark:hover:text-white">&larr; Voltar</a>
        </div>
    </div>

    <form action="<?= $urlBase ?>/pagina-perfil/atualizar" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="foto_perfil_cortada" id="foto_perfil_cortada">
        <input type="hidden" name="foto_fundo_cortada" id="foto_fundo_cortada">

        <!-- Preview estilo "capa + avatar sobreposto", igual à página pública -->
        <div class="card-padrao p-0 overflow-hidden">
            <div class="relative h-32 sm:h-40 bg-gradient-to-r from-roxoApagado to-rosa-2 dark:from-preto2 dark:to-preto3 cursor-pointer group"
                 onclick="document.getElementById('input-arquivo-fundo').click()">
                <img id="preview-foto-fundo" src="<?= htmlspecialchars($fotoFundoUrl) ?>" alt="Capa" class="w-full h-full object-cover <?= empty($fotoFundoUrl) ? 'hidden' : '' ?>">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                    <span class="opacity-0 group-hover:opacity-100 text-white text-xs font-bold transition">✏️ Trocar capa</span>
                </div>
            </div>
            <input type="file" id="input-arquivo-fundo" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="iniciarCropperPagina(event, 'fundo')">

            <div class="flex flex-col items-center -mt-12 pb-6 px-6">
                <div class="w-24 h-24 rounded-full border-4 border-branco dark:border-preto1 bg-surface dark:bg-preto2 overflow-hidden shadow-md cursor-pointer relative group"
                     onclick="document.getElementById('input-arquivo-perfil').click()">
                    <img id="preview-foto-perfil" src="<?= htmlspecialchars($fotoPerfilUrl ?: $urlBase . '/assets/img/perfil-placeholder.png') ?>" alt="Foto de perfil" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                        <span class="opacity-0 group-hover:opacity-100 text-white text-lg transition">✏️</span>
                    </div>
                </div>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="iniciarCropperPagina(event, 'perfil')">
                <p class="text-xs text-text-muted mt-2">Toque nas imagens para trocar</p>
            </div>
        </div>

        <!-- Descrição e PIX (UC 19.1 / UC 19.2) -->
        <div class="card-padrao space-y-4">
            <div>
                <label for="descricao" class="label-padrao">Sobre a ONG/Protetor</label>
                <textarea name="descricao" id="descricao" rows="4" maxlength="1000" class="input-padrao" placeholder="Conte um pouco sobre o seu trabalho..."><?= htmlspecialchars($pagina['descricao'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="chave_pix" class="label-padrao">Chave PIX para doações (opcional)</label>
                <input type="text" name="chave_pix" id="chave_pix" maxlength="255" value="<?= htmlspecialchars($pagina['chave_pix'] ?? '') ?>" class="input-padrao" placeholder="E-mail, CPF/CNPJ, telefone ou chave aleatória">
            </div>
            <button type="submit" class="btn-primario w-full justify-center">Salvar Alterações</button>
        </div>
    </form>

    <!-- Redes Sociais (UC 19.2) -->
    <div class="card-padrao mt-6 space-y-4">
        <h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white">Redes Sociais e Contato</h2>

        <?php if (empty($redes)): ?>
            <p class="text-sm text-text-muted italic">Nenhuma rede social cadastrada ainda.</p>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($redes as $rede): ?>
                    <li class="flex items-center justify-between gap-3 bg-branco dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-xl px-4 py-2.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <span><?= $iconesRede[$rede['tipo_rede']] ?? '🔗' ?></span>
                            <span class="text-xs font-bold text-text-dark dark:text-white shrink-0"><?= htmlspecialchars($labelsRede[$rede['tipo_rede']] ?? ucfirst($rede['tipo_rede'])) ?>:</span>
                            <a href="<?= htmlspecialchars($rede['link_rede']) ?>" target="_blank" rel="noopener" class="text-xs text-primary dark:text-roxinhoFofo underline truncate"><?= htmlspecialchars($rede['link_rede']) ?></a>
                        </div>
                        <form action="<?= $urlBase ?>/pagina-perfil/rede/remover" method="POST" onsubmit="return confirm('Remover esta rede social?');" class="shrink-0">
                            <input type="hidden" name="rede_id" value="<?= (int) $rede['rede_id'] ?>">
                            <button type="submit" class="text-erro text-xs font-bold hover:underline">Remover</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="<?= $urlBase ?>/pagina-perfil/rede/adicionar" method="POST" class="flex flex-wrap gap-2 pt-2 border-t border-cinzaMarrom/15">
            <select name="tipo_rede" class="input-padrao flex-1 min-w-[120px]" required>
                <?php foreach ($labelsRede as $valor => $rotulo): ?>
                    <option value="<?= $valor ?>"><?= $rotulo ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="link_rede" placeholder="Link ou contato..." required class="input-padrao flex-[2] min-w-[180px]">
            <button type="submit" class="btn-secundario">+ Adicionar</button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- MODAL CROPPER (mesmo padrão de onboarding/protetor_onboarding.php: 1:1 pra perfil, 16:9 pra capa) -->
<div id="modal-cropper-pagina" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface dark:bg-preto1 rounded-3xl max-w-md w-full p-6 flex flex-col items-center shadow-2xl border border-rosa-3">
        <h3 id="titulo-cropper-pagina" class="font-shantell text-xl font-bold mb-1 text-text-dark dark:text-white">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Arraste e use o zoom para centralizar.</p>

        <div class="w-full h-64 bg-surface dark:bg-preto2 rounded-2xl overflow-hidden mb-4 flex items-center justify-center border border-cinzaMarrom/30">
            <img id="imagem-para-cortar-pagina" src="" alt="Cortar" class="max-w-full max-h-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropperPagina()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
            <button type="button" onclick="salvarRecortePagina()" class="flex-1 btn-primario py-2.5 rounded-xl font-bold text-sm">Aplicar</button>
        </div>
    </div>
</div>

<script>
    let cropperPagina = null;
    let alvoCropperPagina = null;

    function iniciarCropperPagina(event, alvo) {
        alvoCropperPagina = alvo;
        const fileInput = event.target;
        if (!fileInput.files || !fileInput.files.length) return;

        const limiteMB = (alvo === 'perfil') ? 2 : 3;
        if (typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarTamanhoArquivo(fileInput, limiteMB)) {
            if (typeof mostrarModalFeedback === 'function') {
                mostrarModalFeedback('erro', `A imagem é muito grande. Escolha uma de até ${limiteMB}MB.`);
            } else {
                alert(`A imagem é muito grande. Escolha uma de até ${limiteMB}MB.`);
            }
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const imgModal = document.getElementById('imagem-para-cortar-pagina');
            imgModal.src = e.target.result;

            const isPerfil = (alvo === 'perfil');
            document.getElementById('titulo-cropper-pagina').innerText = isPerfil ? 'Ajustar Foto de Perfil' : 'Ajustar Foto de Capa';
            document.getElementById('modal-cropper-pagina').classList.remove('hidden');
            document.getElementById('modal-cropper-pagina').classList.toggle('cropper-redondo', isPerfil);

            if (cropperPagina) cropperPagina.destroy();
            cropperPagina = new Cropper(imgModal, {
                aspectRatio: isPerfil ? 1 / 1 : 16 / 9,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9
            });
        };
        reader.readAsDataURL(fileInput.files[0]);
    }

    function fecharModalCropperPagina() {
        document.getElementById('modal-cropper-pagina').classList.add('hidden');
        if (cropperPagina) { cropperPagina.destroy(); cropperPagina = null; }
        document.getElementById('input-arquivo-perfil').value = '';
        document.getElementById('input-arquivo-fundo').value = '';
    }

    function salvarRecortePagina() {
        if (!cropperPagina) return;
        const isPerfil = (alvoCropperPagina === 'perfil');
        const options = isPerfil ? { width: 400, height: 400 } : { width: 1200, height: 675 };
        const base64String = cropperPagina.getCroppedCanvas(options).toDataURL('image/png');

        if (isPerfil) {
            document.getElementById('preview-foto-perfil').src = base64String;
            document.getElementById('foto_perfil_cortada').value = base64String;
        } else {
            const previewFundo = document.getElementById('preview-foto-fundo');
            previewFundo.src = base64String;
            previewFundo.classList.remove('hidden');
            document.getElementById('foto_fundo_cortada').value = base64String;
        }
        fecharModalCropperPagina();
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
