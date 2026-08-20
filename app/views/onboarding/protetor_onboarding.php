<?php
/** @var array $regioes */
/** @var string $tipo_perfil */
/** @var bool|null $modoEdicao */
/** @var array|null $dadosProtetor */

require_once __DIR__ . '/../templates/header.php';

$tipo_perfil = $tipo_perfil ?? 'cpf';
$isOng = ($tipo_perfil === 'cnpj');
$modoEdicao = $modoEdicao ?? false;
$d = $dadosProtetor ?? [];

$titulo_etapa1    = $modoEdicao ? "Corrija os dados da solicitação" : ($isOng ? "Vamos validar sua ONG" : "Vamos validar seu perfil");
$texto_etapa1     = $isOng ? "Precisamos confirmar os dados da sua instituição." : "Precisamos confirmar seus dados como protetor independente.";
$label_nome       = $isOng ? "Nome da ONG *" : "Seu Nome (Protetor) *";
$placeholder_nome = $isOng ? "Ex: Ong Vida Animal" : "Ex: Maria da Silva";
$label_doc        = $isOng ? "Digite o CNPJ da ONG *" : "Digite o seu CPF *";
$placeholder_doc  = $isOng ? "00.000.000/0000-00" : "000.000.000-00";
$titulo_pagina    = $isOng ? "Página da ONG" : "Sua Página de Protetor";

