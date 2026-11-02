<?php
/**
 * Template: rodapé global + scripts da aplicação.
 */
$caminhoRodape = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
$noFeedRodape = $caminhoRodape === rtrim(parse_url(URL_BASE, PHP_URL_PATH) ?: '', '/') . '/feed';
?>
    </main>
    <?php require_once __DIR__ . '/modal_boas_vindas.php'; ?>
    <footer class="relative shrink-0 border-t border-white/10 bg-primary text-white shadow-[0_-6px_18px_rgba(0,0,0,0.18)]">
        <img src="<?= e(URL_BASE) ?? '' ?>/assets/img/cachorrorodape.png"
             alt=""
             aria-hidden="true"
             class="pointer-events-none absolute left-0 top-1/2 h-12 w-auto -translate-y-1/2 object-contain sm:h-14 lg:h-16">

        <img src="<?= e(URL_BASE) ?? '' ?>/assets/img/gatorodape.png"
             alt=""
             aria-hidden="true"
             class="pointer-events-none absolute right-0 top-1/2 h-12 w-auto -translate-y-1/2 object-contain sm:h-14 lg:h-16">

        <div class="mx-auto flex h-16 max-w-figma items-center justify-center px-4 sm:px-6 lg:px-20">
            <p class="truncate text-center font-poppins text-sm font-medium text-white/90 sm:text-base">
                &copy; Copyright <?= date('Y') ?> CãoNectados
            </p>
        </div>
        <?php if (!$noFeedRodape): ?>
            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-[calc(100%-0.5rem)] h-8 bg-gradient-to-b from-primary/85 via-primary/55 to-transparent blur-md lg:hidden"></div>
        <?php endif; ?>
    </footer>
    
    </div> 

    <!-- Navegação mobile compartilhada, fora da área que rola. -->
    <?php
    $perfilMenuInferior = $_SESSION['tipo_perfil'] ?? null;
    $inicioMenuInferior = match ($perfilMenuInferior) {
        'administrador' => '/admin/dashboard',
        'adotante' => '/feed',
        default => '/',
    };
    $perfilDestinoInferior = $perfilMenuInferior === null ? '/login' : '/perfil';
    $pesquisaAtivaInferior = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/') === rtrim(parse_url(URL_BASE, PHP_URL_PATH) ?: '', '/') . '/pesquisar';
    $animaisAtivosInferior = in_array(rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'), [rtrim(parse_url(URL_BASE, PHP_URL_PATH) ?: '', '/') . '/animal', rtrim(parse_url(URL_BASE, PHP_URL_PATH) ?: '', '/') . '/gerenciar-animais'], true);
    if ($animaisAtivosInferior && in_array($perfilMenuInferior, ['ong','protetor'], true)) $inicioMenuInferior = '/animal';
    ?>
    <nav id="menu-inferior-mobile" aria-label="Navegação inferior"
         class="fixed bottom-2 left-3 right-3 rounded-3xl z-30 bg-primary flex items-center justify-around shadow-[0_-4px_12px_rgba(0,0,0,0.2)] lg:hidden"
         style="padding-bottom: env(safe-area-inset-bottom)">
        <a href="<?= e(URL_BASE . $inicioMenuInferior) ?>" aria-label="Início"
           class="flex h-16 flex-1 items-center justify-center text-white">
            <img src="<?= e(URL_BASE) ?>/assets/icons/navbar/home.svg" alt="" class="w-6 h-6 brightness-0 invert">
        </a>
        <?php if ($perfilMenuInferior === 'adotante'): ?>
            <a href="<?= e(URL_BASE) ?>/pesquisar" id="busca-menu-inferior" aria-label="Pesquisar animais"
               <?= $pesquisaAtivaInferior ? 'aria-current="page"' : '' ?> class="flex h-16 flex-1 items-center justify-center <?= $pesquisaAtivaInferior ? 'text-accent' : 'text-white' ?>">
                <?= renderIconeMenu('pesquisar.svg', '', 'w-6 h-6') ?>
            </a>
        <?php else: ?>
            <button type="button" id="botao-menu-inferior" aria-label="Abrir menu de navegação"
                    aria-controls="menu-mobile" aria-expanded="false"
                    class="flex h-16 flex-1 items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        <?php endif; ?>
        <a href="<?= e(URL_BASE . $perfilDestinoInferior) ?>" aria-label="<?= $perfilMenuInferior === null ? 'Entrar' : 'Meu perfil' ?>"
           class="flex h-16 flex-1 items-center justify-center text-white">
            <img src="<?= e(URL_BASE) ?>/assets/icons/navbar/perfil.svg" alt="" class="w-6 h-6 brightness-0 invert">
        </a>
    </nav>

    <!-- Modal Unificado de Feedback -->
    <div id="modal-feedback" class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center p-4">
        <div class="relative max-w-sm w-full transform rounded-xl border border-rosa-3 bg-rosa-1 p-6 text-center text-text-dark shadow-2xl transition-all dark:border-rosa-3 dark:bg-[#5d4968] dark:text-white">
            <h2 id="titulo-modal-feedback" class="text-xl font-bold mb-3 font-shantell"></h2>
            <p id="texto-modal-feedback" class="mb-6 text-sm sm:text-base leading-relaxed font-poppins text-text-dark dark:text-white"></p>
            <a id="link-modal-feedback" href="#" class="hidden mb-3 w-full items-center justify-center rounded-lg bg-primary px-4 py-2.5 font-poppins font-semibold text-white transition hover:brightness-110"></a>
            <button id="btn-modal-feedback" onclick="fecharModalFeedback()" class="w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins">
                Entendido
            </button>
            <div id="animacao-petiscos-feedback" class="pointer-events-none absolute inset-0 overflow-visible" aria-hidden="true"></div>
        </div>
    </div>

    <style>
        @keyframes petisco-cair-do-modal {
            0% { transform: translateY(0) rotate(0deg) scale(.85); opacity: 1; }
            75% { opacity: 1; }
            100% { transform: translateY(58vh) rotate(300deg) scale(.65); opacity: 0; }
        }
        .petisco-caindo-do-modal {
            position: absolute;
            top: 100%;
            width: 30px;
            height: 30px;
            object-fit: contain;
            animation: petisco-cair-do-modal 1.8s ease-in var(--atraso-petisco, 0s) forwards;
        }
        @media (prefers-reduced-motion: reduce) {
            .petisco-caindo-do-modal { animation-duration: .01ms; }
        }
    </style>

    <!-- Confirmações de ações com conteúdo sólido, compatível com os dois temas. -->
    <div id="modal-confirmacao" class="hidden fixed inset-0 z-[60] items-center justify-center bg-black/60 p-4" role="presentation" onclick="if (event.target === this) fecharModalConfirmacao()">
        <section class="w-full max-w-md rounded-2xl border border-rosa-3 bg-rosa-1 p-6 text-text-dark shadow-2xl dark:border-rosa-3 dark:bg-[#5d4968] dark:text-white" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-confirmacao" aria-describedby="texto-modal-confirmacao">
            <h2 id="titulo-modal-confirmacao" class="mb-3 text-center font-shantell text-xl font-bold"></h2>
            <p id="texto-modal-confirmacao" class="mb-6 text-center font-poppins text-sm leading-relaxed"></p>
            <div class="flex flex-col-reverse gap-3 sm:flex-row">
                <button id="btn-cancelar-confirmacao" type="button" class="btn-secundario min-h-11 flex-1 dark:!border-white/80 dark:!text-white dark:hover:!bg-white/10 dark:hover:!text-white" onclick="fecharModalConfirmacao()"></button>
                <button id="btn-confirmar-confirmacao" type="button" class="btn-primario min-h-11 flex-1"></button>
            </div>
        </section>
    </div>

    <!-- 1. Scripts globais da aplicação -->
    <script src="<?= e(asset('assets/js/menu.js')) ?>" defer></script>
    <script src="<?= e(asset('assets/js/validacoes.js')) ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <!-- 2. Lógica do Modal de Feedback -->
    <script>
        let acaoConfirmacaoPendente = null;

        function fecharModalConfirmacao() {
            const modal = document.getElementById('modal-confirmacao');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            acaoConfirmacaoPendente = null;
        }

        function abrirModalConfirmacao(titulo, mensagem, acao, rotuloConfirmar = 'Confirmar', rotuloCancelar = 'Cancelar') {
            const modal = document.getElementById('modal-confirmacao');
            if (!modal) {
                if (window.confirm(mensagem) && typeof acao === 'function') acao();
                return;
            }

            document.getElementById('titulo-modal-confirmacao').textContent = titulo;
            document.getElementById('texto-modal-confirmacao').textContent = mensagem;
            document.getElementById('btn-confirmar-confirmacao').textContent = rotuloConfirmar;
            document.getElementById('btn-cancelar-confirmacao').textContent = rotuloCancelar;
            acaoConfirmacaoPendente = typeof acao === 'function' ? acao : null;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('btn-confirmar-confirmacao').focus();
        }

        document.getElementById('btn-confirmar-confirmacao').addEventListener('click', function () {
            const acao = acaoConfirmacaoPendente;
            fecharModalConfirmacao();
            if (acao) acao();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.getElementById('modal-confirmacao').classList.contains('hidden')) {
                fecharModalConfirmacao();
            }
        });

        function fecharModalFeedback() {
            const modal = document.getElementById('modal-feedback');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.getElementById('animacao-petiscos-feedback').replaceChildren();
        }

        function mostrarModalFeedback(tipo, mensagem, opcoes = {}) {
            const titulos = {
                erro:        'Ops! Algo deu errado',
                aviso:       'Atenção!',
                sucesso:     'Sucesso!',
                informativo: 'Informação'
            };

            // Nomes das variáveis CSS (definidas em tailwind_config.php, :root e .dark).
            // Aplicadas via style.setProperty em vez de classes bg-erro/bg-aviso/etc: o
            // Tailwind (CDN) só gera CSS para nomes de classe que consegue "ver" no HTML,
            // e classes só existentes como pedaço de string dentro do JS (cores.bg + tipo)
            // podem não ser detectadas a tempo — resultando no botão sem cor de fundo.
            const varCores = {
                erro:        '--color-erro',
                aviso:       '--color-aviso',
                sucesso:     '--color-sucesso',
                informativo: '--color-informativo'
            };

            const tipoFeedback = tipo || 'informativo';
            const nomeVar = varCores[tipoFeedback] || varCores.informativo;
            // --color-X guarda um trio "R G B" (formato exigido pelo Tailwind para
            // suportar opacidade em bg-x/50 etc.), por isso precisa do wrapper rgb().
            const cor = 'rgb(' + getComputedStyle(document.documentElement).getPropertyValue(nomeVar).trim() + ')';

            const elTitulo = document.getElementById('titulo-modal-feedback');
            const elTexto = document.getElementById('texto-modal-feedback');
            const elBtn = document.getElementById('btn-modal-feedback');
            const modal = document.getElementById('modal-feedback');
            const linkAcao = document.getElementById('link-modal-feedback');
            const animacaoPetiscos = document.getElementById('animacao-petiscos-feedback');

            elTitulo.className = 'text-xl font-bold mb-3 font-shantell';
            elTitulo.style.color = cor;

            elBtn.className = 'w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins';
            elBtn.style.backgroundColor = cor;
            const canais = getComputedStyle(document.documentElement).getPropertyValue(nomeVar).trim().split(/\s+/).map(Number).map(valor => {
                const canal = valor / 255;
                return canal <= 0.04045 ? canal / 12.92 : Math.pow((canal + 0.055) / 1.055, 2.4);
            });
            const luminancia = 0.2126 * canais[0] + 0.7152 * canais[1] + 0.0722 * canais[2];
            elBtn.style.color = luminancia > 0.179 ? '#000' : '#fff';

            elTitulo.innerText = titulos[tipoFeedback] || titulos.informativo;
            elTexto.innerText = mensagem;

            linkAcao.classList.add('hidden');
            linkAcao.classList.remove('inline-flex');
            linkAcao.removeAttribute('href');
            if (opcoes.url && opcoes.textoBotao) {
                linkAcao.href = opcoes.url;
                linkAcao.textContent = opcoes.textoBotao;
                linkAcao.classList.remove('hidden');
                linkAcao.classList.add('inline-flex');
            }

            animacaoPetiscos.replaceChildren();
            if (opcoes.animarPetiscos) {
                const iconePetisco = <?= json_encode(asset('assets/img/icone-petisco-osso.png')) ?>;
                [12, 38, 64, 86].forEach((posicao, indice) => {
                    const petisco = document.createElement('img');
                    petisco.src = iconePetisco;
                    petisco.alt = '';
                    petisco.className = 'petisco-caindo-do-modal';
                    petisco.style.left = posicao + '%';
                    petisco.style.setProperty('--atraso-petisco', (indice * 0.18) + 's');
                    animacaoPetiscos.appendChild(petisco);
                });
            }

            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    </script>

    <!-- Dispara o modal se houver feedback vindo do PHP na sessão -->
    <?php if (isset($_SESSION['feedback'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const feedback = <?= json_encode($_SESSION['feedback']) ?>;
            mostrarModalFeedback(feedback.tipo, feedback.mensagem);
        });
    </script>
    <?php unset($_SESSION['feedback']); ?>
    <?php endif; ?>

</body>
</html>
