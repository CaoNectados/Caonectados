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
        <div class="bg-white rounded-xl p-6 text-center max-w-sm w-full shadow-xl transform transition-all">
            <h2 id="titulo-modal-feedback" class="text-xl font-bold mb-3 font-shantell"></h2>
            <p id="texto-modal-feedback" class="text-text-dark mb-6 text-sm sm:text-base leading-relaxed font-poppins"></p>
            <button id="btn-modal-feedback" onclick="fecharModalFeedback()" class="w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins">
                Entendido
            </button>
        </div>
    </div>

    <!-- 1. Scripts globais da aplicação -->
    <script src="<?= e(URL_BASE) ?>/assets/js/menu.js" defer></script>
    <script src="<?= e(URL_BASE) ?>/assets/js/validacoes.js"></script>

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

            const classesCores = {
                erro:        { texto: 'text-erro', bg: 'bg-erro' },
                aviso:       { texto: 'text-aviso', bg: 'bg-aviso' },
                sucesso:     { texto: 'text-sucesso', bg: 'bg-sucesso' },
                informativo: { texto: 'text-informativo', bg: 'bg-informativo' }
            };

            const tipoFeedback = tipo || 'informativo';
            const cores = classesCores[tipoFeedback] || classesCores.informativo;

            const elTitulo = document.getElementById('titulo-modal-feedback');
            const elTexto = document.getElementById('texto-modal-feedback');
            const elBtn = document.getElementById('btn-modal-feedback');
            const modal = document.getElementById('modal-feedback');

            elTitulo.className = 'text-xl font-bold mb-3 font-shantell ' + cores.texto;
            elBtn.className = 'w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 hover:opacity-90 font-poppins ' + cores.bg;

            elTitulo.innerText = titulos[tipoFeedback] || titulos.informativo;
            elTexto.innerText = mensagem;

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