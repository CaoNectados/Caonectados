<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$pagina =$pagina ?? [];
$redes =$redes ?? [];
$protetorId = (int) ($_SESSION['protetor_id'] ?? 0);

$montarUrlFoto = function (?string $caminho) use ($urlBase): string {
    if (empty($caminho)) {
        return '';
    }
    return strpos($caminho, 'http') === 0 ?$caminho : $urlBase . '/' . ltrim($caminho, '/');
};

$fotoPerfilUrl = $montarUrlFoto($pagina['foto_perfil'] ?? null);
$fotoFundoUrl = $montarUrlFoto($pagina['foto_fundo'] ?? null);

$labelsRede = ['instagram' => 'Instagram', 'facebook' => 'Facebook', 'whatsapp' => 'WhatsApp', 'outro' => 'Outro'];$iconesRede = [
    'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                    </svg>',
    'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                    </svg>',
    'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>',
    'outro' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                    <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                    <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                </svg>'
];
$tiposJaUsados = array_column($redes, 'tipo_rede');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<style>
    #modal-cropper-pagina.cropper-redondo .cropper-view-box,
    #modal-cropper-pagina.cropper-redondo .cropper-face {
        border-radius: 50%;
    }
</style>

<div class="max-w-4xl mx-auto pb-16 px-4 sm:px-6">
    <div class="flex items-center justify-between pt-6 mb-6">
        <div>
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minha Página</h1>
            <p class="text-xs text-text-muted">Como sua ONG/Protetor aparece para os adotantes</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <?php if ($protetorId > 0): ?>
                <a href="<?= $urlBase ?>/pagina?id=<?= $protetorId ?>" target="_blank" class="btn-primario text-xs !py-2 !px-4 font-bold shadow-sm">
                    Ver página pública
                </a>
            <?php endif; ?>
            <a href="<?= $urlBase ?>/perfil" class="inline-flex items-center justify-center gap-1.5 rounded-full bg-cinzaMarrom/20 dark:bg-preto3 text-text-dark dark:text-white px-4 py-2 text-xs font-bold transition hover:bg-cinzaMarrom/35 dark:hover:bg-preto2 active:scale-95 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                </svg> Voltar
            </a>
        </div>
    </div>

    <form action="<?= $urlBase ?>/pagina-perfil/atualizar" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="foto_perfil_cortada" id="foto_perfil_cortada">
        <input type="hidden" name="foto_fundo_cortada" id="foto_fundo_cortada">

        <!-- Preview estilo "capa + avatar sobreposto" -->
        <div class="card-padrao p-0 overflow-hidden">
            <div class="relative h-44 sm:h-56 bg-gradient-to-r from-roxoApagado to-rosa-2 dark:from-preto2 dark:to-preto3 cursor-pointer group"
                onclick="document.getElementById('input-arquivo-fundo').click()">
                <img id="preview-foto-fundo" src="<?= htmlspecialchars($fotoFundoUrl) ?>" alt="Capa" class="w-full h-full object-cover <?= empty($fotoFundoUrl) ? 'hidden' : '' ?>">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                    <span class="opacity-0 group-hover:opacity-100 text-white text-xs sm:text-sm font-bold transition"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                        </svg> Trocar capa</span>
                </div>
            </div>
            <input type="file" id="input-arquivo-fundo" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="iniciarCropperPagina(event, 'fundo')">

            <div class="flex flex-col items-center -mt-14 pb-6 px-6 relative z-10">
                <div class="w-28 h-28 rounded-full border-4 border-branco dark:border-preto1 bg-surface dark:bg-preto2 overflow-hidden shadow-md cursor-pointer relative group"
                    onclick="document.getElementById('input-arquivo-perfil').click()">
                    <img id="preview-foto-perfil" src="<?= htmlspecialchars($fotoPerfilUrl ?: $urlBase . '/assets/img/perfil-placeholder.png') ?>" alt="Foto de perfil" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                        <span class="opacity-0 group-hover:opacity-100 text-white text-lg transition"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                            </svg></span>
                    </div>
                </div>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="iniciarCropperPagina(event, 'perfil')">
                <p class="text-xs text-text-muted mt-2">Toque nas imagens para trocar</p>
            </div>
        </div>

        <!-- Descrição e PIX -->
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

    <!-- Redes Sociais e Contato -->
    <div class="card-padrao mt-6 space-y-4">
        <h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white">Redes Sociais e Contato</h2>

        <?php if (empty($redes)): ?>
            <p class="text-sm text-text-muted italic">Nenhuma rede social cadastrada ainda.</p>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($redes as$rede): ?>
                    <li class="flex items-center justify-between gap-3 bg-branco dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-xl px-4 py-2.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <span><?= $iconesRede[$rede['tipo_rede']] ?? '🔗' ?></span>
                            <span class="text-xs font-bold text-text-dark dark:text-white shrink-0"><?= htmlspecialchars($labelsRede[$rede['tipo_rede']] ?? ucfirst($rede['tipo_rede'])) ?>:</span>
                            <a href="<?= htmlspecialchars($rede['link_rede']) ?>" target="_blank" rel="noopener" class="text-xs text-primary dark:text-roxinhoFofo underline truncate"><?= htmlspecialchars($rede['link_rede']) ?></a>
                        </div>
                        <form id="form-remover-rede-<?= (int) $rede['rede_id'] ?>" action="<?= $urlBase ?>/pagina-perfil/rede/remover" method="POST" class="shrink-0">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="rede_id" value="<?= (int) $rede['rede_id'] ?>">
                            <button type="button"
                                onclick="abrirModalRemoverRede(<?= (int) $rede['rede_id'] ?>)"
                                class="px-3 py-1.5 rounded-lg bg-red-900 hover:bg-red-800 text-gray-200 text-xs font-bold transition shadow-sm">
                                Remover
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="<?= $urlBase ?>/pagina-perfil/rede/adicionar" method="POST" class="flex flex-wrap sm:flex-nowrap gap-2 pt-2 border-t border-cinzaMarrom/15">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <select name="tipo_rede" class="input-padrao w-full sm:w-auto min-w-[130px]" required>
                <?php foreach ($labelsRede as $valor =>$rotulo): ?>
                    <option value="<?= $valor ?>"><?= $rotulo ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="link_rede" placeholder="Link ou contato..." required class="input-padrao flex-1 min-w-[180px]">
            <button type="submit" class="btn-secundario w-full sm:w-auto shrink-0">+ Adicionar</button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- MODAL CROPPER -->
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

