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

<form id="form-onboarding-protetor" action="<?= URL_BASE ?>/onboarding/salvar-protetor" method="POST" enctype="multipart/form-data" onsubmit="return validarEnvioFinal(event)" class="max-w-md mx-auto p-4">

    <input type="hidden" name="tipo_documento" value="<?= $tipo_perfil ?>">

    <!-- BARRA DE PROGRESSO COM 6 ETAPAS -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-6" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR -->
    <button type="button" id="btn-voltar-global" onclick="voltar()" class="mb-4 text-2xl font-bold cursor-pointer transition hover:opacity-75" title="Voltar">
        &#129144;
    </button>

    <!-- ETAPA 1: Validação -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2"><?= $titulo_etapa1 ?></h1>
            <p class="text-sm text-gray-600">Para garantir a segurança dos nossos pets e adotantes, <?= $texto_etapa1 ?></p>
        </div>

        <div class="space-y-4 mb-6">
            <div>
                <label for="nome_fantasia" class="block font-medium mb-1"><?= $label_nome ?></label>
                <input type="text" name="nome_fantasia" id="nome_fantasia" placeholder="<?= $placeholder_nome ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>

            <div>
                <label for="cnpj_cpf" class="block font-medium mb-1"><?= $label_doc ?></label>
                <input type="text" name="cnpj_cpf" id="cnpj_cpf" placeholder="<?= $placeholder_doc ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
                <p id="erro-documento" class="text-xs text-red-500 mt-1 hidden">O documento informado é inválido.</p>
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

        <div class="mb-6 relative">
            <label for="input-busca-bairro" class="block font-medium mb-1">Pesquise seu Bairro / Região *</label>
            <input type="text" id="input-busca-bairro" list="lista-regioes" placeholder="Digite o nome do seu bairro..." autocomplete="off" oninput="sincronizarRegiaoId()" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">

            <datalist id="lista-regioes">
                <?php if (!empty($regioes)): ?>
                    <?php foreach ($regioes as $regiao): ?>
                        <option data-id="<?= $regiao['regiao_id'] ?>" value="<?= e($regiao['nome_regiao']) ?>"></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </datalist>

            <input type="hidden" name="regiao_id" id="regiao_id_hidden">
            <p id="erro-bairro-invalido" class="text-xs text-red-500 mt-1 hidden">Selecione um bairro válido da lista fornecida.</p>
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

        <div class="space-y-4 mb-6">
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

    <!-- ETAPA 4: Foto de Perfil -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <div class="w-32 h-32 rounded-full border-4 border-gray-300 bg-gray-100 flex items-center justify-center overflow-hidden relative shadow-sm">
                    <span id="foto-placeholder" class="text-gray-400 text-4xl font-bold">&#128247;</span>
                    <img id="preview-foto" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
                </div>
            </div>

            <h2 class="text-xl font-bold mb-2">Adicione uma foto de perfil</h2>
            <p class="text-sm text-gray-600 mb-4">Escolha uma foto de perfil bem bonita para que a comunidade reconheça o seu amor pelos animais.</p>

            <div class="mb-4">
                <label for="foto_perfil" class="inline-block bg-emerald-800 text-white font-medium py-2 px-6 rounded-lg cursor-pointer transition hover:bg-emerald-900">
                    Anexar foto
                </label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" onchange="exibirPreviewFoto(this)" class="hidden">
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
            <p class="text-sm text-gray-600 mb-6">Selecione ou arraste o arquivo do seu comprovante para que nossa equipe possa validar o seu perfil no sistema.</p>

            <div class="mb-4">
                <label for="comprovante_documento" class="inline-block bg-emerald-800 text-white font-medium py-2 px-6 rounded-lg cursor-pointer transition hover:bg-emerald-900">
                    Anexar documento
                </label>
                <input type="file" name="comprovante_documento" id="comprovante_documento" onchange="exibirNomeArquivo(this)" class="hidden">
            </div>
            <p id="nome-arquivo-selecionado" class="text-xs text-green-700 font-bold mt-2"></p>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Ler os Termos</span>
            <!-- BOTAO ALTERADO PARA AVANÇAR E NAO ENVIAR -->
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 6: TERMOS DE RESPONSABILIDADE (NOVA) -->
    <div class="etapa-form hidden" id="etapa-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell">Termos de Responsabilidade</h1>
            <p class="text-sm text-gray-600">Por favor, leia atentamente as regras da nossa plataforma antes de finalizar.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm text-left max-h-60 overflow-y-auto text-sm text-gray-700 space-y-3">
            <p><strong>1. Maioridade Civil:</strong> Declaro ser maior de 18 (dezoito) anos e ter plena capacidade civil para utilizar o sistema.</p>
            <p><strong>2. Proteção de Dados (LGPD):</strong> Autorizo a coleta e o armazenamento dos meus dados pessoais necessários para a criação do perfil e intermediação de adoções.</p>
            <p><strong>3. Responsabilidade da Plataforma:</strong> Compreendo que o CãoNectados atua exclusivamente como um <strong>intermediador digital</strong> (vitrine) para facilitar o encontro entre animais e adotantes.</p>
            <p><strong>4. Isenção Legal:</strong> A plataforma <strong>não possui</strong> qualquer responsabilidade legal, logística, veterinária ou financeira sobre o processo de adoção, sendo esta responsabilidade inteiramente do tutor e do protetor/ONG envolvidos.</p>
        </div>

        <div class="mb-6 text-left">
            <label class="flex items-center gap-3 cursor-pointer p-4 bg-pink-50 border border-pink-200 rounded-xl hover:bg-pink-100 transition">
                <input type="checkbox" name="aceite_termos" required class="w-6 h-6 text-pink-500 rounded focus:ring-pink-400">
                <span class="text-sm text-gray-800 font-medium">
                    Li, compreendi e concordo com os Termos de Responsabilidade.
                </span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Finalizar Cadastro</span>
            <button type="submit" class="w-12 h-12 rounded-full bg-pink-300 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>
