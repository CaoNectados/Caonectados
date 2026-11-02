<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$protetor = $protetor ?? [];
$redes = $redes ?? [];
$disponiveis = $disponiveis ?? [];
$adotados = $adotados ?? [];

$montarUrlFoto = function (?string $caminho) use ($urlBase): string {
    if (empty($caminho)) {
        return '';
    }
    return strpos($caminho, 'http') === 0 ? $caminho : $urlBase . '/' . ltrim($caminho, '/');
};

$fotoPerfilUrl = $montarUrlFoto($protetor['foto_perfil'] ?? null) ?: $urlBase . '/assets/img/perfil-placeholder.png';
$fotoFundoUrl = $montarUrlFoto($protetor['foto_fundo'] ?? null);

$linksRede = [];
foreach ($redes as $rede) {
    $linksRede[$rede['tipo_rede']] = $rede['link_rede'];
}
$iconesImagemRede = [
    'instagram' => asset('assets/img/icone-instagram-pagina.png'),
    'facebook' => asset('assets/img/icone-facebook-pagina.png'),
];
$iconesRede = [
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
?>

<div class="w-full pb-20">
    <!-- Cabeçalho e Informações do Protetor -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <!-- Capa da Página -->
        <div class="relative h-44 sm:h-60 rounded-2xl overflow-hidden <?= $fotoFundoUrl ? '' : 'bg-gradient-to-r from-roxoApagado to-rosa-2 dark:from-preto2 dark:to-preto3' ?>">
            <?php if ($fotoFundoUrl): ?>
                <img src="<?= htmlspecialchars($fotoFundoUrl) ?>" alt="Capa da ONG" class="w-full h-full object-cover">
            <?php endif; ?>

            <a href="<?= $urlBase ?>/pagina-perfil" onclick="if(window.history.length > 1){ history.back(); return false; }"
                class="absolute top-4 left-4 w-10 h-10 rounded-full bg-white/90 dark:bg-preto1/90 flex items-center justify-center text-text-dark dark:text-white shadow hover:scale-105 transition z-20" aria-label="Voltar">
                <img src="<?= htmlspecialchars(asset('assets/icons/geral/seta-voltar.svg')) ?>" alt="" class="h-6 w-7 object-contain dark:brightness-0 dark:invert" aria-hidden="true">
            </a>
        </div>

        <!-- Bloco de perfil + sobreposição da foto -->
        <div class="flex flex-col items-center -mt-16 relative z-10 px-4">
            <img src="<?= htmlspecialchars($fotoPerfilUrl) ?>" alt="Foto de <?= htmlspecialchars($protetor['nome_fantasia'] ?? '') ?>"
                class="w-32 h-32 rounded-full border-4 border-branco dark:border-preto1 object-cover bg-surface dark:bg-preto2 shadow-lg"
                onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">

            <h1 class="font-shantell text-2xl sm:text-3xl font-bold text-text-dark dark:text-white mt-3 text-center">
                <?= htmlspecialchars($protetor['nome_fantasia'] ?? 'ONG/Protetor') ?>
            </h1>

            <!-- Redes sociais / contato -->
            <div class="flex items-center justify-center flex-wrap gap-4 mt-3 text-xl">
                <?php foreach (['instagram', 'facebook', 'whatsapp', 'outro'] as $tipo): ?>
                    <?php if (!empty($linksRede[$tipo]) && in_array(strtolower((string)parse_url($linksRede[$tipo], PHP_URL_SCHEME)), ['http', 'https'], true)): ?>
                        <a href="<?= htmlspecialchars($linksRede[$tipo]) ?>" target="_blank" rel="noopener" title="<?= ucfirst($tipo) ?>" class="hover:scale-110 transition inline-flex items-center justify-center">
                            <?php if (isset($iconesImagemRede[$tipo])): ?>
                                <img src="<?= htmlspecialchars($iconesImagemRede[$tipo]) ?>" alt="" class="h-5 w-5 object-contain dark:brightness-0 dark:invert" aria-hidden="true">
                            <?php else: ?>
                                <?= $iconesRede[$tipo] ?>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($protetor['nome_regiao'])): ?>
                    <?php $enderecoExibido = $protetor['nome_regiao'] . ' · Foz do Iguaçu/PR'; ?>
                    <button type="button" onclick="abrirModalLocalizacao()" aria-label="Ver localização: <?= htmlspecialchars($enderecoExibido, ENT_QUOTES, 'UTF-8') ?>" title="Ver localização" class="inline-flex h-9 w-9 items-center justify-center rounded-full transition duration-200 hover:scale-110 hover:bg-roxo2/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary dark:hover:bg-white/10">
                        <img src="<?= htmlspecialchars(asset('assets/img/icone-localizacao-pagina.png')) ?>" alt="" class="h-5 w-5 object-contain dark:brightness-0 dark:invert" aria-hidden="true">
                    </button>
                <?php endif; ?>
                <?php if (!empty($protetor['chave_pix'])): ?>
                    <button type="button" onclick="copiarChavePix(this)" data-chave="<?= htmlspecialchars($protetor['chave_pix']) ?>" title="Copiar chave PIX" aria-label="Copiar chave PIX" class="hover:scale-110 transition cursor-pointer inline-flex items-center justify-center"><img src="<?= htmlspecialchars(asset('assets/img/icone-pix-pagina.png')) ?>" alt="" class="h-5 w-5 object-contain dark:brightness-0 dark:invert" aria-hidden="true"></button>
                <?php endif; ?>
            </div>

            <!-- Descrição -->
            <?php if (!empty($protetor['pagina_descricao'])): ?>
                <p class="text-sm text-text-muted text-center max-w-xl mt-4 leading-relaxed"><?= nl2br(htmlspecialchars($protetor['pagina_descricao'])) ?></p>
            <?php else: ?>
                <p class="text-sm text-text-muted italic text-center mt-4">Esta página ainda não tem uma descrição.</p>
            <?php endif; ?>

            <!-- Estatísticas -->
            <div class="flex items-center gap-12 mt-6">
                <div class="text-center">
                    <p class="font-bold text-xl text-text-dark dark:text-white"><?= count($disponiveis) ?></p>
                    <p class="text-xs text-text-muted">Animais em Adoção</p>
                </div>
                <div class="text-center">
                    <p class="font-bold text-xl text-text-dark dark:text-white"><?= count($adotados) ?></p>
                    <p class="text-xs text-text-muted">Animais Adotados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Abas -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 mt-8">
        <div class="bg-roxo2 dark:bg-primary rounded-2xl overflow-hidden flex shadow-sm">
            <button type="button" onclick="mostrarAbaCatalogo('disponiveis')" id="aba-btn-disponiveis" class="flex-1 py-3 text-white font-bold text-sm sm:text-base border-b-4 border-white transition flex items-center justify-center gap-2">
                <div class="flex items-center justify-center gap-3">
                    <img src="<?= htmlspecialchars(asset('assets/img/icone-menu-disponiveis.png')) ?>" alt="" class="h-6 w-6 object-contain" aria-hidden="true">
                </div>
                Disponíveis
            </button>
            <button type="button" onclick="mostrarAbaCatalogo('adotados')" id="aba-btn-adotados" class="flex-1 py-3 text-white/70 font-bold text-sm sm:text-base border-b-4 border-transparent transition flex items-center justify-center gap-2">
                <img src="<?= $urlBase ?>/assets/img/icone-adotados.png" alt="" class="h-6 w-6 object-contain" aria-hidden="true"> Adotados
            </button>
        </div>
    </div>

    <!-- Catálogo de Animais com Scale no Card Inteiro -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 mt-6">
        <!-- Aba Disponíveis -->
        <div id="aba-disponiveis" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
            <?php if (empty($disponiveis)): ?>
                <p class="col-span-full text-center text-sm text-text-muted italic py-16">Nenhum animal disponível no momento.</p>
            <?php else: ?>
                <?php foreach ($disponiveis as $animal): ?>
                    <?php
                    $foto = $montarUrlFoto($animal->getFotoPrincipal()) ?: $urlBase . '/assets/img/perfil-placeholder.png';
                    ?>
                    <a href="<?= $urlBase ?>/animal/mostrar?id=<?= $animal->getAnimalId() ?>"
                        class="relative aspect-square block rounded-xl overflow-hidden bg-black/5 dark:bg-preto2 border border-rosa-2/20 dark:border-preto3 flex items-center justify-center p-1 transition-transform duration-300 hover:scale-105 hover:z-10 shadow-sm hover:shadow-md">
                        <img src="<?= htmlspecialchars($foto) ?>"
                            alt="<?= htmlspecialchars($animal->getNome()) ?>"
                            class="w-full h-full object-contain"
                            onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                        <img src="<?= $urlBase ?>/assets/img/icone-disponivel-patinha.png" alt="Disponível para adoção" title="Disponível para adoção" class="absolute bottom-2 right-2 z-10 h-9 w-9 object-contain drop-shadow-md">
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Aba Adotados -->
        <div id="aba-adotados" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
            <?php if (empty($adotados)): ?>
                <p class="col-span-full text-center text-sm text-text-muted italic py-16">Nenhum animal adotado ainda.</p>
            <?php else: ?>
                <?php foreach ($adotados as $itemAdotado): ?>
                    <?php
                    $animal = $itemAdotado['animal'];
                    $adotante = $itemAdotado['adotante'] ?? [];
                    $foto = $montarUrlFoto($animal->getFotoPrincipal()) ?: $urlBase . '/assets/img/perfil-placeholder.png';
                    $fotoAdotante = $montarUrlFoto($adotante['foto_perfil'] ?? null) ?: $urlBase . '/assets/img/perfil-placeholder.png';
                    $nomeAdotante = trim((string) ($adotante['nome'] ?? ''));
                    $primeiroNomeAdotante = $nomeAdotante !== '' ? explode(' ', $nomeAdotante)[0] : 'uma família';
                    ?>
                    <a href="<?= $urlBase ?>/animal/mostrar?id=<?= $animal->getAnimalId() ?>"
                        class="relative aspect-square block rounded-xl overflow-hidden bg-black/5 dark:bg-preto2 border border-rosa-2/20 dark:border-preto3 flex items-center justify-center p-1 transition-transform duration-300 hover:scale-105 hover:z-10 shadow-sm hover:shadow-md">
                        <img src="<?= htmlspecialchars($foto) ?>"
                            alt="<?= htmlspecialchars($animal->getNome()) ?>"
                            class="w-full h-full object-contain grayscale-[30%]"
                            onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                        <span class="absolute bottom-2 left-1/2 flex max-w-[calc(100%_-_1rem)] -translate-x-1/2 items-center gap-1.5 rounded-full bg-rosaAlerta/95 py-1 pl-1.5 pr-1 text-[10px] font-bold text-white shadow-lg sm:text-xs z-10">
                            <img src="<?= $urlBase ?>/assets/img/icone-adotado-patinha-coracao.png" alt="" class="h-6 w-6 shrink-0 object-contain" aria-hidden="true">
                            <span class="truncate">Adotado por <?= htmlspecialchars($primeiroNomeAdotante, ENT_QUOTES, 'UTF-8') ?></span>
                            <img src="<?= htmlspecialchars($fotoAdotante, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($primeiroNomeAdotante, ENT_QUOTES, 'UTF-8') ?>" class="h-7 w-7 shrink-0 rounded-full border border-white object-cover" onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($protetor['nome_regiao'])): ?>
    <?php $urlMapaLocalizacao = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($enderecoExibido); ?>
    <div id="modal-localizacao" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-localizacao" onclick="if(event.target === this) fecharModalLocalizacao()">
        <div class="w-full max-w-sm rounded-2xl border border-rosa-2 bg-surface p-5 text-text-dark shadow-2xl dark:border-preto3 dark:bg-preto1 dark:text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="titulo-modal-localizacao" class="font-shantell text-xl font-bold">Localização</h2>
                    <p id="texto-endereco-localizacao" class="mt-2 text-sm text-text-muted dark:text-gray-200"><?= htmlspecialchars($enderecoExibido) ?></p>
                </div>
                <button type="button" onclick="fecharModalLocalizacao()" class="rounded-full p-2 text-text-muted hover:bg-primary/10 dark:text-white" aria-label="Fechar">&times;</button>
            </div>
            <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                <button type="button" onclick="copiarEnderecoLocalizacao()" class="btn-primario inline-flex flex-1 items-center justify-center gap-2">Copiar endereço</button>
                <a href="<?= htmlspecialchars($urlMapaLocalizacao, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn-secundario inline-flex flex-1 items-center justify-center gap-2 dark:text-white">Abrir no Maps</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function mostrarAbaCatalogo(aba) {
        const disponiveis = aba === 'disponiveis';
        document.getElementById('aba-disponiveis').classList.toggle('hidden', !disponiveis);
        document.getElementById('aba-adotados').classList.toggle('hidden', disponiveis);

        document.getElementById('aba-btn-disponiveis').classList.toggle('text-white', disponiveis);
        document.getElementById('aba-btn-disponiveis').classList.toggle('text-white/70', !disponiveis);
        document.getElementById('aba-btn-disponiveis').classList.toggle('border-white', disponiveis);
        document.getElementById('aba-btn-disponiveis').classList.toggle('border-transparent', !disponiveis);

        document.getElementById('aba-btn-adotados').classList.toggle('text-white', !disponiveis);
        document.getElementById('aba-btn-adotados').classList.toggle('text-white/70', disponiveis);
        document.getElementById('aba-btn-adotados').classList.toggle('border-white', !disponiveis);
        document.getElementById('aba-btn-adotados').classList.toggle('border-transparent', disponiveis);
    }

    function copiarChavePix(botao) {
        const chave = botao.dataset.chave;
        navigator.clipboard?.writeText(chave).then(function() {
            mostrarModalFeedback('sucesso', 'Chave PIX copiada!');
        }).catch(function() {
            mostrarModalFeedback('informativo', 'Chave PIX: ' + chave);
        });
    }

    function abrirModalLocalizacao() {
        const modal = document.getElementById('modal-localizacao');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalLocalizacao() {
        const modal = document.getElementById('modal-localizacao');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function copiarEnderecoLocalizacao() {
        const endereco = document.getElementById('texto-endereco-localizacao')?.textContent?.trim();
        if (!endereco) return;
        navigator.clipboard?.writeText(endereco).then(function() {
            fecharModalLocalizacao();
            mostrarModalFeedback('sucesso', 'Endereço copiado!');
        }).catch(function() {
            mostrarModalFeedback('informativo', 'Endereço: ' + endereco);
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') fecharModalLocalizacao();
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