<!-- MODAL CONFIRMAR REMOÇÃO DE REDE -->
<div id="modal-remover-rede" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface dark:bg-preto1 rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl border border-red-900">
        <!-- Ícone de Alerta -->
        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4 text-red-600 dark:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
            </svg>
        </div>
        
        <h3 class="font-shantell text-xl font-bold mb-2 text-text-dark dark:text-white text-center">Remover Rede Social</h3>
        <p class="text-xs text-text-muted mb-6 text-center">Tem certeza que deseja remover esta rede social? Esta ação não poderá ser desfeita.</p>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalRemoverRede()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
            <button type="button" onclick="executarRemocaoRede()" class="flex-1 bg-red-900 hover:bg-red-800 text-gray-200 py-2.5 rounded-xl font-bold text-sm transition">Remover</button>
        </div>
    </div>
</div>

<script>
    let cropperPagina = null;
    let alvoCropperPagina = null;
    let redeIdParaRemover = null;

    // --- FUNÇÕES DA MODAL DE REMOÇÃO DE REDE ---
    function abrirModalRemoverRede(redeId) {
        redeIdParaRemover = redeId;
        document.getElementById('modal-remover-rede').classList.remove('hidden');
    }

    function fecharModalRemoverRede() {
        redeIdParaRemover = null;
        document.getElementById('modal-remover-rede').classList.add('hidden');
    }

    function executarRemocaoRede() {
        if (redeIdParaRemover) {
            const form = document.getElementById('form-remover-rede-' + redeIdParaRemover);
            if (form) {
                form.submit();
            }
        }
        fecharModalRemoverRede();
    }

    // --- FUNÇÕES DA MODAL DO CROPPER ---
    function iniciarCropperPagina(event, alvo) {
        alvoCropperPagina = alvo;
        const fileInput = event.target;
        if (!fileInput.files || !fileInput.files.length) return;

        const limiteMB = (alvo === 'perfil') ? 2 : 3;
        if (typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarTamanhoArquivo(fileInput, limiteMB)) {
            mostrarModalFeedback('erro', `A imagem é muito grande. Escolha uma de até ${limiteMB}MB.`);
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
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
        if (cropperPagina) {
            cropperPagina.destroy();
            cropperPagina = null;
        }
        document.getElementById('input-arquivo-perfil').value = '';
        document.getElementById('input-arquivo-fundo').value = '';
    }

    function salvarRecortePagina() {
        if (!cropperPagina) return;
        const isPerfil = (alvoCropperPagina === 'perfil');
        const options = isPerfil ? {
            width: 400,
            height: 400
        } : {
            width: 1200,
            height: 675
        };
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