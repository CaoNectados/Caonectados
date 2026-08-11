<?php
require_once __DIR__ . '/../templates/header.php';
?>

<form id="form-onboarding-tutor" action="<?= URL_BASE ?>/onboarding/salvar-tutor" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto p-4">

    <!-- BARRA DE PROGRESSO GLOBAL (AGORA COM 6 ETAPAS) -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
        <div id="progresso-6" class="h-2 w-8 rounded-full bg-gray-300 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR GLOBAL -->
    <button type="button" id="btn-voltar-global" onclick="OnboardingManager.voltarEtapa()" class="mb-4 text-2xl font-bold cursor-pointer transition hover:opacity-75" title="Voltar">
        &#129144;
    </button>

    <!-- ETAPA 1: Localização -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2">Selecione a sua localização</h1>
            <p class="text-sm text-gray-600">Selecione a sua localização para podermos encontrar os pets mais próximos de você e dar início a um novo match!</p>
        </div>

        <div class="mb-4 relative text-left">
            <label for="input-busca-bairro" class="block font-medium mb-1">Pesquise seu Bairro / Região *</label>
            <input type="text" id="input-busca-bairro" list="lista-regioes" placeholder="Digite o nome do seu bairro..." autocomplete="off" oninput="OnboardingManager.sincronizarRegiaoId()" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">

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
            <input type="text" name="obs_casa" id="obs_casa" required placeholder="Ex: Avenida Brasil, Apto 42..." class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
        </div>

        <div class="mb-6 text-left">
            <label for="num_morada" class="block font-medium mb-1">Número da Residência *</label>
            <input type="text" name="num_morada" id="num_morada" required placeholder="Ex: 123, S/N" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
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

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="tipo_moradia" class="block font-medium mb-1">Tipo de moradia *</label>
                <select name="tipo_moradia" id="tipo_moradia" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-pink-300">
                    <option value="" disabled selected>Escolha</option>
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                    <option value="chacara">Chácara/Sítio</option>
                    <option value="outro">Outro</option>
                </select>
            </div>

            <div>
                <label for="espaco_interior" class="block font-medium mb-1">Espaço interior *</label>
                <select name="espaco_interior" id="espaco_interior" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-pink-300">
                    <option value="" disabled selected>Escolha</option>
                    <option value="pequeno">Pequeno</option>
                    <option value="medio">Médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>

            <div>
                <label for="espaco_externo" class="block font-medium mb-1">Espaço externo *</label>
                <select name="espaco_externo" id="espaco_externo" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-pink-300">
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

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label class="block font-medium mb-1">Possui filhos ou crianças em casa? *</label>
                <select name="possui_criancas" id="possui_criancas" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-pink-300">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1">Possui outros pets? *</label>
                <select name="possui_outros_pets" id="possui_outros_pets" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-pink-300">
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

    <!-- ETAPA 4: Nome de Usuário e Informações Pessoais -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell">Como podemos te chamar?</h1>
            
            <div class="mb-6 mt-4">
                <label for="foto_perfil" class="block font-medium mb-1 text-left">Foto de Perfil (Opcional)</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
            </div>

            <div class="mb-6">
                <label for="nome_usuario" class="block font-medium mb-1 text-left">Seu Nome *</label>
                <input type="text" name="nome_usuario" id="nome_usuario" placeholder="Digite seu nome aqui" class="w-full p-2 border rounded-lg text-left focus:ring-2 focus:ring-pink-300">
            </div>

            <div class="mb-6 text-left">
                <label for="descricao" class="block font-medium mb-1">Fale um pouco sobre você (Opcional)</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Por que você quer adotar um pet?" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none"></textarea>
            </div>

            <div class="space-y-4 mb-6 text-left">
                <div>
                    <label for="dt_nasc" class="block font-medium mb-1">Data de Nascimento *</label>
                    <input type="date" name="dt_nasc" id="dt_nasc" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
                </div>

                <div>
                    <label for="telefone" class="block font-medium mb-1">Telefone (Opcional)</label>
                    <input type="tel" name="telefone" id="telefone" placeholder="(00) 00000-0000" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium">Ir para Preferências</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-pink-200 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Preferências do Feed -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell">Suas Preferências</h1>
            <p class="text-sm text-gray-600 mb-6">Selecione suas preferências para montarmos o seu feed perfeito. Você pode escolher mais de uma opção!</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <span class="font-bold block mb-2">Espécie:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_especie[]" value="2"> Gato
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_especie[]" value="1"> Cachorro
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
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

            <div>
                <span class="font-bold block mb-2">Porte:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_porte[]" value="pequeno"> Pequeno
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_porte[]" value="medio"> Médio
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_porte[]" value="grande"> Grande
                    </label>
                </div>
            </div>

            <div>
                <span class="font-bold block mb-2">Sexo:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_sexo[]" value="femea"> Fêmea
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-pink-100/70 rounded-lg cursor-pointer hover:bg-pink-100">
                        <input type="checkbox" name="preferencias_sexo[]" value="macho"> Macho
                    </label>
                </div>
            </div>
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
            <span class="font-medium">Ir para o Feed</span>
            <button type="submit" class="w-12 h-12 rounded-full bg-pink-300 text-xl font-bold flex items-center justify-center">&rarr;</button>
        </div>
    </div>

