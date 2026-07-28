<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<div class="max-w-md mx-auto bg-background dark:bg-corFundo-escuro min-h-screen pb-20">
    
    <div class="bg-primary text-white py-4 px-6 flex items-center gap-4 rounded-b-[2rem] shadow-md">
        <a href="<?= URL_BASE ?>/perfil" class="text-2xl hover:scale-110 transition-transform">&larr;</a>
        <h1 class="font-shantell text-2xl font-bold">Alterar Foto de Perfil</h1>
    </div>

    <div class="px-6 mt-12 flex flex-col items-center">
        <form action="<?= URL_BASE ?>/perfil/atualizar-foto" method="POST" id="form-editar-foto" class="w-full flex flex-col items-center">
            
            <input type="hidden" name="foto_cortada" id="foto_cortada_base64">

              <!-- FOTO DE PERFIL COM ÍCONE DE EDITAR -->
        <div class="relative mb-4">
            <div class="w-40 h-40 rounded-full border-[6px] border-roxinhoFofo/60 overflow-hidden bg-white flex items-center justify-center shadow-md">
                <?php 
                    $srcFoto = !empty($fotoPerfil) ? URL_BASE . '/' . $fotoPerfil : URL_BASE . '/assets/img/perfil-placeholder.png';
                ?>
                <img src="<?= $srcFoto ?>" alt="Foto de perfil" class="w-full h-full object-cover">
            </div>
            <a href="<?= URL_BASE ?>/perfil/editar-foto" class="absolute bottom-1 right-0 bg-white p-2 rounded-full shadow border border-gray-200 text-gray-600 hover:bg-gray-50">
                ✏️
            </a>
        </div>
            
            <input type="file" id="input-arquivo-original" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropper(event)">

            <div class="w-full space-y-3 mt-4">
                <button type="submit" class="btn-primario w-full">
                    Salvar Nova Foto
                </button>
                <a href="<?= URL_BASE ?>/perfil" class="btn-secundario w-full block text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CROPPER -->
<div id="modal-cropper" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 class="font-shantell text-xl font-bold mb-1 text-gray-800">Ajustar Foto</h3>
        <p class="text-xs text-gray-500 mb-4 text-center">Enquadre sua foto no círculo perfeitamente.</p>
        
        <div class="w-full h-64 bg-gray-100 rounded-2xl overflow-hidden mb-4 flex items-center justify-center">
            <img id="imagem-para-cortar" src="" alt="Cortar">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropper()" class="flex-1 bg-gray-200 text-gray-700 py-2.5 rounded-xl font-bold text-sm">Cancelar</button>
            <button type="button" onclick="salvarRecorte()" class="flex-1 bg-primary text-white py-2.5 rounded-xl font-bold text-sm">Aplicar</button>
        </div>
    </div>
</div>

<!-- Cropper.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let cropper = null;

    function abrirSeletorFoto() {
        document.getElementById('input-arquivo-original').click();
    }

    function iniciarCropper(event) {
        const files = event.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            if (file.size > 5 * 1024 * 1024) {
                mostrarModalFeedback('erro', 'A imagem é muito grande. Máximo de 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const imagemElement = document.getElementById('imagem-para-cortar');
                imagemElement.src = e.target.result;
                document.getElementById('modal-cropper').classList.remove('hidden');

                if (cropper) cropper.destroy();

                cropper = new Cropper(imagemElement, {
                    aspectRatio: 1 / 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                });
            };
            reader.readAsDataURL(file);
        }
    }

    function fecharModalCropper() {
        document.getElementById('modal-cropper').classList.add('hidden');
        if (cropper) { cropper.destroy(); cropper = null; }
        document.getElementById('input-arquivo-original').value = '';
    }

    function salvarRecorte() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
        const base64String = canvas.toDataURL('image/png');

        document.getElementById('preview-foto').src = base64String;
        document.getElementById('foto_cortada_base64').value = base64String;
        fecharModalCropper();
    }

    document.getElementById('form-editar-foto').addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');

        btn.disabled = true;
        btn.innerHTML = 'Salvando...';

        try {
            const res = await fetch(form.action, { method: 'POST', body: formData });
            const result = await res.json();

            if (result.status === 'erro') {
                btn.disabled = false;
                btn.innerHTML = 'Salvar Nova Foto';
                mostrarModalFeedback('erro', result.mensagem);
            } else {
                mostrarModalFeedback('sucesso', result.mensagem);
                setTimeout(() => { window.location.href = result.redirect_url; }, 1500);
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = 'Salvar Nova Foto';
            mostrarModalFeedback('erro', 'Erro de conexão.');
        }
    });
</script>

<style>
    .cropper-view-box, .cropper-face { border-radius: 50%; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>