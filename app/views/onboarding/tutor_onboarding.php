<?php
require_once __DIR__ . '/../templates/header.php';
?>

<!-- Formulário Principal Multietapas -->
<form id="form-onboarding-tutor" action="<?= URL_BASE ?>/onboarding/salvar-tutor" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto p-4">

    <!-- BARRA DE PROGRESSO GLOBAL (5 ETAPAS) -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-10 rounded-full bg-gray-300 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR GLOBAL -->
    <button type="button" id="btn-voltar-global" onclick="voltar()" class="mb-4 text-2xl font-bold cursor-pointer transition hover:opacity-75" title="Voltar">
        &#129144;
    </button>

    <!-- ETAPA 1: Localização -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Selecione a sua localização</h1>
            <p class="text-sm text-gray-600">Selecione a sua localização para podermos encontrar os pets mais próximos de você e dar início a um novo match!</p>
        </div>

        <div class="mb-6 relative">
            <label for="input-busca-bairro" class="block font-medium mb-1">Pesquise seu Bairro / Região *</label>
            <input type="text" id="input-busca-bairro" list="lista-regioes" placeholder="Digite o nome do seu bairro..." required autocomplete="off" oninput="sincronizarRegiaoId()" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">

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

    <!-- ETAPA 2: Moradia -->
    <div class="etapa-form hidden" id="etapa-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Sobre você</h1>
            <p class="text-sm text-gray-600">Complete esse formulário para acharmos o pet Perfeito para você!</p>
        </div>

        <div class="space-y-4 mb-6">
            <div>
                <label for="tipo_moradia" class="block font-medium mb-1">Tipo de moradia</label>
                <select name="tipo_moradia" id="tipo_moradia" class="w-full p-2 border rounded-lg">
                    <option value="" disabled selected>Escolha</option>
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                    <option value="chacara">Chácara/Sítio</option>
                </select>
            </div>

            <div>
                <label for="espaco_interior" class="block font-medium mb-1">Espaço interior</label>
                <select name="espaco_interior" id="espaco_interior" class="w-full p-2 border rounded-lg">
                    <option value="" disabled selected>Escolha</option>
                    <option value="pequeno">Pequeno</option>
                    <option value="medio">Médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>

            <div>
                <label for="espaco_externo" class="block font-medium mb-1">Espaço externo</label>
                <select name="espaco_externo" id="espaco_externo" class="w-full p-2 border rounded-lg">
                    <option value="" disabled selected>Escolha</option>
                    <option value="nenhum">Não possui quintal</option>
                    <option value="pequeno">Quintal pequeno</option>
                    <option value="medio">Quintal médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 3: Convivência -->
    <div class="etapa-form hidden" id="etapa-3">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Convivência</h1>
            <p class="text-sm text-gray-600">Conte-nos um pouco mais sobre sua rotina com outros pets ou família.</p>
        </div>

        <div class="space-y-4 mb-6">
            <div>
                <label class="block font-medium mb-1">Possui filhos ou crianças em casa?</label>
                <select name="possui_criancas" class="w-full p-2 border rounded-lg">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1">Possui outros pets?</label>
                <select name="possui_outros_pets" class="w-full p-2 border rounded-lg">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 4: Nome de Usuário, Foto de Perfil e Descrição -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <!-- CÍRCULO DA FOTO DE PERFIL / PREVIEW -->
            <div class="flex justify-center mb-4">
                <div class="w-32 h-32 rounded-full border-4 border-gray-300 bg-gray-100 flex items-center justify-center overflow-hidden relative shadow-sm">
                    <!-- Ícone Padrão de Câmera/Placeholder -->
                    <span id="foto-placeholder" class="text-gray-400 text-4xl font-bold">&#128247;</span>
                    <!-- Elemento Imagem do Preview (inicia oculto) -->
                    <img id="preview-foto" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
                </div>
            </div>

            <h1 class="text-2xl font-bold mb-2">Como podemos te chamar?</h1>
            <input type="text" name="nome_usuario" placeholder="Digite seu nome aqui" required class="w-full p-2 border rounded-lg mb-6 text-center">

            <h2 class="text-xl font-bold mb-2">Adicione uma foto de perfil</h2>
            <p class="text-sm text-gray-600 mb-4">Escolha uma foto de perfil bem bonita! Adicione uma foto para que a comunidade reconheça você e o seu amor pelos animais.</p>

            <div class="mb-4">
                <label for="foto_perfil" class="inline-block bg-emerald-800 text-white font-medium py-2 px-6 rounded-lg cursor-pointer transition hover:bg-emerald-900">
                    Anexar foto
                </label>
                <!-- Evento onchange disparando a função do preview -->
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" onchange="exibirPreviewFoto(this)" class="hidden">
            </div>

            <!-- Descrição/Bio -->
            <div class="text-left mt-6">
                <label for="descricao" class="block font-medium mb-1">Conte um pouco sobre você (Descrição):</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Ex: Sou apaixonado por cães, moro em casa com quintal e procuro um novo parceiro de caminhada..." class="w-full p-2 border rounded-lg text-sm"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Preferências do Feed -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Preferências do Feed</h1>
            <p class="text-sm text-gray-600">Selecione suas preferências para montarmos o seu feed perfeito. Você pode escolher mais de uma opção!</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <!-- Espécie -->
            <div>
                <span class="font-bold block mb-2">Espécie preferida:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_especie[]" value="1"> Cachorro
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_especie[]" value="2"> Gato
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" id="checkbox-outras-especies" onchange="toggleOutrasEspecies()" value="outros"> Outros
                    </label>
                </div>
                
                <div id="container-outras-especies" class="hidden mt-3 p-3 border border-pink-300 rounded-lg bg-pink-50">
                    <label class="block font-medium mb-2 text-sm">Selecione outras espécies desejadas:</label>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <?php if (!empty($especies)): ?>
                            <?php foreach ($especies as $especie): ?>
                                <?php 
                                    $espId = is_array($especie) ? $especie['especie_id'] : $especie->getEspecieId();
                                    $espNome = is_array($especie) ? $especie['nome'] : $especie->getNome();
                                ?>
                                <?php if (!in_array(strtolower($espNome), ['cachorro', 'gato'])): ?>
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="preferencias_especie[]" value="<?= $espId ?>" class="check-outras"> 
                                        <?= e($espNome) ?>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Porte -->
            <div>
                <span class="font-bold block mb-2">Porte:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_porte[]" value="pequeno"> Pequeno
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_porte[]" value="medio"> Médio
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_porte[]" value="grande"> Grande
                    </label>
                </div>
            </div>

            <!-- Sexo -->
            <div>
                <span class="font-bold block mb-2">Sexo:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_sexo[]" value="femea"> Fêmea
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100 rounded-lg cursor-pointer">
                        <input type="checkbox" name="preferencias_sexo[]" value="macho"> Macho
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Ir para o Feed</span>
            <button type="submit" class="w-12 h-12 rounded-full bg-pink-300 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>
