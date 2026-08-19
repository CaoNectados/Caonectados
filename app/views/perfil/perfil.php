<?php 
require_once __DIR__ . '/../templates/header.php';

$tipoPerfil = $_SESSION['perfil_ativo']['tipo'] ?? $_SESSION['tipo_perfil'] ?? 'adotante';
$nomeUsuario = $_SESSION['usuario']['nome'] ?? $_SESSION['usuario_nome'] ?? 'Nome de Usuário';

$fotoPerfilSessao = $_SESSION['foto_perfil'] ?? null;
if (empty($fotoPerfilSessao)) {
    if ($tipoPerfil === 'adotante' || $tipoPerfil === 'usuario') {
        $adotanteInfo = (new \app\repositories\AdotanteRepository())->buscarPorUsuarioId((int)$_SESSION['usuario_id']);
        $fotoPerfilSessao = $adotanteInfo['foto_perfil'] ?? null;
    } else {
        $protetorInfo = (new \app\repositories\ProtetorRepository())->buscarPorUsuarioId((int)$_SESSION['usuario_id']);
        if ($protetorInfo) {
            $paginaInfo = (new \app\repositories\PaginaRepository())->buscarPorProtetorId((int)$protetorInfo['protetor_id']);
            $fotoPerfilSessao = $paginaInfo['foto_perfil'] ?? null;
        }
    }
}

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

if ($tipoPerfil === 'administrador' || $tipoPerfil === 'admin') {
    $srcFoto = $urlBase . '/assets/img/logo.png';
} elseif (!empty($fotoPerfilSessao)) {
    $fotoLimpa = ltrim(trim($fotoPerfilSessao), '/');
    $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
    $srcFoto = $urlBase . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
} else {
    $srcFoto = $urlBase . '/assets/img/perfil-placeholder.png';
}

$tituloCabecalho = 'Perfil';
$badgeTexto = ucfirst($tipoPerfil);
$botoes = [];

