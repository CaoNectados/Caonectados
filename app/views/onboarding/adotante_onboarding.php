<?php
require_once __DIR__ . '/../templates/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<form id="form-onboarding-adotante" action="<?= URL_BASE ?>/onboarding/salvar-adotante" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto p-4">

    <input type="hidden" name="foto_perfil_cortada" id="foto_perfil_cortada">

    <!-- BARRA DE PROGRESSO -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-8 rounded-full bg-primary/20 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-8 rounded-full bg-primary/20 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-8 rounded-full bg-primary/20 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-8 rounded-full bg-primary/20 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-8 rounded-full bg-primary/20 transition-colors duration-300"></div>
        <div id="progresso-6" class="h-2 w-8 rounded-full bg-primary/20 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR -->
    <button type="button" id="btn-voltar-global" onclick="OnboardingManager.voltarEtapa()" class="mb-4 text-2xl font-bold cursor-pointer transition hover:opacity-75 text-primary" title="Voltar">
        &#129144;
    </button>

    <!-- ETAPA 1 -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Como podemos te chamar?</h1>

            <div class="flex flex-col items-center mb-6 mt-4">
                <div class="relative cursor-pointer group" onclick="document.getElementById('input-arquivo-perfil').click()">
                    <div class="w-28 h-28 rounded-full border-4 border-rosa-3 overflow-hidden bg-surface flex items-center justify-center shadow">
                        <span id="foto-placeholder-adotante" class="text-text-muted text-3xl font-bold">&#128247;</span>
                        <img id="preview-foto-perfil" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="absolute bottom-0 right-0 bg-surface p-1.5 rounded-full shadow border border-rosa-2 text-text-muted text-xs group-hover:bg-rosa-1">
                        ✏️
                    </div>
                </div>
                <span class="text-xs text-text-muted mt-2">Toque para escolher e ajustar sua foto</span>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperPerfil(event)">
            </div>

            <div class="mb-4 text-left">
                <label for="nome_usuario" class="label-padrao">Seu Nome *</label>
                <input type="text" name="nome_usuario" id="nome_usuario" placeholder="Digite seu nome aqui" class="input-padrao">
            </div>

            <div class="mb-4 text-left">
                <label for="descricao" class="label-padrao">Fale um pouco sobre você (Opcional)</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Por que você quer adotar um pet?" class="input-padrao"></textarea>
            </div>

            <div class="space-y-4 mb-6 text-left">
                <div>
                    <label for="dt_nasc" class="label-padrao">Data de Nascimento *</label>
                    <input type="date" name="dt_nasc" id="dt_nasc" max="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" class="input-padrao">
                </div>
                <div>
                    <label for="telefone" class="label-padrao">Telefone (Opcional)</label>
                    <input type="tel" name="telefone" id="telefone" placeholder="(00) 00000-0000" class="input-padrao">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Ir para Preferências</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 2: Convivência -->
    <div class="etapa-form hidden" id="etapa-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Convivência</h1>
            <p class="text-sm text-text-muted">Conte-nos um pouco mais sobre sua rotina com outros pets ou família.</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label class="label-padrao">Possui filhos ou crianças em casa? *</label>
                <select name="possui_criancas" id="possui_criancas" class="input-padrao">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>
            <div>
                <label class="label-padrao">Possui outros pets? *</label>
                <select name="possui_outros_pets" id="possui_outros_pets" class="input-padrao">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 3: Moradia -->
    <div class="etapa-form hidden" id="etapa-3">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Moradia</h1>
            <p class="text-sm text-text-muted">Complete esse formulário para acharmos o pet Perfeito para você!</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="tipo_moradia" class="label-padrao">Tipo de moradia *</label>
                <select name="tipo_moradia" id="tipo_moradia" class="input-padrao">
                    <option value="" disabled selected>Escolha</option>
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                    <option value="chacara">Chácara/Sítio</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div>
                <label for="espaco_interior" class="label-padrao">Espaço interior *</label>
                <select name="espaco_interior" id="espaco_interior" class="input-padrao">
                    <option value="" disabled selected>Escolha</option>
                    <option value="pequeno">Pequeno</option>
                    <option value="medio">Médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>
            <div>
                <label for="espaco_externo" class="label-padrao">Espaço externo *</label>
                <select name="espaco_externo" id="espaco_externo" class="input-padrao">
                    <option value="" disabled selected>Escolha</option>
                    <option value="nenhum">Não possui quintal</option>
                    <option value="pequeno">Quintal pequeno</option>
                    <option value="medio">Quintal médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 4: Região -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Selecione a sua localização</h1>
            <p class="text-sm text-text-muted">Selecione a sua localização para podermos encontrar os pets mais próximos de você e dar início a um novo match!</p>
        </div>

        <div class="mb-4 relative text-left">
            <label for="input-busca-bairro" class="label-padrao">Pesquise seu Bairro / Região *</label>
            <input type="text" name="busca_bairro_texto" id="input-busca-bairro" list="lista-regioes" placeholder="Digite o nome do seu bairro..." autocomplete="off" oninput="OnboardingManager.sincronizarRegiaoId()" class="input-padrao">
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
            <p id="erro-bairro-invalido" class="text-xs text-erro mt-1 hidden">Selecione um bairro válido da lista fornecida.</p>
        </div>

        <div class="mb-4 text-left">
            <label for="obs_casa" class="label-padrao">Logradouro e Complemento *</label>
            <input type="text" name="obs_casa" id="obs_casa" placeholder="Ex: Avenida Brasil, Apto 42..." class="input-padrao">
        </div>
        <div class="mb-6 text-left">
            <label for="numero" class="label-padrao">Número da Residência *</label>
            <input type="text" name="numero" id="numero" placeholder="Ex: 123, S/N" class="input-padrao">
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Ir para Preferências</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Preferências -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Suas Preferências</h1>
            <p class="text-sm text-text-muted mb-6">Selecione suas preferências para montarmos o seu feed perfeito. Você pode escolher mais de uma opção!</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <span class="font-bold block mb-2 text-text-dark">Espécie:</span>
                <div id="container-especies-padrao" class="space-y-2">
                    <?php
                    $especie1 = null;
                    $especie2 = null;
                    $outrasEspecies = [];
                    if (!empty($especies)) {
                        foreach ($especies as $esp) {
                            $id = is_array($esp) ? $esp['especie_id'] : $esp->getEspecieId();
                            $nome = is_array($esp) ? $esp['nome'] : $esp->getNome();
                            if ((int)$id === 1) $especie1 = ['id' => $id, 'nome' => $nome];
                            elseif ((int)$id === 2) $especie2 = ['id' => $id, 'nome' => $nome];
                            else $outrasEspecies[] = ['id' => $id, 'nome' => $nome];
                        }
                    }
                    ?>
                    <div id="wrapper-especie-1">
                        <?php if ($especie1): ?>
                            <label id="label-especie-<?= $especie1['id'] ?>" class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                                <input type="checkbox" name="preferencias_especie[]" value="<?= $especie1['id'] ?>" class="check-especie"> <?= e($especie1['nome']) ?>
                            </label>
                        <?php endif; ?>
                    </div>
                    <div id="wrapper-especie-2">
                        <?php if ($especie2): ?>
                            <label id="label-especie-<?= $especie2['id'] ?>" class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                                <input type="checkbox" name="preferencias_especie[]" value="<?= $especie2['id'] ?>" class="check-especie"> <?= e($especie2['nome']) ?>
                            </label>
                        <?php endif; ?>
                    </div>
                    <label id="label-opcao-outros" class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition <?= empty($outrasEspecies) ? 'hidden' : '' ?>">
                        <input type="checkbox" id="checkbox-outras-especies" onchange="toggleOutrasEspecies()" value="outros"> Outros
                    </label>
                </div>
                <div id="container-outras-especies" class="hidden mt-3 p-3 border border-rosa-2 rounded-lg bg-surface">
                    <label class="block font-medium mb-2 text-sm text-text-dark">Selecione outras espécies desejadas:</label>
                    <div id="lista-outras-especies-dinamica" class="space-y-2 max-h-40 overflow-y-auto">
                        <?php foreach ($outrasEspecies as $esp): ?>
                            <label id="label-especie-<?= $esp['id'] ?>" class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" name="preferencias_especie[]" value="<?= $esp['id'] ?>" class="check-outras check-especie">
                                <?= e($esp['nome']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div>
                <span class="font-bold block mb-2 text-text-dark">Porte:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                        <input type="checkbox" name="preferencias_porte[]" value="pequeno"> Pequeno
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                        <input type="checkbox" name="preferencias_porte[]" value="medio"> Médio
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                        <input type="checkbox" name="preferencias_porte[]" value="grande"> Grande
                    </label>
                </div>
            </div>
            <div>
                <span class="font-bold block mb-2 text-text-dark">Sexo:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                        <input type="checkbox" name="preferencias_sexo[]" value="femea"> Fêmea
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-surface border border-rosa-2 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition">
                        <input type="checkbox" name="preferencias_sexo[]" value="macho"> Macho
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Ler os Termos</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 6: Termos -->
    <div class="etapa-form hidden" id="etapa-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Termos de Responsabilidade</h1>
            <p class="text-sm text-text-muted">Por favor, leia atentamente as regras da nossa plataforma antes de finalizar.</p>
        </div>

        <div class="bg-surface border border-rosa-2 rounded-xl p-5 mb-6 shadow-sm text-left max-h-60 overflow-y-auto text-sm text-text-dark/80 space-y-3">
            <p><strong>1. Maioridade Civil:</strong> Declaro ser maior de 18 (dezoito) anos e ter plena capacidade civil para utilizar o sistema.</p>
            <p><strong>2. Proteção de Dados (LGPD):</strong> Autorizo a coleta e o armazenamento dos meus dados pessoais necessários para a criação do perfil e intermediação de adoções.</p>
            <p><strong>3. Responsabilidade da Plataforma:</strong> Compreendo que o CãoNectados atua exclusivamente como um <strong>intermediador digital</strong> (vitrine) para facilitar o encontro entre animais e adotantes.</p>
            <p><strong>4. Isenção Legal:</strong> A plataforma <strong>não possui</strong> qualquer responsabilidade legal, logística, veterinária ou financeira sobre o processo de adoção, sendo esta responsabilidade inteiramente do adotante e do protetor/ONG envolvidos.</p>
        </div>

        <div class="mb-6 text-left">
            <label class="flex items-center gap-3 cursor-pointer p-4 bg-surface border border-rosa-2 rounded-xl hover:bg-rosa-1/20 transition">
                <input type="checkbox" name="aceite_termos" id="aceite_termos" class="w-6 h-6 text-rosaAlerta rounded focus:ring-rosaAlerta">
                <span class="text-sm text-text-dark font-medium">
                    Li, compreendi e concordo com os Termos de Responsabilidade.
                </span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Ir para o Feed</span>
            <button type="button" onclick="submeterFormularioAdotante()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-3 transition">&rarr;</button>
        </div>
    </div>
</form>

<!-- MODAL CROPPER -->
<div id="modal-cropper" class="fixed inset-0 bg-preto/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 class="font-shantell text-xl font-bold mb-1 text-text-dark">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Arraste e use o zoom para centralizar.</p>
        <div class="w-full h-64 bg-surface rounded-2xl overflow-hidden mb-4 flex items-center justify-center">
            <img id="imagem-para-cortar" src="" alt="Cortar" class="max-block max-full">
        </div>
        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropper()" class="flex-1 bg-cinzaMarrom/30 text-text-dark py-2.5 rounded-xl font-bold text-sm hover:bg-cinzaMarrom/50 transition">Cancelar</button>
            <button type="button" onclick="salvarRecorte()" class="flex-1 bg-rosaAlerta text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">Aplicar</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="<?= e(URL_BASE) ?>/assets/js/onboarding.js"></script>
<script src="<?= e(URL_BASE) ?>/assets/js/autosave.js"></script>

<script>
    // (mesmo JavaScript, sem alterações)
    let cropper = null;
    function iniciarCropperPerfil(event) { /* ... */ }
    function fecharModalCropper() { /* ... */ }
    function salvarRecorte() { /* ... */ }
    async function sincronizarEspeciesAtivasAjax() { /* ... */ }
    function proximaEtapa() { /* ... */ }
    function toggleOutrasEspecies() { /* ... */ }
    async function submeterFormularioAdotante() { /* ... */ }
    document.addEventListener('DOMContentLoaded', function() { /* ... */ });
</script>

<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>