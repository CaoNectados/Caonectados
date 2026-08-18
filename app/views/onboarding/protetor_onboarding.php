<?php
require_once __DIR__ . '/../templates/header.php';
$tipo_perfil = $tipo_perfil ?? 'cpf';
$isOng = ($tipo_perfil === 'cnpj');

$titulo_etapa1    = $isOng ? "Vamos validar sua ONG" : "Vamos validar seu perfil";
$texto_etapa1     = $isOng ? "Precisamos confirmar os dados da sua instituição." : "Precisamos confirmar seus dados como protetor independente.";
$label_nome       = $isOng ? "Nome da ONG *" : "Seu Nome (Protetor) *";
$placeholder_nome = $isOng ? "Ex: Ong Vida Animal" : "Ex: Maria da Silva";
$label_doc        = $isOng ? "Digite o CNPJ da ONG *" : "Digite o seu CPF *";
$placeholder_doc  = $isOng ? "00.000.000/0000-00" : "000.000.000-00";
$titulo_pagina    = $isOng ? "Página da ONG" : "Sua Página de Protetor";
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<form id="form-onboarding-protetor" action="<?= URL_BASE ?>/onboarding/salvar-protetor" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto p-4">

    <input type="hidden" name="tipo_documento" id="tipo_documento" value="<?= htmlspecialchars($tipo_perfil, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="foto_perfil_cortada" id="foto_perfil_cortada">
    <input type="hidden" name="foto_fundo_cortada" id="foto_fundo_cortada">

    <!-- BARRA DE PROGRESSO COM 6 ETAPAS -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-6" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR -->
    <button type="button" id="btn-voltar-global" onclick="OnboardingManager.voltarEtapa()" class="mb-4 text-2xl font-bold cursor-pointer transition hover:opacity-75" title="Voltar">
        &#129144;
    </button>

    <!-- ETAPA 1: Validação -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2"><?= $titulo_etapa1 ?></h1>
            <p class="text-sm text-gray-600">Para garantir a segurança dos nossos pets e adotantes, <?= $texto_etapa1 ?></p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="nome_fantasia" class="block font-medium mb-1"><?= $label_nome ?></label>
                <input type="text" name="nome_fantasia" id="nome_fantasia" placeholder="<?= $placeholder_nome ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>

            <div>
                <label for="cnpj_cpf" class="block font-medium mb-1"><?= $label_doc ?></label>
                <input type="text" name="cnpj_cpf" id="cnpj_cpf" placeholder="<?= $placeholder_doc ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
                <p id="erro-documento" class="text-xs text-red-500 mt-1 hidden">O documento informado é inválido.</p>
            </div>

            <?php if ($isOng): ?>
                <div>
                    <label for="data_abertura_cnpj" class="block font-medium mb-1">
                        Data de Abertura do CNPJ *
                    </label>
                    <input
                        type="date"
                        id="data_abertura_cnpj"
                        name="data_abertura_cnpj"
                        max="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                        value="<?= htmlspecialchars($old_input['data_abertura_cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none"
                    >
                </div>
            <?php else: ?>
                <div>
                    <label for="dt_nasc" class="block font-medium mb-1">
                        Data de Nascimento *
                    </label>
                    <input
                        type="date"
                        id="dt_nasc"
                        name="dt_nasc"
                        max="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                        value="<?= htmlspecialchars($old_input['dt_nasc'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none"
                    >
                </div>
            <?php endif; ?>

            <div>
                <label for="telefone" class="block font-medium mb-1">Telefone / WhatsApp *</label>
                <input type="tel" name="telefone" id="telefone" placeholder="(00) 00000-0000" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 2: Localização -->
    <div class="etapa-form hidden" id="etapa-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Selecione a sua localização</h1>
            <p class="text-sm text-gray-600">Onde fica a sua sede/atuação? Informe a localização para que adotantes da região encontrem seus animais.</p>
        </div>

        <div class="mb-4 relative text-left">
            <label for="input-busca-bairro" class="block font-medium mb-1">Pesquise seu Bairro / Região *</label>
            <!-- Adicionado 'name' para o AutoSave capturar -->
            <input type="text" name="busca_bairro_texto" id="input-busca-bairro" list="lista-regioes" placeholder="Digite o nome do seu bairro..." autocomplete="off" oninput="OnboardingManager.sincronizarRegiaoId()" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">

            <datalist id="lista-regioes">
                <?php if (!empty($regioes)): ?>
                    <?php foreach ($regioes as $regiao): ?>
                        <?php
                        $regId = is_array($regiao) ? $regiao['regiao_id'] : $regiao->getRegiaoId();
                        $regNome = is_array($regiao) ? $regiao['nome_regiao'] : $regiao->getNomeRegiao();
                        ?>
                        <option data-id="<?= $regId ?>" value="<?= e($regNome) ?>"></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </datalist>

            <input type="hidden" name="regiao_id" id="regiao_id_hidden">
            <p id="erro-bairro-invalido" class="text-xs text-red-500 mt-1 hidden">Selecione um bairro válido da lista fornecida.</p>
        </div>

        <div class="mb-4 text-left">
            <label for="obs_casa" class="block font-medium mb-1">Logradouro e Complemento *</label>
            <input type="text" name="obs_casa" id="obs_casa" placeholder="Ex: Avenida Brasil, Apto 42..." class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
        </div>

        <div class="mb-6 text-left">
            <label for="numero" class="block font-medium mb-1">Número do Local *</label>
            <input type="text" name="numero" id="numero" placeholder="Ex: 123, S/N" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 3: Dados da Página -->
    <div class="etapa-form hidden" id="etapa-3">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2"><?= $titulo_pagina ?></h1>
            <p class="text-sm text-gray-600">Agora, vamos configurar como você aparecerá para a comunidade.</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="descricao" class="block font-medium mb-1">Descrição / Causa *</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Mínimo de 15 caracteres apresentando a causa..." class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-300"></textarea>
            </div>

            <div>
                <label for="instagram" class="block font-medium mb-1">Link do Instagram</label>
                <input type="text" name="instagram" id="instagram" placeholder="https://instagram.com/seu_perfil" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>

            <div>
                <label for="facebook" class="block font-medium mb-1">Link do Facebook</label>
                <input type="text" name="facebook" id="facebook" placeholder="https://facebook.com/seu_perfil" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>

            <div>
                <label for="chave_pix" class="block font-medium mb-1">Chave PIX para doações</label>
                <input type="text" name="chave_pix" id="chave_pix" placeholder="E-mail, CNPJ, CPF, Celular ou Chave Aleatória" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 4: Fotos de Perfil e Capa (Com Cropper.js) -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold mb-2">Personalize sua página</h2>
            <p class="text-sm text-gray-600 mb-6">Adicione imagens para que a comunidade reconheça o seu trabalho.</p>

            <!-- Foto de Perfil -->
            <div class="mb-6">
                <label class="block font-medium mb-2 text-left">Foto de Perfil (Opcional)</label>
                <div class="flex justify-center mb-2">
                    <div class="w-32 h-32 rounded-full border-4 border-pink-300 bg-gray-100 flex items-center justify-center overflow-hidden relative shadow-sm cursor-pointer" onclick="document.getElementById('input-arquivo-perfil').click()">
                        <span id="foto-placeholder-perfil" class="text-gray-400 text-4xl font-bold">&#128247;</span>
                        <img id="preview-foto-perfil" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('input-arquivo-perfil').click()" class="inline-block bg-emerald-800 text-white font-medium py-2 px-6 rounded-lg cursor-pointer transition hover:bg-emerald-900 text-sm">
                    Ajustar Perfil
                </button>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperFoto(event, 'perfil')">
            </div>

            <hr class="my-4 border-gray-200">

            <!-- Foto de Capa / Fundo -->
            <div class="mb-2">
                <label class="block font-medium mb-1 text-left">Foto de Capa/Fundo (Opcional)</label>
                <p class="text-xs text-gray-500 mb-2 text-left">Essa imagem ficará no topo da sua página de perfil.</p>
                <div class="w-full h-36 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden relative mb-2 cursor-pointer" onclick="document.getElementById('input-arquivo-fundo').click()">
                    <span id="fundo-placeholder-capa" class="text-gray-400 text-sm">Preview da Capa</span>
                    <img id="preview-foto-fundo" src="" alt="Capa" class="w-full h-full object-cover hidden">
                </div>
                <button type="button" onclick="document.getElementById('input-arquivo-fundo').click()" class="inline-block bg-emerald-800 text-white font-medium py-2 px-6 rounded-lg cursor-pointer transition hover:bg-emerald-900 text-sm">
                    Ajustar Capa
                </button>
                <input type="file" id="input-arquivo-fundo" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperFoto(event, 'fundo')">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Comprovante de Atividade -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <div class="w-24 h-24 rounded-full border-4 border-gray-300 bg-gray-100 flex items-center justify-center shadow-sm">
                    <span class="text-gray-400 text-4xl font-bold">&#128193;</span>
                </div>
            </div>

            <h1 class="text-2xl font-bold mb-2">Comprove a sua atividade</h1>
            
            <!-- Textos condicionados para ONG ou Protetor Independente -->
            <?php if ($isOng): ?>
                <p class="text-sm text-gray-600 mb-6">Anexe o cartão CNPJ atualizado ou um documento de registro da prefeitura comprovando a existência da ONG.</p>
            <?php else: ?>
                <p class="text-sm text-gray-600 mb-6">Anexe uma foto de um documento de identidade válido com foto (como RG ou CNH).</p>
            <?php endif; ?>

            <div class="mb-4">
                <label for="comprovante_documento" class="inline-block bg-emerald-800 text-white font-medium py-2 px-6 rounded-lg cursor-pointer transition hover:bg-emerald-900">
                    Anexar documento
                </label>
                <input type="file" name="comprovante_documento" id="comprovante_documento" accept=".pdf, .jpg, .jpeg, .png" onchange="exibirNomeArquivo(this, 'nome-arquivo-selecionado')" class="hidden">
            </div>
            <p id="nome-arquivo-selecionado" class="text-xs text-green-700 font-bold mt-2"></p>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Ler os Termos</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 6: TERMOS DE RESPONSABILIDADE -->
    <div class="etapa-form hidden" id="etapa-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell">Termos de Responsabilidade</h1>
            <p class="text-sm text-gray-600">Por favor, leia atentamente as regras da nossa plataforma antes de finalizar.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm text-left max-h-60 overflow-y-auto text-sm text-gray-700 space-y-3">
            <p><strong>1. Maioridade Civil:</strong> Declaro ser maior de 18 (dezoito) anos e ter plena capacidade civil para utilizar o sistema.</p>
            <p><strong>2. Proteção de Dados (LGPD):</strong> Autorizo a coleta e o armazenamento dos meus dados pessoais necessários para a criação do perfil e intermediação de adoções.</p>
            <p><strong>3. Responsabilidade da Plataforma:</strong> Compreendo que o CãoNectados atua exclusivamente como um <strong>intermediador digital</strong> (vitrine) para facilitar o encontro entre animais e adotantes.</p>
            <p><strong>4. Isenção Legal:</strong> A plataforma <strong>não possui</strong> qualquer responsabilidade legal, logística, veterinária ou financeira sobre o processo de adoção, sendo esta responsabilidade inteiramente do adotante e do protetor/ONG envolvidos.</p>
        </div>

        <div class="mb-6 text-left">
            <label class="flex items-center gap-3 cursor-pointer p-4 bg-pink-50 border border-pink-200 rounded-xl hover:bg-pink-100 transition">
                <input type="checkbox" name="aceite_termos" id="aceite_termos" class="w-6 h-6 text-pink-500 rounded focus:ring-pink-400">
                <span class="text-sm text-gray-800 font-medium">
                    Li, compreendi e concordo com os Termos de Responsabilidade.
                </span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Finalizar Cadastro</span>
            <button type="button" onclick="submeterFormularioProtetor()" class="w-12 h-12 rounded-full bg-pink-300 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>
</form>

<!-- MODAL CROPPER REUTILIZÁVEL -->
<div id="modal-cropper" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 id="modal-cropper-titulo" class="font-shantell text-xl font-bold mb-1 text-gray-800">Ajustar Foto</h3>
        <p class="text-xs text-gray-500 mb-4 text-center">Arraste e use o zoom para centralizar.</p>
        
        <div class="w-full h-64 bg-gray-100 rounded-2xl overflow-hidden mb-4 flex items-center justify-center">
            <img id="imagem-para-cortar" src="" alt="Cortar" class="max-block max-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropper()" class="flex-1 bg-gray-200 text-gray-700 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-300 transition">Cancelar</button>
            <button type="button" onclick="salvarRecorte()" class="flex-1 bg-pink-400 text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">Aplicar</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="<?= e(URL_BASE) ?>/assets/js/onboarding.js"></script>
<script src="<?= e(URL_BASE) ?>/assets/js/autosave.js"></script>

<script>
    let cropper = null;
    let alvoAtual = null; 

    function iniciarCropperFoto(event, alvo) {
        alvoAtual = alvo;
        const fileInput = event.target;
        if (fileInput.files && fileInput.files.length > 0) {
            const limiteMB = (alvo === 'perfil') ? 2 : 3;
            if (typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarTamanhoArquivo(fileInput, limiteMB)) {
                mostrarModalFeedback('erro', `A imagem é muito grande. Escolha uma de até ${limiteMB}MB.`);
                fileInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const imgModal = document.getElementById('imagem-para-cortar');
                imgModal.src = e.target.result;
                
                document.getElementById('modal-cropper-titulo').innerText = (alvo === 'perfil') ? 'Ajustar Foto de Perfil' : 'Ajustar Foto de Capa';
                document.getElementById('modal-cropper').classList.remove('hidden');

                if (cropper) cropper.destroy();

                const isPerfil = (alvo === 'perfil');
                document.body.classList.toggle('cropper-circular', isPerfil);

                cropper = new Cropper(imgModal, {
                    aspectRatio: isPerfil ? 1 / 1 : 16 / 9,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85
                });
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    function fecharModalCropper() {
        document.getElementById('modal-cropper').classList.add('hidden');
        if (cropper) { cropper.destroy(); cropper = null; }
        if (alvoAtual === 'perfil') {
            document.getElementById('input-arquivo-perfil').value = '';
        } else if (alvoAtual === 'fundo') {
            document.getElementById('input-arquivo-fundo').value = '';
        }
    }

    function salvarRecorte() {
        if (!cropper) return;

        const options = (alvoAtual === 'perfil') 
            ? { width: 400, height: 400 } 
            : { width: 1200, height: 675 };

        const base64String = cropper.getCroppedCanvas(options).toDataURL('image/png');

        if (alvoAtual === 'perfil') {
            const preview = document.getElementById('preview-foto-perfil');
            const placeholder = document.getElementById('foto-placeholder-perfil');
            preview.src = base64String;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
            document.getElementById('foto_perfil_cortada').value = base64String;
        } else {
            const previewFundo = document.getElementById('preview-foto-fundo');
            const placeholderFundo = document.getElementById('fundo-placeholder-capa');
            previewFundo.src = base64String;
            previewFundo.classList.remove('hidden');
            if (placeholderFundo) placeholderFundo.classList.add('hidden');
            document.getElementById('foto_fundo_cortada').value = base64String;
        }

        fecharModalCropper();
    }

    function proximaEtapa() {
        OnboardingManager.avancarEtapa(function(etapaAtual) {
            if (etapaAtual === 1) {
                const nomeInput = document.getElementById('nome_fantasia');
                if (!CaonectadosValidator.validarNome(nomeInput.value)) {
                    mostrarModalFeedback('erro', "Informe um nome válido com pelo menos 2 caracteres.");
                    nomeInput.focus();
                    return false;
                }
                const inputDoc = document.getElementById('cnpj_cpf');
                const msgErroDoc = document.getElementById('erro-documento');
                const inputTipoDoc = document.getElementById('tipo_documento');
                const tipoDoc = inputTipoDoc ? inputTipoDoc.value.toLowerCase().trim() : 'cpf';

                const docLimpo = inputDoc.value.replace(/[^\d]+/g, '');
                let docValido = false;
                let msgErroEspecifica = "";

                if (tipoDoc === 'cnpj') {
                    docValido = (docLimpo.length === 14 && CaonectadosValidator.isCnpjValido(docLimpo));
                    msgErroEspecifica = "Informe um CNPJ válido com 14 dígitos.";
                } else {
                    docValido = (docLimpo.length === 11 && CaonectadosValidator.isCpfValido(docLimpo));
                    msgErroEspecifica = "Informe um CPF válido com 11 dígitos.";
                }

                if (!docValido) {
                    if (msgErroDoc) {
                        msgErroDoc.innerText = msgErroEspecifica;
                        msgErroDoc.classList.remove('hidden');
                    }
                    inputDoc.classList.add('border-red-500', 'ring-red-300');
                    mostrarModalFeedback('erro', msgErroEspecifica);
                    inputDoc.focus();
                    return false;
                } else if (msgErroDoc) {
                    msgErroDoc.classList.add('hidden');
                    inputDoc.classList.remove('border-red-500', 'ring-red-300');
                }

                if (tipoDoc === 'cnpj') {
                    const dataAbertura = document.getElementById('data_abertura_cnpj');
                    if (!dataAbertura || !dataAbertura.value) {
                        mostrarModalFeedback('erro', "Informe a data de abertura do CNPJ.");
                        dataAbertura.focus();
                        return false;
                    }
                    const hoje = new Date().toISOString().split('T')[0];
                    if (dataAbertura.value > hoje) {
                        mostrarModalFeedback('erro', "A data de abertura do CNPJ não pode ser futura.");
                        dataAbertura.focus();
                        return false;
                    }
                } else {
                    const dataNasc = document.getElementById('dt_nasc');
                    if (!dataNasc || !dataNasc.value) {
                        mostrarModalFeedback('erro', "Informe sua data de nascimento.");
                        dataNasc.focus();
                        return false;
                    } else if (!CaonectadosValidator.validarMaioridade(dataNasc.value)) {
                        mostrarModalFeedback('erro', "É necessário ter pelo menos 18 anos para se cadastrar.");
                        dataNasc.focus();
                        return false;
                    }
                }

                const telefoneInput = document.getElementById('telefone');
                if (!telefoneInput.value || !CaonectadosValidator.validarTelefone(telefoneInput.value)) {
                    mostrarModalFeedback('erro', "O telefone informado é inválido ou está vazio. Certifique-se de incluir o DDD.");
                    telefoneInput.focus();
                    return false;
                }
            }

            if (etapaAtual === 2) {
                const inputHidden = document.getElementById('regiao_id_hidden');
                const msgErro = document.getElementById('erro-bairro-invalido');
                const obsCasa = document.getElementById('obs_casa');
                const numMorada = document.getElementById('numero');

                OnboardingManager.sincronizarRegiaoId();

                if (!inputHidden.value) {
                    if (msgErro) msgErro.classList.remove('hidden');
                    mostrarModalFeedback('aviso', "Por favor, selecione um bairro válido da lista.");
                    document.getElementById('input-busca-bairro').focus();
                    return false;
                }

                if (!obsCasa || obsCasa.value.trim() === '') {
                    mostrarModalFeedback('aviso', "Por favor, informe o seu logradouro.");
                    obsCasa.focus();
                    return false;
                }

                if (!numMorada || numMorada.value.trim() === '') {
                    mostrarModalFeedback('aviso', "Por favor, informe o número do local.");
                    numMorada.focus();
                    return false;
                }
            }

            if (etapaAtual === 3) {
                const descricao = document.getElementById('descricao');
                if (descricao.value.trim().length < 15) {
                    mostrarModalFeedback('aviso', "Por favor, forneça uma breve descrição da sua causa (mínimo de 15 caracteres).");
                    descricao.focus();
                    return false;
                }

                const insta = document.getElementById('instagram').value.trim();
                if (insta !== '' && !CaonectadosValidator.validarLinkSocial(insta, 'instagram')) {
                    mostrarModalFeedback('erro', "Informe um link válido do Instagram (Ex: instagram.com/sua_ong).");
                    document.getElementById('instagram').focus();
                    return false;
                }

                const face = document.getElementById('facebook').value.trim();
                if (face !== '' && !CaonectadosValidator.validarLinkSocial(face, 'facebook')) {
                    mostrarModalFeedback('erro', "Informe um link válido do Facebook (Ex: facebook.com/sua_ong).");
                    document.getElementById('facebook').focus();
                    return false;
                }

                const pix = document.getElementById('chave_pix').value.trim();
                if (pix !== '' && !CaonectadosValidator.validarChavePix(pix)) {
                    mostrarModalFeedback('erro', "Informe uma Chave PIX válida.");
                    document.getElementById('chave_pix').focus();
                    return false;
                }
            }

            if (etapaAtual === 5) {
                const docInput = document.getElementById('comprovante_documento');

                if (docInput.files.length === 0) {
                    mostrarModalFeedback('aviso', "Você precisa anexar o comprovante de atividade para prosseguir.");
                    return false;
                }

                const arquivo = docInput.files[0];
                const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

                if (!tiposPermitidos.includes(arquivo.type)) {
                    mostrarModalFeedback('erro', "Formato de arquivo inválido. Anexe apenas arquivos em PDF, JPG ou PNG.");
                    docInput.value = '';
                    document.getElementById('nome-arquivo-selecionado').innerText = '';
                    return false;
                }

                if (!CaonectadosValidator.validarTamanhoArquivo(docInput, 5)) {
                    mostrarModalFeedback('erro', "O comprovante de atividade é muito pesado. Escolha um arquivo de até 5MB.");
                    return false;
                }
            }

            return true;
        });
    }

    async function submeterFormularioProtetor() {
        const aceiteTermos = document.getElementById('aceite_termos');
        if (!aceiteTermos.checked) {
            mostrarModalFeedback('aviso', "Você deve ler e concordar com os Termos de Responsabilidade para continuar.");
            aceiteTermos.focus();
            return;
        }

        const form = document.getElementById('form-onboarding-protetor');
        const formData = new FormData(form);

        try {
            const btnSubmit = event.target;
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'Aguarde...';
            btnSubmit.disabled = true;

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'sucesso') {
                if (typeof limparAutoSave === 'function') limparAutoSave();
                window.location.href = result.redirect_url;
            } else {
                mostrarModalFeedback('erro', result.mensagem);
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        } catch (error) {
            console.error(error);
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        OnboardingManager.init({
            totalEtapas: 6,
            urlSelecionarPerfil: "<?= URL_BASE ?>/onboarding",
            validarEnvioFinal: () => true
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>