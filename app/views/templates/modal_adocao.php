<?php
// Confirmação de envio de petisco no feed.
$modalPetisco = $modalPetisco ?? false;
?>
<?php if ($modalPetisco): ?>
<dialog id="modal-petisco" class="adocao-modal box-border m-auto w-[calc(100%_-_1.5rem)] max-w-[390px] max-h-[calc(100dvh_-_2rem)] overflow-y-auto rounded-3xl border border-rosa-2 bg-surface px-5 pb-7 pt-12 text-center font-poppins text-text-dark shadow-xl backdrop:bg-preto/80 dark:border-preto3" aria-labelledby="petisco-titulo" aria-describedby="petisco-descricao" data-modal-adocao>
    <button type="button" class="adocao-fechar absolute right-2 top-2 inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent text-3xl leading-none text-primary transition hover:bg-rosa-1/40 dark:text-rosa-2" data-fechar-adocao aria-label="Fechar confirmação" autofocus>&times;</button>
    <h2 id="petisco-titulo" class="adocao-titulo mb-5 mt-3 font-shantell text-2xl font-bold text-primary sm:text-3xl dark:text-rosa-2">Petisco enviado!</h2>
    <img class="adocao-gif mx-auto mb-4 block h-auto max-h-[55dvh] w-full rounded-2xl bg-preto object-contain" data-gif-adocao src="<?= e(asset('assets/img/Caonectou.gif')) ?>" alt="Animação CãoNectou comemorando o envio do petisco" width="848" height="636">
    <p id="petisco-descricao" class="adocao-descricao mb-6 mt-4 text-sm leading-relaxed"></p>
    <a class="adocao-whatsapp btn-primario min-h-11 w-full whitespace-normal sm:w-auto" href="<?= e(URL_BASE.'/minhas-solicitacoes') ?>">Acompanhar solicitação</a>
</dialog>
<?php endif; ?>

<script>
(function () {
    'use strict';
    const modais = Array.from(document.querySelectorAll('[data-modal-adocao]'));
    const fechados = new Set();
    function foiFechado(modal) {
        if (fechados.has(modal.dataset.autoKey)) return true;
        try { return sessionStorage.getItem(modal.dataset.autoKey) === '1'; }
        catch (_) { return false; }
    }
    function abrir(modal) {
        if (!modal || modal.open) return;
        modal.showModal();
        const gif = modal.querySelector('[data-gif-adocao]');
        if (gif) gif.src = gif.getAttribute('src');
    }
    function proximo() {
        if (document.querySelector('dialog[open]')) return;
        abrir(modais.find(modal => modal.dataset.autoKey && !foiFechado(modal)));
    }
    modais.forEach(modal => {
        modal.querySelector('[data-fechar-adocao]').addEventListener('click', () => modal.close());
        modal.addEventListener('click', event => {
            const r = modal.getBoundingClientRect();
            if (event.target === modal && (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom)) modal.close();
        });
        modal.addEventListener('close', () => {
            if (!modal.dataset.autoKey) return;
            fechados.add(modal.dataset.autoKey);
            try { sessionStorage.setItem(modal.dataset.autoKey, '1'); } catch (_) {}
            proximo();
        });
        modal.querySelectorAll('[data-foto-adocao]').forEach(img => {
            img.addEventListener('error', () => { img.hidden = true; });
        });
    });
    document.querySelectorAll('[data-abrir-adocao]').forEach(botao => {
        botao.addEventListener('click', () => abrir(document.getElementById(botao.dataset.abrirAdocao)));
    });
    window.mostrarModalPetisco = function (mensagem) {
        const modal = document.getElementById('modal-petisco');
        if (!modal) return;
        modal.querySelector('#petisco-descricao').textContent = mensagem;
        abrir(modal);
    };
    document.addEventListener('DOMContentLoaded', proximo);
})();
</script>