</form>
<script src="<?= URL_BASE ?>/assets/js/validacoes.js"></script>
<script src="<?= URL_BASE ?>/assets/js/validacoes.js"></script>
<script>
    let etapaAtual = 1;
    const totalEtapas = 5;
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
        const etapaDiv = document.getElementById(`etapa-${etapaAtual}`);

        // --- VALIDAÇÕES POR ETAPA DO TUTOR ---

        // ETAPA 1: Localização
        if (etapaAtual === 1) {
            const inputHidden = document.getElementById('regiao_id_hidden');
            const msgErro = document.getElementById('erro-bairro-invalido');

            sincronizarRegiaoId();

            if (!inputHidden.value) {
                msgErro.classList.remove('hidden');
                document.getElementById('input-busca-bairro').focus();
                return;
            }
        }

        // ETAPA 2: Moradia
        if (etapaAtual === 2) {
            const moradia = document.getElementById('tipo_moradia');
            if (!moradia.value) {
                mostrarModalFeedback('aviso', "Por favor, selecione o tipo de moradia.");
                moradia.focus();
                return;
            }
        }

        // ETAPA 3: Convivência
        if (etapaAtual === 3) {
            const criancas = document.querySelector('select[name="possui_criancas"]');
            const pets = document.querySelector('select[name="possui_outros_pets"]');
            if (!criancas.value || !pets.value) {
                mostrarModalFeedback('aviso', "Por favor, responda às perguntas sobre convivência.");
                return;
            }
        }

        // ETAPA 4: Nome, Foto de Perfil e Descrição
        if (etapaAtual === 4) {
            const nomeInput = document.querySelector('input[name="nome_usuario"]');
            if (nomeInput.value.trim().length < 2) {
                mostrarModalFeedback('erro', "Por favor, informe seu nome corretamente.");
                nomeInput.focus();
                return;
            }

            const fotoInput = document.getElementById('foto_perfil');
            if (fotoInput.files.length > 0 && !CaonectadosValidator.validarTamanhoArquivo(fotoInput, 2)) {
                mostrarModalFeedback('erro', "A foto de perfil é muito pesada. Escolha uma imagem de até 2MB.");
                return;
            }
        }

        // ETAPA 5: Preferências do Feed (Antes de submeter)
        if (etapaAtual === 5) {
            const opcoesEspecie = document.querySelectorAll('input[name="preferencias_especie[]"]:checked');
            if (opcoesEspecie.length === 0) {
                mostrarModalFeedback('aviso', "Selecione pelo menos uma preferência de espécie antes de avançar.");
                return;
            }
        }

        // Validação genérica HTML5 para campos com o atributo required
        const camposObrigatorios = etapaDiv.querySelectorAll('[required]');
        let valido = true;

        camposObrigatorios.forEach(campo => {
            if (!campo.checkValidity()) {
                campo.reportValidity();
                valido = false;
            }
        });

        if (valido && etapaAtual < totalEtapas) {
            etapaAtual++;
            atualizarVisualEtapas();
        }
    }
    function voltar() {
        if (etapaAtual > 1) {
            etapaAtual--;
            atualizarVisualEtapas();
        } else {
            window.location.href = urlSelecionarPerfil;
        }
    }

    function toggleOutrasEspecies() {
        const checkbox = document.getElementById('checkbox-outras-especies');
        const container = document.getElementById('container-outras-especies');
        const checkboxesOutras = container.querySelectorAll('.check-outras');
        
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            checkboxesOutras.forEach(cb => cb.checked = false);
        }
    }

    function exibirPreviewFoto(input) {
        const imgPreview = document.getElementById('preview-foto');
        const placeholder = document.getElementById('foto-placeholder');

        if (input.files && input.files[0]) {
            const leitor = new FileReader();

            leitor.onload = function (e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };

            leitor.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', atualizarVisualEtapas);
</script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>