if ($tipoPerfil === 'administrador' || $tipoPerfil === 'admin') {
    $tituloCabecalho = 'Configurações';
    $badgeTexto = 'Admin';
    $botoes = [
        ['label' => 'Editar Perfil',    'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil', 'icone' => 'alternar.svg',      'action' => 'abrirModalTrocaPerfil()'],
        ['label' => 'Termos de Uso',    'icone' => 'termos.svg',        'url' => '/termos'],
        ['label' => 'Relatórios',      'icone' => 'relatorios.svg',    'url' => '/admin/relatorios'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
    ];
} elseif (in_array($tipoPerfil, ['ong', 'protetor'], true)) {
    $botoes = [
        ['label' => 'Editar Perfil',    'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil',  'icone' => 'alternar.svg',      'action' => 'abrirModalTrocaPerfil()'],
        ['label' => 'Página ' . ucfirst($tipoPerfil), 'icone' => 'pagina.svg', 'url' => '/pagina-perfil'],
        ['label' => 'Relatórios',       'icone' => 'relatorios.svg',    'url' => '/relatorios'],
        ['label' => 'Gerenciar Animais','icone' => 'patinha.svg',       'url' => '/animal'],
        ['label' => 'Solicitações',     'icone' => 'solicitacoes.svg',  'url' => '/solicitacoes'],
        ['label' => 'Quero adotar!',    'icone' => 'quero-adotar.svg',  'url' => '/feed'],
        ['label' => 'Excluir Conta',    'icone' => 'excluir.svg',       'url' => '/perfil/excluir'],
        ['label' => 'Sair',             'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',        'icone' => 'denunciar.svg',     'url' => '/denuncias/nova'],
    ];
} else { 
    $botoes = [
        ['label' => 'Editar Perfil',          'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil',        'icone' => 'alternar.svg',      'action' => 'abrirModalTrocaPerfil()'],
        ['label' => 'Petiscos diários',       'icone' => 'petiscos.svg',      'url' => '/petiscos'],
        ['label' => 'Torne-se uma ONG/Protetor', 'icone' => 'torne-se.svg',   'url' => '/onboarding'],
        ['label' => 'Excluir Conta',          'icone' => 'excluir.svg',       'url' => '/perfil/excluir'],
        ['label' => 'Sair',                   'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',              'icone' => 'denunciar.svg',     'url' => '/denuncias/nova'],
    ];
}

$paginasBotoes = array_chunk($botoes, 6);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<div class="max-w-md mx-auto bg-background min-h-screen pb-20">
    <div class="px-6 -mt-4 pt-10 flex flex-col items-center">
        
        <!-- Badge de Perfil Atual -->
        <div class="bg-rosa-3 dark:bg-primary text-text-dark dark:text-white font-shantell font-bold text-xl px-12 py-1.5 rounded-full relative mb-6 shadow-sm border border-rosa-2">
            <span class="absolute -left-3 -top-1 text-xl">🐾</span>
            <?= htmlspecialchars($badgeTexto) ?>
            <span class="absolute -right-3 -top-1 text-xl">🐾</span>
        </div>

        <!-- Foto de Perfil -->
        <div class="relative mb-4">
            <div class="w-32 h-32 rounded-full border-[5px] border-rosa-3 dark:border-preto3 overflow-hidden bg-surface dark:bg-preto2 flex items-center justify-center shadow-md">
                <img src="<?= htmlspecialchars($srcFoto) ?>" 
                     id="foto-perfil-display" 
                     alt="Foto de perfil" 
                     class="w-full h-full rounded-full <?= ($tipoPerfil === 'administrador' || $tipoPerfil === 'admin') ? 'object-contain p-2 bg-white' : 'object-cover' ?>"
                     onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
            </div>
            
            <?php if (!in_array($tipoPerfil, ['administrador', 'admin'], true)): ?>
                <button type="button" 
                        onclick="document.getElementById('input-foto-direta').click()" 
                        class="absolute bottom-1 right-0 bg-surface dark:bg-preto1 p-2 rounded-full shadow border border-rosa-2 text-text-muted hover:bg-rosa-1 transition hover:scale-105 cursor-pointer"
                        title="Alterar foto">
                    ✏️
                </button>
                <input type="file" id="input-foto-direta" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="processarSelecaoFoto(event)">
            <?php endif; ?>
        </div>

        <!-- Nome do Usuário -->
        <h2 class="font-shantell text-2xl font-bold text-text-dark dark:text-white mb-6 text-center">
            <?= htmlspecialchars($nomeUsuario) ?>
        </h2>

        <!-- Container de Ações -->
        <div class="w-full bg-surface dark:bg-preto1 rounded-3xl p-5 shadow-inner relative border border-rosa-2 dark:border-preto3">
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="text-xl">⚙️</span>
                <h3 class="font-bold text-lg text-text-dark dark:text-white"><?= htmlspecialchars($tituloCabecalho) ?></h3>
            </div>

            <!-- Grid de Botões -->
            <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide hide-scroll gap-4" id="slider-botoes">
                <?php foreach ($paginasBotoes as $pagina): ?>
                    <div class="min-w-full snap-center grid grid-cols-3 gap-3 auto-rows-max">
                        <?php foreach ($pagina as $botao): ?>
                            <?php if (isset($botao['action'])): ?>
                                <button type="button" onclick="<?= htmlspecialchars($botao['action']) ?>" class="flex flex-col items-center justify-center bg-branco dark:bg-preto2 rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-24 cursor-pointer border border-rosa-2 dark:border-preto3 w-full">
                                    <img src="<?= $urlBase ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= htmlspecialchars($botao['label']) ?>" class="h-8 w-8 mb-2 object-contain">
                                    <span class="text-[10px] font-bold leading-tight text-text-dark dark:text-white"><?= htmlspecialchars($botao['label']) ?></span>
                                </button>
                            <?php else: ?>
                                <a href="<?= $urlBase . $botao['url'] ?>" class="flex flex-col items-center justify-center bg-branco dark:bg-preto2 rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-24 border border-rosa-2 dark:border-preto3">
                                    <img src="<?= $urlBase ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= htmlspecialchars($botao['label']) ?>" class="h-8 w-8 mb-2 object-contain">
                                    <span class="text-[10px] font-bold leading-tight text-text-dark dark:text-white"><?= htmlspecialchars($botao['label']) ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Indicadores do Carrossel -->
            <?php if (count($paginasBotoes) > 1): ?>
                <div class="flex justify-center gap-2 mt-4">
                    <?php foreach ($paginasBotoes as $index => $pagina): ?>
                        <div class="w-2.5 h-2.5 rounded-full <?= $index === 0 ? 'bg-primary dark:bg-roxinhoFofo' : 'bg-text-muted' ?> indicador-pagina transition-all duration-300" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="btn-prev" class="absolute inset-y-0 left-1 flex items-center px-2 cursor-pointer text-text-muted hover:text-text-dark dark:hover:text-white font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-95">&lsaquo;</button>
                <button type="button" id="btn-next" class="absolute inset-y-0 right-1 flex items-center px-2 cursor-pointer text-text-muted hover:text-text-dark dark:hover:text-white font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-95">&rsaquo;</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Trocar Perfil (Sem fotos listadas, apenas seleção de texto/papel) -->
<div id="modalTrocarPerfil" class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-surface dark:bg-preto1 rounded-3xl shadow-xl w-full max-w-sm p-6 transform transition-all scale-100 border border-rosa-3">
        <div class="flex justify-between items-center mb-4 border-b border-cinzaMarrom/20 pb-3">
            <h3 class="text-xl font-shantell font-bold text-text-dark dark:text-white">Trocar Perfil</h3>
            <button onclick="fecharModalTrocaPerfil()" class="text-text-muted hover:text-erro text-3xl font-bold transition">&times;</button>
        </div>
        
        <p class="text-sm font-poppins text-text-muted mb-5">Selecione o perfil desejado para navegar:</p>

        <div class="space-y-3">
            <?php 
            $perfis = $_SESSION['perfis'] ?? [];
            if (!empty($perfis)): 
                foreach ($perfis as $p): 
                    $isCurrent = (isset($_SESSION['perfil_ativo']['tipo']) && $_SESSION['perfil_ativo']['tipo'] === $p['tipo']);
            ?>
                <div class="flex items-center justify-between p-4 rounded-xl transition border <?= $isCurrent ? 'bg-rosa-1/40 border-primary dark:bg-preto2' : 'bg-branco dark:bg-preto2 border-cinzaMarrom/30' ?>">
                    <div>
                        <p class="font-bold text-text-dark dark:text-white capitalize"><?= htmlspecialchars($p['tipo'] === 'ong' ? 'ONG' : ($p['tipo'] === 'administrador' ? 'Administrador' : $p['tipo'])) ?></p>
                    </div>
                    <?php if ($isCurrent): ?>
                        <span class="text-xs bg-primary text-white font-bold px-3 py-1.5 rounded-full shadow-sm">Ativo</span>
                    <?php else: ?>
                        <form action="<?= $urlBase ?>/perfil/trocar" method="POST" class="m-0">
                            <input type="hidden" name="tipo" value="<?= htmlspecialchars($p['tipo']) ?>">
                            <button type="submit" class="bg-text-dark hover:opacity-90 dark:bg-primary text-white text-xs font-bold px-4 py-2 rounded-full transition shadow cursor-pointer">
                                Acessar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php 
                endforeach; 
            else: 
            ?>
                <div class="p-4 bg-erro/10 text-erro border border-erro/20 rounded-xl text-center text-sm font-medium">
                    Nenhum outro perfil disponível.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Cropper Direto -->
<div id="modal-cropper-direto" class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 hidden">
    <div class="bg-surface dark:bg-preto1 rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl border border-rosa-3">
        <h3 class="font-shantell text-xl font-bold mb-1 text-text-dark dark:text-white">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Enquadre sua foto de perfil perfeitamente.</p>
        
        <div class="w-full h-64 bg-branco dark:bg-preto2 rounded-2xl overflow-hidden mb-4 flex items-center justify-center border border-cinzaMarrom/30">
            <img id="imagem-cropper-direto" src="" alt="Recorte" class="max-w-full max-h-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharCropperDireto()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
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
    function abrirModalTrocaPerfil() {
        document.getElementById('modalTrocarPerfil').classList.remove('hidden');
    }
    function fecharModalTrocaPerfil() {
        document.getElementById('modalTrocarPerfil').classList.add('hidden');
    }

    const slider = document.getElementById('slider-botoes');
    const indicadores = document.querySelectorAll('.indicador-pagina');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    if (slider) {
        slider.addEventListener('scroll', () => {
            let index = Math.round(slider.scrollLeft / slider.clientWidth);
            indicadores.forEach((ind, i) => {
                if (i === index) {
                    ind.classList.remove('bg-text-muted');
                    ind.classList.add('bg-primary', 'dark:bg-roxinhoFofo', 'scale-110');
                } else {
                    ind.classList.remove('bg-primary', 'dark:bg-roxinhoFofo', 'scale-110');
                    ind.classList.add('bg-text-muted');
                }
            });
        });

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                let curr = Math.round(slider.scrollLeft / slider.clientWidth);
                slider.scrollTo({ 
                    left: curr >= indicadores.length - 1 ? 0 : (curr + 1) * slider.clientWidth, 
                    behavior: 'smooth' 
                });
            });
        }
        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                let curr = Math.round(slider.scrollLeft / slider.clientWidth);
                slider.scrollTo({ 
                    left: curr === 0 ? (indicadores.length - 1) * slider.clientWidth : (curr - 1) * slider.clientWidth, 
                    behavior: 'smooth' 
                });
            });
        }
    }

    let cropperInstancia = null;

    function processarSelecaoFoto(event) {
        const files = event.target.files;
        if (files && files.length > 0) {
            if (files[0].size > 5 * 1024 * 1024) {
                if (typeof mostrarModalFeedback === 'function') {
                    mostrarModalFeedback('erro', 'A imagem excede o tamanho máximo de 5MB.');
                } else {
                    alert('A imagem excede o tamanho máximo de 5MB.');
                }
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgEl = document.getElementById('imagem-cropper-direto');
                imgEl.src = e.target.result;
                document.getElementById('modal-cropper-direto').classList.remove('hidden');

                if (cropperInstancia) cropperInstancia.destroy();
                cropperInstancia = new Cropper(imgEl, {
                    aspectRatio: 1, 
                    viewMode: 1, 
                    dragMode: 'move', 
                    autoCropArea: 0.8
                });
            };
            reader.readAsDataURL(files[0]);
        }
    }

    function fecharCropperDireto() {
        document.getElementById('modal-cropper-direto').classList.add('hidden');
        if (cropperInstancia) { 
            cropperInstancia.destroy(); 
            cropperInstancia = null; 
        }
        document.getElementById('input-foto-direta').value = '';
    }

    async function salvarFotoDireta() {
        if (!cropperInstancia) return;
        const btn = document.getElementById('btn-salvar-foto-direta');
        btn.disabled = true; 
        btn.innerText = 'Enviando...';

        const base64Image = cropperInstancia.getCroppedCanvas({ width: 400, height: 400 }).toDataURL('image/png');
        const formData = new FormData(); 
        formData.append('foto_cortada', base64Image);

        try {
            const response = await fetch('<?= $urlBase ?>/perfil/atualizar-foto', { 
                method: 'POST', 
                body: formData 
            });
            const result = await response.json();

            if (result.status === 'sucesso' || result.sucesso === true) {
                document.getElementById('foto-perfil-display').src = base64Image;
                if (typeof mostrarModalFeedback === 'function') {
                    mostrarModalFeedback('sucesso', result.mensagem || 'Foto atualizada com sucesso!');
                }
                fecharCropperDireto();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                if (typeof mostrarModalFeedback === 'function') {
                    mostrarModalFeedback('erro', result.mensagem || 'Falha ao atualizar foto.');
                } else {
                    alert(result.mensagem || 'Falha ao atualizar foto.');
                }
            }
        } catch (err) {
            if (typeof mostrarModalFeedback === 'function') {
                mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
            } else {
                alert('Erro de conexão com o servidor.');
            }
        } finally {
            btn.disabled = false; 
            btn.innerText = 'Aplicar';
        }
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>