</form>

<script>
    function proximaEtapa() {
        OnboardingManager.avancarEtapa(function(etapaAtual) {
            if (etapaAtual === 1) {
                const inputHidden = document.getElementById('regiao_id_hidden');
                const msgErro = document.getElementById('erro-bairro-invalido');
                const obsCasa = document.getElementById('obs_casa');
                const numMorada = document.getElementById('num_morada');

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
                    mostrarModalFeedback('aviso', "Por favor, informe o número da sua residência.");
                    numMorada.focus();
                    return false;
                }
            }

            if (etapaAtual === 2) {
                const moradia = document.getElementById('tipo_moradia').value;
                const interior = document.getElementById('espaco_interior').value;
                const externo = document.getElementById('espaco_externo').value;
                if (!moradia || !interior || !externo) {
                    mostrarModalFeedback('aviso', "Por favor, preencha todas as perguntas sobre a sua moradia.");
                    return false;
                }
            }

            if (etapaAtual === 3) {
                const criancas = document.getElementById('possui_criancas').value;
                const pets = document.getElementById('possui_outros_pets').value;
                if (!criancas || !pets) {
                    mostrarModalFeedback('aviso', "Por favor, responda às perguntas sobre convivência.");
                    return false;
                }
            }

            if (etapaAtual === 4) {
                const nomeInput = document.getElementById('nome_usuario');
                if (!CaonectadosValidator.validarNome(nomeInput.value)) {
                    mostrarModalFeedback('erro', "Por favor, informe seu nome corretamente (mínimo de 2 caracteres).");
                    nomeInput.focus();
                    return false;
                }
                
                const dataNasc = document.getElementById('dt_nasc');
                if (!dataNasc || !dataNasc.value) {
                    mostrarModalFeedback('erro', "Por favor, informe sua data de nascimento.");
                    dataNasc.focus();
                    return false;
                } else if (!CaonectadosValidator.validarMaioridade(dataNasc.value)) {
                    mostrarModalFeedback('erro', "É necessário ter pelo menos 18 anos para se cadastrar.");
                    dataNasc.focus();
                    return false;
                }

                const telefoneInput = document.getElementById('telefone');
                if (!CaonectadosValidator.validarTelefone(telefoneInput.value)) {
                    mostrarModalFeedback('erro', "O telefone informado é inválido. Certifique-se de incluir o DDD.");
                    telefoneInput.focus();
                    return false;
                }
            }

            if (etapaAtual === 5) {
                const opcoesEspecie = document.querySelectorAll('input[name="preferencias_especie[]"]:checked');
                if (opcoesEspecie.length === 0) {
                    mostrarModalFeedback('aviso', "Selecione pelo menos uma preferência de espécie para montar o seu feed.");
                    return false;
                }
            }

            return true;
        });
    }

    function validarEnvioFinal() {
        const aceiteTermos = document.querySelector('input[name="aceite_termos"]');
        if (!aceiteTermos || !aceiteTermos.checked) {
            mostrarModalFeedback('aviso', "Você deve ler e concordar com os Termos de Responsabilidade para continuar.");
            aceiteTermos.focus();
            return false;
        }
        return true;
    }

    function toggleOutrasEspecies() {
        const checkbox = document.getElementById('checkbox-outras-especies');
        const container = document.getElementById('container-outras-especies');
        const checkboxesOutras = container ? container.querySelectorAll('.check-outras') : [];

        if (checkbox && container) {
            if (checkbox.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                checkboxesOutras.forEach(cb => cb.checked = false);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        OnboardingManager.init({
            totalEtapas: 6,
            urlSelecionarPerfil: "<?= URL_BASE ?>/onboarding",
            validarEnvioFinal: validarEnvioFinal
        });
    });
</script>

<!-- Utiliza o script onboarding.js compartilhado para gerenciar as chamadas AJAX e Etapas -->
<script src="<?= e(URL_BASE) ?>/assets/js/onboarding.js"></script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>