$fotoPerfilUrl = !empty($d['foto_perfil']) ? ((strpos($d['foto_perfil'], 'http') === 0) ? $d['foto_perfil'] : URL_BASE . '/' . ltrim($d['foto_perfil'], '/')) : '';
$fotoFundoUrl  = !empty($d['foto_fundo']) ? ((strpos($d['foto_fundo'], 'http') === 0) ? $d['foto_fundo'] : URL_BASE . '/' . ltrim($d['foto_fundo'], '/')) : '';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<form id="form-onboarding-protetor" action="<?= URL_BASE ?>/onboarding/salvar-protetor" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto p-4 text-text-dark">

    <input type="hidden" name="tipo_documento" id="tipo_documento" value="<?= htmlspecialchars($tipo_perfil, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="foto_perfil_cortada" id="foto_perfil_cortada">
    <input type="hidden" name="foto_fundo_cortada" id="foto_fundo_cortada">

    <!-- BARRA DE PROGRESSO -->
    <!-- Classe base precisa ser bg-gray-300: OnboardingManager.atualizarVisualEtapas() (onboarding.js)
         alterna especificamente entre bg-gray-300 (pendente) e bg-green-500 (concluída/atual). -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-6" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR -->
    <button type="button" id="btn-voltar-global" onclick="OnboardingManager.voltarEtapa()" class="mb-4 text-xl font-bold cursor-pointer transition hover:opacity-75 text-primary" title="Voltar">
        <img src="<?= URL_BASE ?>/assets/icons/geral/seta-voltar.svg" alt="Voltar" class="w-8 h-8">
    </button>


    <!-- ETAPA 1: Validação -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <img src="<?= URL_BASE ?>/assets/img/mascote-validacao.png" alt="" class="w-40 h-40 sm:w-56 sm:h-56 mx-auto mb-5 object-contain" onerror="this.style.display='none';">
            <h1 class="font-shantell text-2xl font-bold mb-2 text-text-dark dark:text-white"><?= $titulo_etapa1 ?></h1>
            <p class="text-sm text-text-muted">Para garantir a segurança dos nossos pets e adotantes, <?= $texto_etapa1 ?></p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="nome_fantasia" class="label-padrao"><?= $label_nome ?></label>
                <input type="text" name="nome_fantasia" id="nome_fantasia" value="<?= htmlspecialchars($d['nome_fantasia'] ?? ($d['usuario_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= $placeholder_nome ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
            </div>
            <div>
                <label for="cnpj_cpf" class="label-padrao"><?= $label_doc ?></label>
                <input type="text" name="cnpj_cpf" id="cnpj_cpf" value="<?= htmlspecialchars($d['codigo_documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= $placeholder_doc ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                <p id="erro-documento" class="text-xs text-erro mt-1 hidden">O documento informado é inválido.</p>
            </div>

            <?php if ($isOng): ?>
                <div>
                    <label for="data_abertura_cnpj" class="label-padrao">Data de Abertura do CNPJ *</label>
                    <input type="date" id="data_abertura_cnpj" name="data_abertura_cnpj" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($d['data_abertura_cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                </div>
            <?php else: ?>
                <div>
                    <label for="dt_nasc" class="label-padrao">Data de Nascimento *</label>
                    <input type="date" id="dt_nasc" name="dt_nasc" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($d['dt_nasc'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                </div>
            <?php endif; ?>

            <div>
                <label for="telefone" class="label-padrao">Telefone / WhatsApp *</label>
                <input type="tel" name="telefone" id="telefone" value="<?= htmlspecialchars($d['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="(45) 90000-0000" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="text-sm font-medium text-text-muted">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-white text-xl font-bold flex items-center justify-center shadow hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 2: Localização -->
    <div class="etapa-form hidden" id="etapa-2">
        <div class="text-center mb-6">
            <div class="relative w-40 h-40 sm:w-56 sm:h-56 mx-auto mb-5">
                <img src="<?= URL_BASE ?>/assets/img/mascote-localizacao.png" alt="" class="absolute inset-0 w-full h-full object-contain scale-150 pointer-events-none" onerror="this.style.display='none';">
            </div>
            <h1 class="font-shantell text-2xl font-bold mb-2 text-text-dark dark:text-white">Selecione a sua localização</h1>
            <p class="text-sm text-text-muted">Onde fica a sua sede/atuação? Informe a localização para que adotantes da região encontrem seus animais.</p>
        </div>

        <div class="mb-4 text-left">
            <label for="input-busca-bairro" class="label-padrao">Pesquise seu Bairro / Região *</label>
            <input type="text" name="busca_bairro_texto" id="input-busca-bairro" list="lista-regioes" value="<?= htmlspecialchars($d['nome_regiao'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Digite o nome do seu bairro..." autocomplete="off" oninput="OnboardingManager.sincronizarRegiaoId()" class="input-padrao input-com-seta bg-branco dark:bg-preto2 dark:text-white">

            <datalist id="lista-regioes">
                <?php if (!empty($regioes)): ?>
                    <?php foreach ($regioes as $regiao): ?>
                        <?php
                        $regId = is_array($regiao) ? $regiao['regiao_id'] : $regiao->getRegiaoId();
                        $regNome = is_array($regiao) ? $regiao['nome_regiao'] : $regiao->getNomeRegiao();
                        ?>
                        <option data-id="<?= $regId ?>" value="<?= htmlspecialchars($regNome, ENT_QUOTES, 'UTF-8') ?>"></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </datalist>

            <input type="hidden" name="regiao_id" id="regiao_id_hidden" value="<?= htmlspecialchars($d['regiao_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <p id="erro-bairro-invalido" class="text-xs text-erro mt-1 hidden">Selecione um bairro válido da lista fornecida.</p>
        </div>

        <div class="mb-4 text-left">
            <label for="obs_casa" class="label-padrao">Logradouro e Complemento *</label>
            <input type="text" name="obs_casa" id="obs_casa" value="<?= htmlspecialchars($d['logradouro'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Avenida Brasil, Apto 42..." class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
        </div>
        <div class="mb-6 text-left">
            <label for="numero" class="label-padrao">Número do Local *</label>
            <input type="text" name="numero" id="numero" value="<?= htmlspecialchars($d['numero'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: 123, S/N" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
        </div>

        <div class="flex items-center justify-between mt-8">
            <button type="button" onclick="OnboardingManager.voltarEtapa()" class="text-sm text-text-muted hover:text-text-dark dark:hover:text-white underline">Voltar</button>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-white text-xl font-bold flex items-center justify-center shadow hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 3: Dados da Página -->
    <div class="etapa-form hidden" id="etapa-3">
        <div class="text-center mb-6">
            <h1 class="font-shantell text-2xl font-bold mb-2 text-text-dark dark:text-white"><?= $titulo_pagina ?></h1>
            <p class="text-sm text-text-muted">Configure como você aparecerá para a comunidade.</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="descricao" class="label-padrao">Descrição / Causa *</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Mínimo de 15 caracteres apresentando a causa..." class="input-padrao bg-branco dark:bg-preto2 dark:text-white"><?= htmlspecialchars($d['pagina_descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div>
                <label for="instagram" class="label-padrao">Link do Instagram</label>
                <input type="text" name="instagram" id="instagram" placeholder="https://instagram.com/seu_perfil" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
            </div>
            <div>
                <label for="facebook" class="label-padrao">Link do Facebook</label>
                <input type="text" name="facebook" id="facebook" placeholder="https://facebook.com/seu_perfil" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
            </div>
            <div>
                <label for="chave_pix" class="label-padrao">Chave PIX para doações</label>
                <input type="text" name="chave_pix" id="chave_pix" value="<?= htmlspecialchars($d['chave_pix'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="E-mail, CNPJ, CPF ou Chave Aleatória" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <button type="button" onclick="OnboardingManager.voltarEtapa()" class="text-sm text-text-muted hover:text-text-dark dark:hover:text-white underline">Voltar</button>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-white text-xl font-bold flex items-center justify-center shadow hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 4: Fotos -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <div class="relative w-36 h-36 sm:w-48 sm:h-48 mx-auto mb-5">
                <img src="<?= URL_BASE ?>/assets/img/mascote-foto-perfil.png" alt="" class="absolute inset-0 w-full h-full object-contain scale-150 pointer-events-none" onerror="this.style.display='none';">
            </div>
            <h2 class="font-shantell text-xl font-bold mb-2 text-text-dark dark:text-white">Personalize sua página</h2>
            <p class="text-sm text-text-muted mb-6">Adicione imagens para identificação.</p>

            <div class="mb-6">
                <label class="block font-medium mb-2 text-left text-sm text-text-dark dark:text-white">Foto de Perfil (Opcional)</label>
                <div class="flex justify-center mb-2">
                    <div class="w-32 h-32 rounded-full border-4 border-rosa-2 bg-surface dark:bg-preto2 flex items-center justify-center overflow-hidden relative shadow-sm cursor-pointer" onclick="document.getElementById('input-arquivo-perfil').click()">
                        <img id="foto-placeholder-perfil" src="<?= URL_BASE ?>/assets/img/perfil-placeholder.png" alt="Adicionar foto de perfil" class="w-full h-full object-cover opacity-60 <?= !empty($fotoPerfilUrl) ? 'hidden' : '' ?>">
                        <img id="preview-foto-perfil" src="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de Perfil" class="w-full h-full object-cover <?= empty($fotoPerfilUrl) ? 'hidden' : '' ?>">
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('input-arquivo-perfil').click()" class="btn-secundario text-xs py-1.5 px-4 rounded-xl">
                    <?= !empty($fotoPerfilUrl) ? 'Trocar Foto' : 'Ajustar Perfil' ?>
                </button>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperFoto(event, 'perfil')">
            </div>

            <hr class="my-4 border-rosa-2 dark:border-preto3">

            <!-- Foto de Capa -->
            <div class="mb-2">
                <label class="block font-medium mb-1 text-left text-sm text-text-dark dark:text-white">Foto de Capa/Fundo (Opcional)</label>
                <div class="w-full h-36 bg-surface dark:bg-preto2 rounded-xl border-2 border-dashed border-rosa-2 dark:border-preto3 flex items-center justify-center overflow-hidden relative mb-2 cursor-pointer" onclick="document.getElementById('input-arquivo-fundo').click()">
                    <span id="fundo-placeholder-capa" class="text-text-muted text-sm <?= !empty($fotoFundoUrl) ? 'hidden' : '' ?>">Preview da Capa</span>
                    <img id="preview-foto-fundo" src="<?= htmlspecialchars($fotoFundoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Capa" class="w-full h-full object-cover <?= empty($fotoFundoUrl) ? 'hidden' : '' ?>">
                </div>
                <button type="button" onclick="document.getElementById('input-arquivo-fundo').click()" class="btn-secundario text-xs py-1.5 px-4 rounded-xl">
                    <?= !empty($fotoFundoUrl) ? 'Trocar Capa' : 'Ajustar Capa' ?>
                </button>
                <input type="file" id="input-arquivo-fundo" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperFoto(event, 'fundo')">
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <button type="button" onclick="OnboardingManager.voltarEtapa()" class="text-sm text-text-muted hover:text-text-dark dark:hover:text-white underline">Voltar</button>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-white text-xl font-bold flex items-center justify-center shadow hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Comprovante -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <img src="<?= URL_BASE ?>/assets/img/mascote-comprovante.png" alt="" class="w-40 h-40 sm:w-56 sm:h-56 mx-auto mb-5 object-contain" onerror="this.style.display='none';">

            <h1 class="font-shantell text-2xl font-bold mb-2 text-text-dark dark:text-white">Comprove a sua atividade</h1>
            
            <?php if ($isOng): ?>
                <p class="text-sm text-text-muted mb-6">Anexe o cartão CNPJ atualizado ou um comprovante municipal da ONG.</p>
            <?php else: ?>
                <p class="text-sm text-text-muted mb-6">Anexe uma foto de um documento oficial com foto (RG ou CNH).</p>
            <?php endif; ?>

            <div class="mb-4">
                <label for="comprovante_documento" class="btn-primario py-2.5 px-6 rounded-xl cursor-pointer inline-flex items-center gap-2">
                    <img src="<?= URL_BASE ?>/assets/icons/upload.svg" alt="" class="w-4 h-4"> <?= $modoEdicao ? 'Substituir documento' : 'Anexar documento' ?>
                </label>
                <input type="file" name="comprovante_documento" id="comprovante_documento" accept=".pdf, .jpg, .jpeg, .png" onchange="exibirNomeArquivo(this, 'nome-arquivo-selecionado')" class="hidden">
            </div>
            <p id="nome-arquivo-selecionado" class="text-xs text-sucesso font-bold mt-2">
                <?= !empty($d['comprovante_documento']) ? 'Documento atual mantido no servidor' : '' ?>
            </p>
        </div>

        <div class="flex items-center justify-between mt-8">
            <button type="button" onclick="OnboardingManager.voltarEtapa()" class="text-sm text-text-muted hover:text-text-dark dark:hover:text-white underline">Voltar</button>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-white text-xl font-bold flex items-center justify-center shadow hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 6: Termos -->
    <div class="etapa-form hidden" id="etapa-6">
        <div class="text-center mb-6">
            <h1 class="font-shantell text-2xl font-bold mb-2 text-text-dark dark:text-white">Termos de Responsabilidade</h1>
            <p class="text-sm text-text-muted">Confirme seus dados e envie para validação.</p>
        </div>

        <div class="bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-xl p-4 mb-4 text-xs text-text-dark dark:text-white space-y-2 text-left max-h-48 overflow-y-auto">
            <p><strong>1. Maioridade Civil:</strong> Declaro ser maior de 18 anos e possuir capacidade civil.</p>
            <p><strong>2. Checagem de Dados:</strong> Concordo com a checagem da documentação pela equipe do CãoNectados.</p>
            <p><strong>3. Responsabilidade:</strong> Assumo inteira responsabilidade sobre os dados e animais cadastrados.</p>
        </div>

        <label class="flex items-center gap-3 p-3 bg-rosa-1/30 dark:bg-preto2 rounded-xl mb-6 text-left cursor-pointer border border-rosa-2 dark:border-preto3">
            <input type="checkbox" name="aceite_termos" id="aceite_termos" <?= $modoEdicao ? 'checked' : '' ?> class="w-5 h-5 rounded text-primary">
            <span class="text-xs text-text-dark dark:text-white font-medium">Li e concordo com os termos de validação.</span>
        </label>

        <div class="flex items-center justify-between mt-8">
            <button type="button" onclick="OnboardingManager.voltarEtapa()" class="text-sm text-text-muted hover:text-text-dark dark:hover:text-white underline">Voltar</button>
            <button type="button" onclick="submeterFormularioProtetor()" class="btn-primario py-3 px-6 rounded-full font-bold shadow-md cursor-pointer">
                <?= $modoEdicao ? 'Reenviar Solicitação' : 'Finalizar Cadastro' ?>
            </button>
        </div>
    </div>
</form>

<!-- MODAL CROPPER REUTILIZÁVEL -->
<div id="modal-cropper" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface dark:bg-preto1 rounded-3xl max-w-md w-full p-6 flex flex-col items-center shadow-2xl border border-rosa-3">
        <h3 id="modal-cropper-titulo" class="font-shantell text-xl font-bold mb-1 text-text-dark dark:text-white">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Arraste e use o zoom para centralizar.</p>
        
        <div class="w-full h-64 bg-surface dark:bg-preto2 rounded-2xl overflow-hidden mb-4 flex items-center justify-center border border-cinzaMarrom/30">
            <img id="imagem-para-cortar" src="" alt="Cortar" class="max-w-full max-h-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropper()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
            <button type="button" onclick="salvarRecorte()" class="flex-1 btn-primario py-2.5 rounded-xl font-bold text-sm">Aplicar</button>
        </div>
    </div>
</div>

<script src="<?= URL_BASE ?>/assets/js/onboarding.js"></script>

<?php if (!$modoEdicao): ?>
    <script src="<?= URL_BASE ?>/assets/js/autosave.js"></script>
<?php endif; ?>

<script>
    const modoEdicao = <?= json_encode($modoEdicao) ?>;
    const jaPossuiComprovante = <?= json_encode(!empty($d['comprovante_documento'])) ?>;
    let cropper = null;
    let alvoAtual = null;

    function exibirNomeArquivo(input, idDestino) {
        const span = document.getElementById(idDestino);
        if (input.files && input.files[0]) {
            span.innerText = "Arquivo selecionado: " + input.files[0].name;
        }
    }

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
    }

    function salvarRecorte() {
        if (!cropper) return;
        const isPerfil = (alvoAtual === 'perfil');
        const options = isPerfil ? { width: 400, height: 400 } : { width: 1200, height: 675 };
        const base64String = cropper.getCroppedCanvas(options).toDataURL('image/png');

        if (isPerfil) {
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
                    if (docLimpo.length !== 14) {
                        msgErroEspecifica = "O CNPJ deve conter exatamente 14 números. Você digitou " + docLimpo.length + ".";
                    } else if (!CaonectadosValidator.isCnpjValido(docLimpo)) {
                        msgErroEspecifica = "O CNPJ digitado é inválido.";
                    } else {
                        docValido = true;
                    }
                } else {
                    if (docLimpo.length !== 11) {
                        msgErroEspecifica = "O CPF deve conter exatamente 11 números. Você digitou " + docLimpo.length + ".";
                    } else if (!CaonectadosValidator.isCpfValido(docLimpo)) {
                        msgErroEspecifica = "O CPF digitado é inválido.";
                    } else {
                        docValido = true;
                    }
                }

                if (!docValido) {
                    if (msgErroDoc) {
                        msgErroDoc.innerText = msgErroEspecifica;
                        msgErroDoc.classList.remove('hidden');
                    }
                    inputDoc.classList.add('border-erro', 'ring-rosa-2');
                    mostrarModalFeedback('erro', msgErroEspecifica);
                    inputDoc.focus();
                    return false;
                } else if (msgErroDoc) {
                    msgErroDoc.classList.add('hidden');
                    inputDoc.classList.remove('border-erro', 'ring-rosa-2');
                }

                if (tipoDoc === 'cnpj') {
                    const dataAbertura = document.getElementById('data_abertura_cnpj');
                    if (!dataAbertura || !dataAbertura.value) {
                        mostrarModalFeedback('erro', "Informe a data de abertura do CNPJ.");
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
                    mostrarModalFeedback('erro', "O telefone informado é inválido ou está vazio.");
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

                const insta = document.getElementById('instagram') ? document.getElementById('instagram').value.trim() : '';
                if (insta !== '' && typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarLinkSocial(insta, 'instagram')) {
                    mostrarModalFeedback('erro', "Informe um link válido do Instagram.");
                    document.getElementById('instagram').focus();
                    return false;
                }

                const face = document.getElementById('facebook') ? document.getElementById('facebook').value.trim() : '';
                if (face !== '' && typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarLinkSocial(face, 'facebook')) {
                    mostrarModalFeedback('erro', "Informe um link válido do Facebook.");
                    document.getElementById('facebook').focus();
                    return false;
                }

                const pix = document.getElementById('chave_pix') ? document.getElementById('chave_pix').value.trim() : '';
                if (pix !== '' && typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarChavePix(pix)) {
                    mostrarModalFeedback('erro', "Informe uma Chave PIX válida.");
                    document.getElementById('chave_pix').focus();
                    return false;
                }
            }

            if (etapaAtual === 5) {
                const docInput = document.getElementById('comprovante_documento');

                if (docInput.files.length === 0) {
                    if (modoEdicao && jaPossuiComprovante) {
                        return true;
                    }
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

                if (typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarTamanhoArquivo(docInput, 5)) {
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

<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>