</form>

<script>
    let etapaAtual = 1;
    const totalEtapas = 6; // Atualizado para 6
    const urlSelecionarPerfil = "<?= URL_BASE ?>/onboarding";

    function atualizarVisualEtapas() {
        for (let i = 1; i <= totalEtapas; i++) {
            const elEtapa = document.getElementById(`etapa-${i}`);
            const elProgresso = document.getElementById(`progresso-${i}`);

            if (i === etapaAtual) {
                elEtapa.classList.remove('hidden');
            } else {
                elEtapa.classList.add('hidden');
            }

            if (i <= etapaAtual) {
                elProgresso.classList.remove('bg-gray-300');
                elProgresso.classList.add('bg-green-500');
            } else {
                elProgresso.classList.remove('bg-green-500');
                elProgresso.classList.add('bg-gray-300');
            }
        }
    }

    function sincronizarRegiaoId() {
        const inputTexto = document.getElementById('input-busca-bairro');
        const inputHidden = document.getElementById('regiao_id_hidden');
        const msgErro = document.getElementById('erro-bairro-invalido');
        const datalistOptions = document.querySelectorAll('#lista-regioes option');

        let encontradoId = '';

        datalistOptions.forEach(option => {
            if (option.value.trim().toLowerCase() === inputTexto.value.trim().toLowerCase()) {
                encontradoId = option.getAttribute('data-id');
            }
        });

        inputHidden.value = encontradoId;

        if (inputTexto.value.trim() !== '' && !encontradoId) {
            msgErro.classList.remove('hidden');
        } else {
            msgErro.classList.add('hidden');
        }
    }

    function proximaEtapa() {
        // ETAPA 1: Nome Fantasia e CPF/CNPJ
        if (etapaAtual === 1) {
            const nomeInput = document.getElementById('nome_fantasia');
            if (nomeInput.value.trim().length < 3) {
                mostrarModalFeedback('erro', "Informe um nome válido com pelo menos 3 caracteres.");
                nomeInput.focus();
                return;
            }

            const inputDoc = document.getElementById('cnpj_cpf');
            const msgErroDoc = document.getElementById('erro-documento');

            if (inputDoc.value.trim() === '' || !CaonectadosValidator.validarDocumento(inputDoc.value)) {
                msgErroDoc.classList.remove('hidden');
                inputDoc.classList.add('border-red-500', 'ring-red-300');
                mostrarModalFeedback('erro', "Informe um número de CPF ou CNPJ válido.");
                inputDoc.focus();
                return;
            } else {
                msgErroDoc.classList.add('hidden');
                inputDoc.classList.remove('border-red-500', 'ring-red-300');
            }
        }

        // ETAPA 2: Bairro/Região
        if (etapaAtual === 2) {
            const inputHidden = document.getElementById('regiao_id_hidden');
            const msgErro = document.getElementById('erro-bairro-invalido');

            sincronizarRegiaoId();

            if (!inputHidden.value) {
                msgErro.classList.remove('hidden');
                mostrarModalFeedback('aviso', "Por favor, selecione um bairro válido da lista.");
                document.getElementById('input-busca-bairro').focus();
                return;
            }
        }

        // ETAPA 3: Descrição, Links e PIX
        if (etapaAtual === 3) {
            const descricao = document.getElementById('descricao');
            if (descricao.value.trim().length < 15) {
                mostrarModalFeedback('aviso', "Por favor, forneça uma breve descrição da sua causa (mínimo de 15 caracteres).");
                descricao.focus();
                return;
            }

            const insta = document.getElementById('instagram').value.trim();
            if (insta !== '' && !CaonectadosValidator.validarLinkSocial(insta, 'instagram')) {
                mostrarModalFeedback('erro', "Informe um link válido do Instagram (Ex: instagram.com/sua_ong).");
                document.getElementById('instagram').focus();
                return;
            }

            const face = document.getElementById('facebook').value.trim();
            if (face !== '' && !CaonectadosValidator.validarLinkSocial(face, 'facebook')) {
                mostrarModalFeedback('erro', "Informe um link válido do Facebook (Ex: facebook.com/sua_ong).");
                document.getElementById('facebook').focus();
                return;
            }

            const pix = document.getElementById('chave_pix').value.trim();
            if (pix !== '' && pix.length < 5) {
                mostrarModalFeedback('erro', "Informe uma Chave PIX válida.");
                document.getElementById('chave_pix').focus();
                return;
            }
        }

        // ETAPA 4: Foto de Perfil
        if (etapaAtual === 4) {
            const fotoInput = document.getElementById('foto_perfil');
            if (fotoInput.files.length > 0 && !CaonectadosValidator.validarTamanhoArquivo(fotoInput, 2)) {
                mostrarModalFeedback('erro', "A foto de perfil é muito pesada. Escolha uma imagem de até 2MB.");
                return;
            }
        }

        // ETAPA 5: VALIDAÇÃO ANTES DOS TERMOS (Comprovante)
        if (etapaAtual === 5) {
            const docInput = document.getElementById('comprovante_documento');
            if (docInput.files.length === 0) {
                mostrarModalFeedback('aviso', "Você precisa anexar o comprovante de atividade para prosseguir.");
                return;
            }
            if (!CaonectadosValidator.validarTamanhoArquivo(docInput, 5)) {
                mostrarModalFeedback('erro', "O comprovante de atividade é muito pesado. Escolha um arquivo de até 5MB.");
                return;
            }
        }

        if (etapaAtual < totalEtapas) {
            etapaAtual++;
            atualizarVisualEtapas();
        }
    }

    function validarEnvioFinal(event) {
        // Valida apenas a caixinha na etapa final
        const aceiteTermos = document.querySelector('input[name="aceite_termos"]');
        if (!aceiteTermos || !aceiteTermos.checked) {
            event.preventDefault();
            mostrarModalFeedback('aviso', "Você deve ler e concordar com os Termos de Responsabilidade para continuar.");
            aceiteTermos.focus();
            return false;
        }

        return true;
    }

    function voltar() {
        if (etapaAtual > 1) {
            etapaAtual--;
            atualizarVisualEtapas();
        } else {
            window.location.href = urlSelecionarPerfil;
        }
    }

    function exibirPreviewFoto(input) {
        const imgPreview = document.getElementById('preview-foto');
        const placeholder = document.getElementById('foto-placeholder');

        if (input.files && input.files[0]) {
            const leitor = new FileReader();

            leitor.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };

            leitor.readAsDataURL(input.files[0]);
        }
    }

    function exibirNomeArquivo(input) {
        const pNome = document.getElementById('nome-arquivo-selecionado');
        if (input.files && input.files[0]) {
            pNome.innerText = "Arquivo anexado: " + input.files[0].name;
        } else {
            pNome.innerText = "";
        }
    }

    document.addEventListener('DOMContentLoaded', atualizarVisualEtapas);
</script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>