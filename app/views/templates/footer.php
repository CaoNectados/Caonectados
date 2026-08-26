<?php
/**
 * Template: rodapé global + scripts da aplicação.
 */
?>
    </main>
    <?php require_once __DIR__ . '/modal_boas_vindas.php'; ?>
    <footer class="relative border-t border-white/10 bg-primary text-white shadow-[0_-6px_18px_rgba(0,0,0,0.18)]">
        <img src="<?= e(URL_BASE) ?? '' ?>/assets/img/cachorrorodape.png"
             alt=""
             aria-hidden="true"
             class="pointer-events-none absolute left-0 top-1/2 h-14 w-auto -translate-y-1/2 object-contain sm:h-16">

        <img src="<?= e(URL_BASE) ?? '' ?>/assets/img/gatorodape.png"
             alt=""
             aria-hidden="true"
             class="pointer-events-none absolute right-0 top-1/2 h-14 w-auto -translate-y-1/2 object-contain sm:h-16">

        <div class="mx-auto flex h-16 max-w-figma items-center justify-center px-16 sm:px-20">
            <p class="truncate text-center font-poppins text-sm font-medium text-white/90 sm:text-base">
                &copy; Copyright <?= date('Y') ?> CãoNectados
            </p>
        </div>
    </footer>
    
    </div> 

    <!-- Modal Unificado de Feedback -->
    <div id="modal-feedback" class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center p-4">
        <div class="bg-surface rounded-xl p-6 text-center max-w-sm w-full shadow-xl transform transition-all">
            <h2 id="titulo-modal-feedback" class="text-xl font-bold mb-3 font-shantell"></h2>
            <p id="texto-modal-feedback" class="text-text-dark mb-6 text-sm sm:text-base leading-relaxed font-poppins"></p>
            <button id="btn-modal-feedback" onclick="fecharModalFeedback()" class="w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins">
                Entendido
            </button>
        </div>
    </div>

    <!--
        Modal Unificado de Confirmação (substitui window.confirm() nativo em toda a aplicação).
        IDs prefixados com "-generico" de propósito: admin/gerenciar_usuarios.php já tem seu
        próprio modal #modal-confirmacao (com troca de ícone e ação assíncrona pendente) — os
        dois não podem competir pelo mesmo id na mesma página. A função pública continua se
        chamando abrirModalConfirmacao(), que é o nome que regiao/index.php já esperava existir.
    -->
    <div id="modal-confirmacao-generico" class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center p-4">
        <div class="bg-surface rounded-xl p-6 text-center max-w-sm w-full shadow-xl transform transition-all">
            <h2 id="titulo-modal-confirmacao-generico" class="text-xl font-bold mb-3 font-shantell text-text-dark"></h2>
            <p id="texto-modal-confirmacao-generico" class="text-text-dark mb-6 text-sm sm:text-base leading-relaxed font-poppins"></p>
            <div class="flex gap-3">
                <button id="btn-modal-confirmacao-generico-cancelar" type="button"
                        class="flex-1 font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins bg-cinzaMarrom/30 text-text-dark">
                    Cancelar
                </button>
                <button id="btn-modal-confirmacao-generico-confirmar" type="button"
                        class="flex-1 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins bg-erro">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- 1. Scripts globais da aplicação -->
    <script src="<?= e(asset('assets/js/menu.js')) ?>" defer></script>
    <script src="<?= e(asset('assets/js/validacoes.js')) ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <!-- 2. Lógica do Modal de Feedback -->
    <script>
        function fecharModalFeedback() {
            const modal = document.getElementById('modal-feedback');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        function mostrarModalFeedback(tipo, mensagem) {
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

            elTitulo.className = 'text-xl font-bold mb-3 font-shantell';
            elTitulo.style.color = cor;

            elBtn.className = 'w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins';
            elBtn.style.backgroundColor = cor;

            elTitulo.innerText = titulos[tipoFeedback] || titulos.informativo;
            elTexto.innerText = mensagem;

            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    </script>

    <!-- 3. Lógica do Modal de Confirmação genérico (substitui window.confirm()) -->
    <script>
        function fecharModalConfirmacaoGenerico() {
            const modal = document.getElementById('modal-confirmacao-generico');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        /**
         * Substituto de window.confirm() no padrão visual da aplicação. Uso:
         *   abrirModalConfirmacao('Título', 'Mensagem', function () { ...ação confirmada... });
         * Opcionalmente customiza os textos dos botões (padrão: "Confirmar"/"Cancelar").
         */
        function abrirModalConfirmacao(titulo, mensagem, aoConfirmar, textoConfirmar, textoCancelar) {
            const modal = document.getElementById('modal-confirmacao-generico');
            document.getElementById('titulo-modal-confirmacao-generico').innerText = titulo || 'Confirmar ação';
            document.getElementById('texto-modal-confirmacao-generico').innerText = mensagem || 'Tem certeza que deseja continuar?';

            const btnCancelar = document.getElementById('btn-modal-confirmacao-generico-cancelar');
            const btnConfirmarAntigo = document.getElementById('btn-modal-confirmacao-generico-confirmar');

            btnCancelar.textContent = textoCancelar || 'Cancelar';
            btnConfirmarAntigo.textContent = textoConfirmar || 'Confirmar';
            btnCancelar.onclick = fecharModalConfirmacaoGenerico;

            // Troca o botão de confirmar por um clone antes de religar o listener — cada
            // chamada de abrirModalConfirmacao() tem sua própria ação, e sem isso os cliques
            // de aberturas anteriores continuariam empilhados no mesmo botão.
            const btnConfirmar = btnConfirmarAntigo.cloneNode(true);
            btnConfirmarAntigo.parentNode.replaceChild(btnConfirmar, btnConfirmarAntigo);
            btnConfirmar.addEventListener('click', function () {
                fecharModalConfirmacaoGenerico();
                if (typeof aoConfirmar === 'function') {
                    aoConfirmar();
                }
            });

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