<?php
require_once __DIR__ . '/../templates/header.php';

$tipoPerfil = $_SESSION['tipo_perfil'] ?? 'adotante';
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'NomeUsuário';

// Configurações padrão
$tituloCabecalho = 'Perfil';
$badgeTexto = ucfirst($tipoPerfil);
$botoes = [];

// ==========================================
// DEFINIÇÃO DOS BOTÕES POR TIPO DE PERFIL
// ==========================================
if ($tipoPerfil === 'administrador') {
    $tituloCabecalho = 'Configurações';
    $badgeTexto = 'Admin';
    $botoes = [
        ['label' => 'Editar Perfil',   'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil', 'icone' => 'alternar.svg',      'url' => '/perfil/alternar'],
        ['label' => 'Termos de Uso',   'icone' => 'termos.svg',        'url' => '/termos'],
        ['label' => 'Relatórios',      'icone' => 'relatorios.svg',    'url' => '/admin/relatorios'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
    ];
} elseif ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') {
    $nomePagina = $tipoPerfil === 'ong' ? 'Página Ong' : 'Página Protetor';
    $botoes = [
        ['label' => 'Editar Perfil',   'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil', 'icone' => 'alternar.svg',      'url' => '/perfil/alternar'],
        ['label' => 'Página ' . ucfirst($tipoPerfil), 'icone' => 'pagina.svg', 'url' => '/pagina-perfil'],
        ['label' => 'Relatórios',      'icone' => 'relatorios.svg',    'url' => '/relatorios'],
        ['label' => 'Gerenciar Animais','icone' => 'patinha.svg',      'url' => '/animais/cadastrar'],
        ['label' => 'Solicitações',    'icone' => 'solicitacoes.svg',  'url' => '/solicitacoes'],
        ['label' => 'Quero adotar!',   'icone' => 'quero-adotar.svg',  'url' => '/feed'],
        ['label' => 'Excluir Conta',   'icone' => 'excluir.svg',       'url' => '/perfil/excluir'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',       'icone' => 'denunciar.svg',     'url' => '/denuncias/nova'],
    ];
} else { // ADOTANTE (Adotante)
    $botoes = [
        ['label' => 'Editar Perfil',   'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil', 'icone' => 'alternar.svg',      'url' => '/perfil/alternar'],
        ['label' => 'Petiscos diários','icone' => 'petiscos.svg',      'url' => '/petiscos'],
        ['label' => 'Torne-se uma ONG/Protetor', 'icone' => 'torne-se.svg', 'url' => '/onboarding'],
        ['label' => 'Excluir Conta',   'icone' => 'excluir.svg',       'url' => '/perfil/excluir'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',       'icone' => 'denunciar.svg',     'url' => '/denuncias/nova'],
    ];
}

$paginasBotoes = array_chunk($botoes, 6);
?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<div class="max-w-md mx-auto bg-background dark:bg-corFundo-escuro min-h-screen pb-20">
    <div class="px-6 -mt-4 pt-10 flex flex-col items-center">
        
        <div class="bg-roxinhoFofo/80 text-white font-shantell font-bold text-xl px-12 py-1 rounded-full relative mb-6 shadow-sm">
            <span class="absolute -left-3 -top-2 text-2xl">🐾</span>
            <?= htmlspecialchars($badgeTexto) ?>
            <span class="absolute -right-3 -top-2 text-2xl">🐾</span>
        </div>

     <!-- FOTO DE PERFIL COM ÍCONE DE EDITAR (ABRE CROPPER DIRETO) -->
        <div class="relative mb-4">
            <div class="w-32 h-32 rounded-full border-[6px] border-roxinhoFofo/60 overflow-hidden bg-white flex items-center justify-center shadow-md">
                <?php 
                    $caminhoDB = $fotoPerfil ?? '';
                    
                    if ($tipoPerfil === 'administrador') {
                        $srcFoto = rtrim(URL_BASE, '/') . '/assets/img/logo.png';
                    } else {
                        $srcFoto = !empty($caminhoDB) 
                            ? rtrim(URL_BASE, '/') . '/' . ltrim($caminhoDB, '/') 
                            : rtrim(URL_BASE, '/') . '/assets/img/perfil-placeholder.png';
                    }
                ?>
                <img src="<?= $srcFoto ?>" id="foto-perfil-display" alt="Foto de perfil" class="w-full h-full rounded-full <?= $tipoPerfil === 'administrador' ? 'object-contain' : 'object-cover' ?>">
            </div>
            
            <?php if ($tipoPerfil !== 'administrador'): ?>
                <button type="button" onclick="document.getElementById('input-foto-direta').click()" class="absolute bottom-1 right-0 bg-white p-2 rounded-full shadow border border-gray-200 text-gray-600 hover:bg-gray-50 cursor-pointer transition hover:scale-105">
                    ✏️
                </button>
                <input type="file" id="input-foto-direta" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="processarSelecaoFoto(event)">
            <?php endif; ?>
        </div>

        <h2 class="font-shantell text-2xl font-bold text-text-dark mb-6">
            <?= htmlspecialchars($nomeUsuario) ?>
        </h2>

        <div class="w-full bg-gray-300/80 dark:bg-gray-800 rounded-3xl p-5 shadow-inner relative">
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="text-xl">⚙️</span>
                <h3 class="font-bold text-lg text-text-dark"><?= htmlspecialchars($tituloCabecalho) ?></h3>
            </div>

            <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide hide-scroll gap-4" id="slider-botoes">
                <?php foreach ($paginasBotoes as $index => $pagina): ?>
                    <div class="min-w-full snap-center grid grid-cols-3 gap-3 auto-rows-max">
                        <?php foreach ($pagina as $botao): ?>
                            <a href="<?= URL_BASE . $botao['url'] ?>" class="flex flex-col items-center justify-center bg-white dark:bg-gray-700 rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-24">
                                <img src="<?= URL_BASE ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= htmlspecialchars($botao['label']) ?>" class="h-8 w-8 mb-2">
                                <span class="text-[10px] font-bold leading-tight text-gray-800 dark:text-white"><?= htmlspecialchars($botao['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($paginasBotoes) > 1): ?>
                <div class="flex justify-center gap-2 mt-4">
                    <?php foreach ($paginasBotoes as $index => $pagina): ?>
                        <div class="w-2 h-2 rounded-full <?= $index === 0 ? 'bg-gray-600' : 'bg-gray-400' ?> indicador-pagina" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="btn-prev" class="absolute inset-y-0 left-0 flex items-center px-2 cursor-pointer text-gray-400 hover:text-gray-700 font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-90">&lsaquo;</button>
                <button type="button" id="btn-next" class="absolute inset-y-0 right-0 flex items-center px-2 cursor-pointer text-gray-400 hover:text-gray-700 font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-90">&rsaquo;</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Cropper Integrada -->
<div id="modal-cropper-direto" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 class="font-shantell text-xl font-bold mb-1 text-gray-800">Ajustar Foto</h3>
        <p class="text-xs text-gray-500 mb-4 text-center">Enquadre sua foto de perfil perfeitamente.</p>
        
        <div class="w-full h-64 bg-gray-100 rounded-2xl overflow-hidden mb-4 flex items-center justify-center">
            <img id="imagem-cropper-direto" src="" alt="Cortar" class="max-block max-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharCropperDireto()" class="flex-1 bg-gray-200 text-gray-700 py-2.5 rounded-xl font-bold text-sm">Cancelar</button>
            <button type="button" onclick="salvarFotoDireta()" id="btn-salvar-foto-direta" class="flex-1 bg-primary text-white py-2.5 rounded-xl font-bold text-sm">Aplicar</button>
        </div>
    </div>
</div>

<style>
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .cropper-view-box, .cropper-face { border-radius: 50%; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // Carrossel
    const slider = document.getElementById('slider-botoes');
    const indicadores = document.querySelectorAll('.indicador-pagina');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    if (slider) {
        slider.addEventListener('scroll', () => {
            let index = Math.round(slider.scrollLeft / slider.clientWidth);
            indicadores.forEach((ind, i) => {
                ind.classList.replace(i === index ? 'bg-gray-400' : 'bg-gray-600', i === index ? 'bg-gray-600' : 'bg-gray-400');
            });
        });

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                let curr = Math.round(slider.scrollLeft / slider.clientWidth);
                slider.scrollTo({ left: curr >= indicadores.length - 1 ? 0 : slider.scrollLeft + slider.clientWidth, behavior: 'smooth' });
            });
        }
        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                let curr = Math.round(slider.scrollLeft / slider.clientWidth);
                slider.scrollTo({ left: curr === 0 ? (indicadores.length - 1) * slider.clientWidth : slider.scrollLeft - slider.clientWidth, behavior: 'smooth' });
            });
        }
    }

    // Cropper
    let cropperInstancia = null;

    function processarSelecaoFoto(event) {
        const files = event.target.files;
        if (files && files.length > 0) {
            if (files[0].size > 5 * 1024 * 1024) {
                mostrarModalFeedback('erro', 'A imagem excede o tamanho máximo de 5MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgEl = document.getElementById('imagem-cropper-direto');
                imgEl.src = e.target.result;
                document.getElementById('modal-cropper-direto').classList.remove('hidden');

                if (cropperInstancia) cropperInstancia.destroy();
                cropperInstancia = new Cropper(imgEl, {
                    aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 0.8
                });
            };
            reader.readAsDataURL(files[0]);
        }
    }

    function fecharCropperDireto() {
        document.getElementById('modal-cropper-direto').classList.add('hidden');
        if (cropperInstancia) { cropperInstancia.destroy(); cropperInstancia = null; }
        document.getElementById('input-foto-direta').value = '';
    }

    async function salvarFotoDireta() {
        if (!cropperInstancia) return;
        const btn = document.getElementById('btn-salvar-foto-direta');
        btn.disabled = true; btn.innerText = 'Enviando...';

        const base64Image = cropperInstancia.getCroppedCanvas({ width: 400, height: 400 }).toDataURL('image/png');
        const formData = new FormData(); formData.append('foto_cortada', base64Image);

        try {
            const response = await fetch('<?= URL_BASE ?>/perfil/atualizar-foto', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'sucesso') {
                document.getElementById('foto-perfil-display').src = base64Image;
                mostrarModalFeedback('sucesso', result.mensagem);
                fecharCropperDireto();
            } else {
                mostrarModalFeedback('erro', result.mensagem);
            }
        } catch (err) {
            mostrarModalFeedback('erro', 'Erro de conexão.');
        } finally {
            btn.disabled = false; btn.innerText = 'Aplicar';
        }
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

