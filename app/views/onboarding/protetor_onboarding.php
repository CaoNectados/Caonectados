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

    <!-- ETAPA 1: Validação -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark"><?= $titulo_etapa1 ?></h1>
            <p class="text-sm text-text-muted">Para garantir a segurança dos nossos pets e adotantes, <?= $texto_etapa1 ?></p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="nome_fantasia" class="label-padrao"><?= $label_nome ?></label>
                <input type="text" name="nome_fantasia" id="nome_fantasia" placeholder="<?= $placeholder_nome ?>" class="input-padrao">
            </div>
            <div>
                <label for="cnpj_cpf" class="label-padrao"><?= $label_doc ?></label>
                <input type="text" name="cnpj_cpf" id="cnpj_cpf" placeholder="<?= $placeholder_doc ?>" class="input-padrao">
                <p id="erro-documento" class="text-xs text-erro mt-1 hidden">O documento informado é inválido.</p>
            </div>
            <?php if ($isOng): ?>
                <div>
                    <label for="data_abertura_cnpj" class="label-padrao">Data de Abertura do CNPJ *</label>
                    <input type="date" id="data_abertura_cnpj" name="data_abertura_cnpj" max="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($old_input['data_abertura_cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-padrao">
                </div>
            <?php else: ?>
                <div>
                    <label for="dt_nasc" class="label-padrao">Data de Nascimento *</label>
                    <input type="date" id="dt_nasc" name="dt_nasc" max="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($old_input['dt_nasc'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-padrao">
                </div>
            <?php endif; ?>
            <div>
                <label for="telefone" class="label-padrao">Telefone / WhatsApp *</label>
                <input type="tel" name="telefone" id="telefone" placeholder="(00) 00000-0000" class="input-padrao">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 2: Localização -->
    <div class="etapa-form hidden" id="etapa-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Selecione a sua localização</h1>
            <p class="text-sm text-text-muted">Onde fica a sua sede/atuação? Informe a localização para que adotantes da região encontrem seus animais.</p>
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
            <label for="numero" class="label-padrao">Número do Local *</label>
            <input type="text" name="numero" id="numero" placeholder="Ex: 123, S/N" class="input-padrao">
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 3: Dados da Página -->
    <div class="etapa-form hidden" id="etapa-3">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark"><?= $titulo_pagina ?></h1>
            <p class="text-sm text-text-muted">Agora, vamos configurar como você aparecerá para a comunidade.</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="descricao" class="label-padrao">Descrição / Causa *</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Mínimo de 15 caracteres apresentando a causa..." class="input-padrao"></textarea>
            </div>
            <div>
                <label for="instagram" class="label-padrao">Link do Instagram</label>
                <input type="text" name="instagram" id="instagram" placeholder="https://instagram.com/seu_perfil" class="input-padrao">
            </div>
            <div>
                <label for="facebook" class="label-padrao">Link do Facebook</label>
                <input type="text" name="facebook" id="facebook" placeholder="https://facebook.com/seu_perfil" class="input-padrao">
            </div>
            <div>
                <label for="chave_pix" class="label-padrao">Chave PIX para doações</label>
                <input type="text" name="chave_pix" id="chave_pix" placeholder="E-mail, CNPJ, CPF, Celular ou Chave Aleatória" class="input-padrao">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 4: Fotos -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold mb-2 font-shantell text-text-dark">Personalize sua página</h2>
            <p class="text-sm text-text-muted mb-6">Adicione imagens para que a comunidade reconheça o seu trabalho.</p>

            <div class="mb-6">
                <label class="label-padrao text-left">Foto de Perfil (Opcional)</label>
                <div class="flex justify-center mb-2">
                    <div class="w-32 h-32 rounded-full border-4 border-rosa-3 bg-surface flex items-center justify-center overflow-hidden relative shadow-sm cursor-pointer" onclick="document.getElementById('input-arquivo-perfil').click()">
                        <span id="foto-placeholder-perfil" class="text-text-muted text-4xl font-bold">&#128247;</span>
                        <img id="preview-foto-perfil" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('input-arquivo-perfil').click()" class="btn-primario text-sm">
                    Ajustar Perfil
                </button>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperFoto(event, 'perfil')">
            </div>

            <hr class="my-4 border-rosa-2">

            <div class="mb-2">
                <label class="label-padrao text-left">Foto de Capa/Fundo (Opcional)</label>
                <p class="text-xs text-text-muted mb-2 text-left">Essa imagem ficará no topo da sua página de perfil.</p>
                <div class="w-full h-36 bg-surface rounded-lg border-2 border-dashed border-rosa-2 flex items-center justify-center overflow-hidden relative mb-2 cursor-pointer" onclick="document.getElementById('input-arquivo-fundo').click()">
                    <span id="fundo-placeholder-capa" class="text-text-muted text-sm">Preview da Capa</span>
                    <img id="preview-foto-fundo" src="" alt="Capa" class="w-full h-full object-cover hidden">
                </div>
                <button type="button" onclick="document.getElementById('input-arquivo-fundo').click()" class="btn-primario text-sm">
                    Ajustar Capa
                </button>
                <input type="file" id="input-arquivo-fundo" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperFoto(event, 'fundo')">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-primary text-xl font-bold flex items-center justify-center text-white hover:bg-roxo1 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Comprovante -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <div class="w-24 h-24 rounded-full border-4 border-primary/30 bg-surface flex items-center justify-center shadow-sm">
                    <span class="text-primary text-4xl font-bold">&#128193;</span>
                </div>
            </div>
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark">Comprove a sua atividade</h1>
            <?php if ($isOng): ?>
                <p class="text-sm text-text-muted mb-6">Anexe o cartão CNPJ atualizado ou um documento de registro da prefeitura comprovando a existência da ONG.</p>
            <?php else: ?>
                <p class="text-sm text-text-muted mb-6">Anexe uma foto de um documento de identidade válido com foto (como RG ou CNH).</p>
            <?php endif; ?>
            <div class="mb-4">
                <label for="comprovante_documento" class="btn-primario cursor-pointer">
                    Anexar documento
                </label>
                <input type="file" name="comprovante_documento" id="comprovante_documento" accept=".pdf, .jpg, .jpeg, .png" onchange="exibirNomeArquivo(this, 'nome-arquivo-selecionado')" class="hidden">
            </div>
            <p id="nome-arquivo-selecionado" class="text-xs text-sucesso font-bold mt-2"></p>
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
            <span class="font-medium text-text-dark">Finalizar Cadastro</span>
            <button type="button" onclick="submeterFormularioProtetor()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-3 transition">&rarr;</button>
        </div>
    </div>
</form>

<!-- MODAL CROPPER -->
<div id="modal-cropper" class="fixed inset-0 bg-preto/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface rounded-3xl max-w-md w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 id="modal-cropper-titulo" class="font-shantell text-xl font-bold mb-1 text-text-dark">Ajustar Foto</h3>
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
    let alvoAtual = null;
    function iniciarCropperFoto(event, alvo) { /* ... */ }
    function fecharModalCropper() { /* ... */ }
    function salvarRecorte() { /* ... */ }
    function proximaEtapa() { /* ... */ }
    async function submeterFormularioProtetor() { /* ... */ }
    document.addEventListener('DOMContentLoaded', function() { /* ... */ });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>