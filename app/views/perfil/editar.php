<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="max-w-md mx-auto bg-background dark:bg-corFundo-escuro min-h-screen pb-20">

    <div class="bg-primary text-white py-4 px-6 flex items-center gap-4 rounded-b-[2rem] shadow-md">
        <a href="<?= URL_BASE ?>/perfil" class="text-2xl hover:scale-110 transition-transform">&larr;</a>
        <h1 class="font-shantell text-2xl font-bold">Editar Perfil</h1>
    </div>

    <div class="px-6 mt-6">
        <form action="<?= URL_BASE ?>/perfil/atualizar" method="POST" enctype="multipart/form-data" class="space-y-4" id="form-editar-perfil">

            <!-- FOTO DE PERFIL COM TRIGGER PARA A MODAL -->
            <div class="flex flex-col items-center mb-6">
                <!-- Input hidden que receberá a string Base64 da imagem cortada -->
                <input type="hidden" name="foto_cortada" id="foto_cortada_base64">
                <input type="hidden" name="foto_atual" value="<?= htmlspecialchars($especifico['foto_perfil'] ?? '') ?>">

                <div class="relative cursor-pointer group" onclick="abrirSeletorFoto()">
                    <div class="w-32 h-32 rounded-full border-4 border-roxinhoFofo overflow-hidden bg-gray-200 flex items-center justify-center shadow">
                        <?php
                        $fotoSrc = !empty($especifico['foto_perfil']) ? URL_BASE . '/' . $especifico['foto_perfil'] : URL_BASE . '/assets/img/perfil-placeholder.png';
                        ?>
                        <img src="<?= $fotoSrc ?>" id="preview-foto" alt="Sua foto" class="w-full h-full object-cover">
                    </div>
                    <!-- Ícone de lápis flutuante -->
                    <div class="absolute bottom-1 right-1 bg-white p-2 rounded-full shadow border text-gray-700 group-hover:bg-gray-50">
                        ✏️
                    </div>
                </div>
                <span class="text-xs text-text-muted mt-2">Clique na foto ou no lápis para ajustar</span>
                <!-- Input file real (fica oculto, acionado pelo JS) -->
                <input type="file" id="input-arquivo-original" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropper(event)">
            </div>

            <!-- NOME / NOME FANTASIA -->
            <div>
                <label class="label-padrao"><?= ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') ? 'Nome da Instituição / Fantasia' : 'Nome Completo' ?></label>
                <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required class="input-padrao">
            </div>

            <?php if ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor'): ?>
                <div>
                    <label class="label-padrao">Nome Fantasia</label>
                    <input type="text" name="nome_fantasia" value="<?= htmlspecialchars($especifico['nome_fantasia'] ?? '') ?>" class="input-padrao">
                </div>
                <div>
                    <label class="label-padrao">CNPJ / CPF do Responsável (A alteração inativa a conta temporariamente para revalidação)</label>
                    <input type="text" name="codigo_documento" value="<?= htmlspecialchars($especifico['codigo_documento'] ?? '') ?>" required class="input-padrao">
                </div>
                <div>
                    <label class="label-padrao">Atualizar Comprovante do Documento</label>
                    <input type="hidden" name="comprovante_atual" value="<?= htmlspecialchars($especifico['comprovante_documento'] ?? '') ?>">
                    <input type="file" name="comprovante_documento" class="input-padrao bg-white text-xs py-2">
                </div>
            <?php endif; ?>

            <!-- E-MAIL E SENHA -->
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="label-padrao">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required class="input-padrao">
                </div>
                <div>
                    <label class="label-padrao">Nova Senha (Deixe em branco para não alterar)</label>
                    <input type="password" name="senha" placeholder="********" class="input-padrao">
                </div>
            </div>

            <!-- TELEFONE -->
            <div>
                <label class="label-padrao">Telefone / WhatsApp</label>
                <input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" class="input-padrao">
            </div>

          <!-- ================= DADOS DE ENDEREÇO E NASCIMENTO (COMUNS PARA TODOS) ================= -->
            <div class="bg-white p-4 rounded-2xl shadow-sm space-y-3 mt-4">
                <h3 class="font-bold text-lg border-b pb-2 mb-2">Informações Pessoais e Endereço</h3>

                <!-- DATA DE NASCIMENTO (BLOQUEADA / SOMENTE LEITURA) -->
                <div>
                    <label class="label-padrao">Data de Nascimento (Não pode ser alterada)</label>
                    <input type="date" value="<?= htmlspecialchars($usuario['dt_nasc'] ?? '') ?>" disabled class="input-padrao bg-gray-100 text-gray-500 cursor-not-allowed">
                </div>

                <!-- NÚMERO DA MORADA E COMPLEMENTO -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="label-padrao">Número</label>
                        <input type="text" name="num_morada" value="<?= htmlspecialchars($usuario['num_morada'] ?? '') ?>" placeholder="S/N" required class="input-padrao">
                    </div>
                    <div class="col-span-2">
                        <label class="label-padrao">Complemento / Obs</label>
                        <input type="text" name="obs_casa" value="<?= htmlspecialchars($usuario['obs_casa'] ?? '') ?>" placeholder="Ex: Apto 302, Bloco B" class="input-padrao">
                    </div>
                </div>
            </div>

            <!-- TIPO DE MORADIA E ESPAÇO (Apenas para Tutores) -->
            <?php if ($tipoPerfil === 'tutor' || $tipoPerfil === 'usuario'): ?>
                <div class="bg-white p-4 rounded-2xl shadow-sm space-y-3 mt-4">
                    <h3 class="font-bold text-lg border-b pb-2 mb-2">Sua Casa e Rotina</h3>

                    <div>
                        <label class="label-padrao">Tipo de Moradia</label>
                        <select name="tipo_morada" class="input-padrao bg-white">
                            <option value="casa" <?= (($especifico['tipo_morada'] ?? '') === 'casa') ? 'selected' : '' ?>>Casa</option>
                            <option value="apartamento" <?= (($especifico['tipo_morada'] ?? '') === 'apartamento') ? 'selected' : '' ?>>Apartamento</option>
                            <option value="sitio" <?= (($especifico['tipo_morada'] ?? '') === 'sitio') ? 'selected' : '' ?>>Sítio / Chácara</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-padrao">Tamanho do Espaço Interno</label>
                        <select name="tamanho_interno_morada" class="input-padrao bg-white">
                            <option value="pequeno" <?= (($especifico['tamanho_interno_morada'] ?? '') === 'pequeno') ? 'selected' : '' ?>>Pequeno</option>
                            <option value="medio" <?= (($especifico['tamanho_interno_morada'] ?? '') === 'medio') ? 'selected' : '' ?>>Médio</option>
                            <option value="grande" <?= (($especifico['tamanho_interno_morada'] ?? '') === 'grande') ? 'selected' : '' ?>>Grande</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label-padrao">Crianças em casa?</label>
                            <select name="possui_criancas" class="input-padrao bg-white">
                                <option value="sim" <?= (($especifico['possui_criancas'] ?? '') === 'sim') ? 'selected' : '' ?>>Sim</option>
                                <option value="nao" <?= (($especifico['possui_criancas'] ?? '') === 'nao') ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-padrao">Outros pets?</label>
                            <select name="possui_outros_pets" class="input-padrao bg-white">
                                <option value="sim" <?= (($especifico['possui_outros_pets'] ?? '') === 'sim') ? 'selected' : '' ?>>Sim</option>
                                <option value="nao" <?= (($especifico['possui_outros_pets'] ?? '') === 'nao') ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- REGIÃO / BAIRRO -->
            <div>
                <label class="label-padrao">Bairro (Região)</label>
                <select name="regiao_id" class="input-padrao bg-white">
                    <option value="">Selecione sua região</option>
                    <?php foreach ($regioes as $regiao): ?>
                        <option value="<?= $regiao['regiao_id'] ?>" <?= ($usuario['regiao_id'] == $regiao['regiao_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($regiao['nome_regiao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primario w-full mt-4">
                Salvar Alterações
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL DO CROPPER.JS ================= -->
<div id="modal-cropper" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 class="font-shantell text-xl font-bold mb-1 text-gray-800">Ajustar Foto de Perfil</h3>
        <p class="text-xs text-gray-500 mb-4 text-center">Arraste para posicionar e use o zoom. Resolução recomendada: <b>500x500px</b></p>

        <!-- Container onde a imagem vai carregar para corte -->
        <div class="w-full h-64 bg-gray-100 rounded-2xl overflow-hidden mb-4 flex items-center justify-center">
            <img id="imagem-para-cortar" src="" alt="Cortar" class="max-block max-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropper()" class="flex-1 bg-gray-200 text-gray-700 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-300 transition">
                Cancelar
            </button>
            <button type="button" onclick="salvarRecorte()" class="flex-1 bg-primary text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">
                Aplicar Foto
            </button>
        </div>
    </div>
</div>

<!-- SCRIPTS DO CROPPER E AJAX -->
<script>
    let cropper = null;

    function abrirSeletorFoto() {
        document.getElementById('input-arquivo-original').click();
    }

    function iniciarCropper(event) {
        const files = event.target.files;
        if (files && files.length > 0) {
            const file = files[0];

            // Validação de tamanho (ex: max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                mostrarModalFeedback('erro', 'A imagem é muito grande. Escolha uma de até 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const imagemElement = document.getElementById('imagem-para-cortar');
                imagemElement.src = e.target.result;

                // Abre a modal
                document.getElementById('modal-cropper').classList.remove('hidden');

                // Destrói instância anterior do cropper se existir
                if (cropper) {
                    cropper.destroy();
                }

                // Inicializa o Cropper.js com formato circular restrito
                cropper = new Cropper(imagemElement, {
                    aspectRatio: 1 / 1, // Quadrado perfeito para o círculo
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: false,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            reader.readAsDataURL(file);
        }
    }

    function fecharModalCropper() {
        document.getElementById('modal-cropper').classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        document.getElementById('input-arquivo-original').value = '';
    }

    function salvarRecorte() {
        if (!cropper) return;

        // Pega a área cortada em formato circular/quadrado compactado em PNG Base64
        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
        });

        const base64String = canvas.toDataURL('image/png');

        // Mostra a foto cortada na pré-visualização da tela de perfil
        document.getElementById('preview-foto').src = base64String;

        // Injeta a string base64 no input hidden para enviar via POST no form
        document.getElementById('foto_cortada_base64').value = base64String;

        fecharModalCropper();
    }

    // Interceptação AJAX do Formulário Geral
    document.getElementById('form-editar-perfil').addEventListener('submit', async function(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const btnSubmit = form.querySelector('button[type="submit"]');
        const btnTextoOriginal = btnSubmit.innerHTML;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Salvando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'erro') {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;
                mostrarModalFeedback('erro', result.mensagem);
            } else if (result.status === 'sucesso') {
                if (typeof limparAutoSave === 'function') limparAutoSave();

                mostrarModalFeedback('sucesso', result.mensagem);
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 1500);
            }
        } catch (error) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = btnTextoOriginal;
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        }
    });
</script>

<!-- Estilo customizado para deixar a máscara do Cropper redonda se desejar -->
<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>