<?php // Confirmação compartilhada pelas ações que chamam abrirModalConfirmacao(). ?>
<dialog id="modal-confirmar-acao" aria-labelledby="titulo-confirmar-acao" aria-describedby="texto-confirmar-acao"
        class="m-auto w-[calc(100%_-_2rem)] max-w-md max-h-[calc(100dvh_-_2rem)] overflow-y-auto rounded-3xl border border-rosa-2 bg-surface p-6 font-poppins text-text-dark shadow-xl backdrop:bg-preto/80 sm:p-8 dark:border-preto3">
    <h2 id="titulo-confirmar-acao" class="font-shantell text-2xl font-bold text-primary dark:text-rosa-2"></h2>
    <p id="texto-confirmar-acao" class="mt-4 text-sm leading-relaxed text-text-dark"></p>
    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button type="button" id="cancelar-confirmar-acao" autofocus
                class="btn-secundario min-h-11 w-full">Voltar</button>
        <button type="button" id="executar-confirmar-acao"
                class="btn-primario min-h-11 w-full">Confirmar</button>
    </div>
</dialog>
<script>
(function () {
    'use strict';
    const modal = document.getElementById('modal-confirmar-acao');
    const confirmar = document.getElementById('executar-confirmar-acao');
    const cancelar = document.getElementById('cancelar-confirmar-acao');
    let acaoPendente = null;

    window.abrirModalConfirmacao = function (titulo, mensagem, acao, textoConfirmar = 'Confirmar', textoCancelar = 'Voltar') {
        if (modal.open || typeof acao !== 'function') return;
        document.getElementById('titulo-confirmar-acao').textContent = titulo;
        document.getElementById('texto-confirmar-acao').textContent = mensagem;
        confirmar.textContent = textoConfirmar;
        cancelar.textContent = textoCancelar;
        confirmar.disabled = false;
        acaoPendente = acao;
        modal.showModal();
    };

    cancelar.addEventListener('click', () => { acaoPendente = null; modal.close(); });
    modal.addEventListener('cancel', () => { acaoPendente = null; });
    confirmar.addEventListener('click', () => {
        if (confirmar.disabled || !acaoPendente) return;
        confirmar.disabled = true;
        const acao = acaoPendente;
        acaoPendente = null;
        modal.close();
        acao();
    });
})();
</script>
