<?php
require_once __DIR__ . '/../templates/header.php';

$tipoPerfil = $_SESSION['tipo_conta'] ?? 'tutor';
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
        // Página 2 (O PHP vai quebrar automaticamente)
        ['label' => 'Quero adotar!',   'icone' => 'quero-adotar.svg',  'url' => '/feed'],
        ['label' => 'Excluir Conta',   'icone' => 'excluir.svg',       'url' => '/perfil/excluir'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',       'icone' => 'denunciar.svg',     'url' => '/denuncias/nova'],
    ];
} else { // ADOTANTE (Tutor)
    $botoes = [
        ['label' => 'Editar Perfil',   'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil', 'icone' => 'alternar.svg',      'url' => '/perfil/alternar'],
        ['label' => 'Petiscos diários','icone' => 'petiscos.svg',      'url' => '/petiscos'],
        ['label' => 'Torne-se uma ONG / Protetor', 'icone' => 'torne-se.svg', 'url' => '/onboarding'],
        ['label' => 'Excluir Conta',   'icone' => 'excluir.svg',       'url' => '/perfil/excluir'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
        // Página 2
        ['label' => 'Denunciar',       'icone' => 'denunciar.svg',     'url' => '/denuncias/nova'],
    ];
}

// O segredo da paginação: Divide o array de botões de 6 em 6
$paginasBotoes = array_chunk($botoes, 6);
?>

<div class="max-w-md mx-auto bg-background dark:bg-corFundo-escuro min-h-screen pb-20">
    

    <!-- CONTEÚDO DO PERFIL -->
    <div class="px-6 -mt-4 pt-10 flex flex-col items-center">
        
        <!-- BADGE (Admin, Ong, etc) -->
        <div class="bg-roxinhoFofo/80 text-white font-shantell font-bold text-xl px-12 py-1 rounded-full relative mb-6 shadow-sm">
            <span class="absolute -left-3 -top-2 text-2xl">🐾</span>
            <?= $badgeTexto ?>
            <span class="absolute -right-3 -top-2 text-2xl">🐾</span>
        </div>

       <!-- FOTO DE PERFIL COM ÍCONE DE EDITAR -->
        <div class="relative mb-4">
            <div class="w-32 h-32 rounded-full border-[6px] border-roxinhoFofo/60 overflow-hidden bg-white flex items-center justify-center shadow-md">
                <?php 
                    $srcFoto = !empty($fotoPerfil) ? URL_BASE . '/' . $fotoPerfil : URL_BASE . '/assets/img/perfil-placeholder.png';
                ?>
                <img src="<?= $srcFoto ?>" alt="Foto de perfil" class="w-full h-full object-cover">
            </div>
            <a href="<?= URL_BASE ?>/perfil/editar-foto" class="absolute bottom-1 right-0 bg-white p-2 rounded-full shadow border border-gray-200 text-gray-600 hover:bg-gray-50">
                ✏️
            </a>
        </div>

        <!-- NOME DO USUÁRIO -->
        <h2 class="font-shantell text-2xl font-bold text-text-dark mb-6">
            <?= htmlspecialchars($nomeUsuario) ?>
        </h2>

        <!-- CAIXA CINZA DE CONFIGURAÇÕES (COM CARROSSEL) -->
        <div class="w-full bg-gray-300/80 dark:bg-gray-800 rounded-3xl p-5 shadow-inner relative">
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="text-xl">⚙️</span>
                <h3 class="font-bold text-lg text-text-dark">Configurações</h3>
            </div>

            <!-- CONTAINER DO SLIDER -->
            <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide hide-scroll gap-4" id="slider-botoes">
                
                <?php foreach ($paginasBotoes as $index => $pagina): ?>
                    <!-- CADA PÁGINA OCUPA 100% DA LARGURA -->
                    <div class="min-w-full snap-center grid grid-cols-3 gap-3 auto-rows-max">
                        
                        <?php foreach ($pagina as $botao): ?>
                            <a href="<?= URL_BASE . $botao['url'] ?>" class="flex flex-col items-center justify-center bg-white dark:bg-gray-700 rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-24">
                                <!-- Substitua pelo caminho correto dos seus ícones -->
                                <img src="<?= URL_BASE ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= $botao['label'] ?>" class="h-8 w-8 mb-2">
                                <span class="text-[10px] font-bold leading-tight text-gray-800 dark:text-white"><?= $botao['label'] ?></span>
                            </a>
                        <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>

            </div>

            <!-- INDICADORES (BOLINHAS) SÓ APARECEM SE TIVER MAIS DE 1 PÁGINA -->
            <?php if (count($paginasBotoes) > 1): ?>
                <div class="flex justify-center gap-2 mt-4">
                    <?php foreach ($paginasBotoes as $index => $pagina): ?>
                        <div class="w-2 h-2 rounded-full <?= $index === 0 ? 'bg-gray-600' : 'bg-gray-400' ?> indicador-pagina" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
                
                <!-- SETINHAS DE NAVEGAÇÃO LATERAIS  -->
                <button type="button" id="btn-prev" class="absolute inset-y-0 left-0 flex items-center px-2 cursor-pointer text-gray-400 hover:text-gray-700 font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-90">
                    &lsaquo;
                </button>
                <button type="button" id="btn-next" class="absolute inset-y-0 right-0 flex items-center px-2 cursor-pointer text-gray-400 hover:text-gray-700 font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-90">
                    &rsaquo;
                </button>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    /* Esconde a barra de rolagem mas mantém a funcionalidade do swipe no celular */
    .hide-scroll::-webkit-scrollbar {
        display: none;
    }
    .hide-scroll {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>

<script>
    const slider = document.getElementById('slider-botoes');
    const indicadores = document.querySelectorAll('.indicador-pagina');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    if (slider) {
        // Atualiza as bolinhas conforme a pessoa arrasta ou clica
        slider.addEventListener('scroll', () => {
            let index = Math.round(slider.scrollLeft / slider.clientWidth);
            
            indicadores.forEach((ind, i) => {
                if(i === index) {
                    ind.classList.replace('bg-gray-400', 'bg-gray-600');
                } else {
                    ind.classList.replace('bg-gray-600', 'bg-gray-400');
                }
            });
        });

        // Ação de clicar na seta para a direita (COM LOOP)
        if (btnNext) {
            btnNext.addEventListener('click', () => {
                let currentIndex = Math.round(slider.scrollLeft / slider.clientWidth);
                let totalPages = indicadores.length;
                
                // Se estiver na última página, volta para a posição zero (início)
                if (currentIndex >= totalPages - 1) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: slider.clientWidth, behavior: 'smooth' });
                }
            });
        }

        // Ação de clicar na seta para a esquerda (COM LOOP)
        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                let currentIndex = Math.round(slider.scrollLeft / slider.clientWidth);
                let totalPages = indicadores.length;
                
                // Se estiver na primeira página, vai para o final
                if (currentIndex === 0) {
                    slider.scrollTo({ left: (totalPages - 1) * slider.clientWidth, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: -slider.clientWidth, behavior: 'smooth' });
                }
            });
        }
        
        // Torna as bolinhas clicáveis
        indicadores.forEach((ind, index) => {
            ind.addEventListener('click', () => {
                slider.scrollTo({ left: index * slider.clientWidth, behavior: 'smooth' });
            });
            ind.style.cursor = 'pointer';
        });
    }
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>