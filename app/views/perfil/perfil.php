<?php
require_once __DIR__ . '/../templates/header.php';

$tipoPerfil = $_SESSION['tipo_perfil'] ?? 'adotante';
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'NomeUsuário';

$tituloCabecalho = 'Perfil';
$badgeTexto = ucfirst($tipoPerfil);
$botoes = [];

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
} else {
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<div class="max-w-md mx-auto bg-background min-h-screen pb-20">
    <div class="px-6 -mt-4 pt-10 flex flex-col items-center">
        
        <div class="bg-roxinhoFofo/80 text-white font-shantell font-bold text-xl px-12 py-1 rounded-full relative mb-6 shadow-sm">
            <span class="absolute -left-3 -top-2 text-2xl">🐾</span>
            <?= htmlspecialchars($badgeTexto) ?>
            <span class="absolute -right-3 -top-2 text-2xl">🐾</span>
        </div>

        <div class="relative mb-4">
            <div class="w-32 h-32 rounded-full border-[6px] border-roxinhoFofo/60 overflow-hidden bg-surface flex items-center justify-center shadow-md">
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
                <button type="button" onclick="document.getElementById('input-foto-direta').click()" class="absolute bottom-1 right-0 bg-surface p-2 rounded-full shadow border border-rosa-2 text-text-muted hover:bg-rosa-1 transition hover:scale-105 cursor-pointer">
                    ✏️
                </button>
                <input type="file" id="input-foto-direta" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="processarSelecaoFoto(event)">
            <?php endif; ?>
        </div>

        <h2 class="font-shantell text-2xl font-bold text-text-dark mb-6">
            <?= htmlspecialchars($nomeUsuario) ?>
        </h2>

        <div class="w-full bg-surface/80 rounded-3xl p-5 shadow-inner relative border border-rosa-2">
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="text-xl">⚙️</span>
                <h3 class="font-bold text-lg text-text-dark"><?= htmlspecialchars($tituloCabecalho) ?></h3>
            </div>

            <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide hide-scroll gap-4" id="slider-botoes">
                <?php foreach ($paginasBotoes as $index => $pagina): ?>
                    <div class="min-w-full snap-center grid grid-cols-3 gap-3 auto-rows-max">
                        <?php foreach ($pagina as $botao): ?>
                            <a href="<?= URL_BASE . $botao['url'] ?>" class="flex flex-col items-center justify-center bg-surface rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-24 border border-rosa-2 hover:border-rosa-3">
                                <img src="<?= URL_BASE ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= htmlspecialchars($botao['label']) ?>" class="h-8 w-8 mb-2">
                                <span class="text-[10px] font-bold leading-tight text-text-dark"><?= htmlspecialchars($botao['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($paginasBotoes) > 1): ?>
                <div class="flex justify-center gap-2 mt-4">
                    <?php foreach ($paginasBotoes as $index => $pagina): ?>
                        <div class="w-2 h-2 rounded-full <?= $index === 0 ? 'bg-primary' : 'bg-text-muted' ?> indicador-pagina" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="btn-prev" class="absolute inset-y-0 left-0 flex items-center px-2 cursor-pointer text-text-muted hover:text-text-dark font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-90">&lsaquo;</button>
                <button type="button" id="btn-next" class="absolute inset-y-0 right-0 flex items-center px-2 cursor-pointer text-text-muted hover:text-text-dark font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-90">&rsaquo;</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Cropper -->
<div id="modal-cropper-direto" class="fixed inset-0 bg-preto/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl">
        <h3 class="font-shantell text-xl font-bold mb-1 text-text-dark">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Enquadre sua foto de perfil perfeitamente.</p>
        
        <div class="w-full h-64 bg-surface rounded-2xl overflow-hidden mb-4 flex items-center justify-center">
            <img id="imagem-cropper-direto" src="" alt="Cortar" class="max-block max-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharCropperDireto()" class="flex-1 bg-cinzaMarrom/30 text-text-dark py-2.5 rounded-xl font-bold text-sm hover:bg-cinzaMarrom/50 transition">Cancelar</button>
            <button type="button" onclick="salvarFotoDireta()" id="btn-salvar-foto-direta" class="flex-1 bg-rosaAlerta text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">Aplicar</button>
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
    // Carrossel e Cropper (mesmo JavaScript, apenas mantido)
    const slider = document.getElementById('slider-botoes');
    // ... (código original)
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>