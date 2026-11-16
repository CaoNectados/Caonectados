document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select[data-regiao-selecionada]').forEach(select => {
        const hidden = document.getElementById(select.id.startsWith('feed-') ? 'feed-regiao-id-hidden' : 'regiao_id_hidden');
        const inicial = [...select.options].find(option => option.dataset.id && option.dataset.id === hidden?.value);
        select.value = inicial?.value || select.dataset.regiaoSelecionada || '';
        const sincronizar = () => {
            if (hidden) hidden.value = select.selectedOptions[0]?.dataset.id || '';
            select.setCustomValidity('');
        };
        select.addEventListener('change', sincronizar);
        sincronizar();
    });
    // Modais fixas não devem herdar deslocamentos ou limites do conteúdo rolável.
    document.querySelectorAll('[id]').forEach(modal => {
        if (!(modal.getAttribute('id') || '').toLowerCase().includes('modal') || !modal.classList.contains('fixed')) return;
        modal.classList.add('modal-centralizada', 'flex');
        document.body.appendChild(modal);
    });

    const modais = [...document.querySelectorAll('.modal-centralizada')];
    const seletorFoco = 'button:not([disabled]), a[href], input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const visivel = elemento => elemento.getClientRects().length > 0
        && getComputedStyle(elemento).visibility !== 'hidden';
    let modalAtiva = null;
    let focoAnterior = null;
    let ultimoAcionador = null;
    document.addEventListener('click', evento => {
        if (!modalAtiva) ultimoAcionador = evento.target.closest('button, a, input, select, textarea');
    }, true);
    const focaveis = modal => [...modal.querySelectorAll(seletorFoco)].filter(visivel);
    const atualizarModal = () => {
        const proxima = modais.filter(visivel).at(-1) ?? null;
        if (proxima === modalAtiva) return;
        if (!proxima) {
            modalAtiva = null;
            if (focoAnterior?.isConnected && visivel(focoAnterior)) focoAnterior.focus();
            focoAnterior = null;
            return;
        }
        if (!modalAtiva) focoAnterior = proxima.contains(document.activeElement) ? ultimoAcionador : document.activeElement;
        modalAtiva = proxima;
        const painel = proxima.querySelector('[role="dialog"]') ?? proxima;
        painel.setAttribute('role', 'dialog');
        painel.setAttribute('aria-modal', 'true');
        painel.tabIndex = -1;
        (focaveis(proxima)[0] ?? painel).focus();
    };
    const observador = new MutationObserver(atualizarModal);
    modais.forEach(modal => observador.observe(modal, { attributes: true, attributeFilter: ['class', 'style', 'hidden'] }));
    document.addEventListener('keydown', evento => {
        if (evento.defaultPrevented || evento.key !== 'Tab' || !modalAtiva) return;
        const itens = focaveis(modalAtiva);
        if (!itens.length) {
            evento.preventDefault();
            return;
        }
        const primeiro = itens[0];
        const ultimo = itens.at(-1);
        if (evento.shiftKey && (document.activeElement === primeiro || !modalAtiva.contains(document.activeElement))) {
            evento.preventDefault();
            ultimo.focus();
        } else if (!evento.shiftKey && (document.activeElement === ultimo || !modalAtiva.contains(document.activeElement))) {
            evento.preventDefault();
            primeiro.focus();
        }
    });
    atualizarModal();
});
