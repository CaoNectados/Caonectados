(() => {
    'use strict';
    const modal = document.getElementById('modal-caonectou');
    const cartao = document.getElementById('cartao-caonectou');
    const fonte = document.getElementById('dados-caonectou');
    if (!modal || !cartao || !fonte) return;
    const encontros = JSON.parse(fonte.textContent);
    const gif = document.getElementById('gif-caonectou');
    const vistos = new Set();
    const reduzirMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let temporizador;
    let retornoFoco;
    let chaveAtual;
    let scrollBloqueadoAntes;

    const visto = dados => {
        if (vistos.has(dados.sessaoKey)) return true;
        try { return sessionStorage.getItem(dados.sessaoKey) === '1'; }
        catch (_) { return false; }
    };
    const marcarVisto = () => {
        if (!chaveAtual) return;
        vistos.add(chaveAtual);
        try { sessionStorage.setItem(chaveAtual, '1'); } catch (_) {}
    };
    const proximo = () => {
        if (modal.classList.contains('caonectou-aberto') || document.querySelector('dialog[open], [role="dialog"][aria-hidden="false"]')) return;
        const id = Object.keys(encontros).find(id => !visto(encontros[id]));
        if (id) abrir(id);
    };
    const abrir = (id, origem) => {
        const dados = encontros[id];
        if (!dados) return;
        window.clearTimeout(temporizador);
        retornoFoco = origem || document.activeElement;
        chaveAtual = dados.sessaoKey;
        modal.dataset.solicitacaoId = id;
        document.getElementById('nome-animal-caonectou').textContent = dados.animal;
        document.getElementById('nome-adotante-caonectou').textContent = dados.adotante;
        document.getElementById('nome-responsavel-caonectou').textContent = dados.responsavel;
        document.getElementById('mensagem-caonectou').textContent = dados.mensagem;
        [['foto-animal-caonectou', dados.fotoAnimal, dados.animal], ['foto-adotante-caonectou', dados.fotoAdotante, dados.adotante], ['foto-responsavel-caonectou', dados.fotoResponsavel, dados.responsavel]].forEach(([id, caminho, nome]) => {
            const imagem = document.getElementById(id);
            imagem.onerror = () => {
                imagem.onerror = null;
                imagem.src = new URL('assets/img/perfil-placeholder.png', gif.dataset.src.split('/assets/')[0] + '/').href;
            };
            imagem.src = caminho;
            imagem.alt = nome;
        });
        const whatsapp = document.getElementById('whatsapp-caonectou');
        whatsapp.removeAttribute('href');
        if (dados.whatsapp) whatsapp.href = dados.whatsapp;
        whatsapp.textContent = dados.rotuloWhatsapp;
        whatsapp.classList.toggle('hidden', !dados.whatsapp);
        whatsapp.classList.toggle('inline-flex', Boolean(dados.whatsapp));
        const aviso = document.getElementById('whatsapp-indisponivel-caonectou');
        aviso.textContent = dados.semTelefone;
        aviso.classList.toggle('hidden', Boolean(dados.whatsapp));
        gif.src = `${gif.dataset.src}?replay=${Date.now()}`;
        modal.classList.remove('hidden', 'caonectou-final');
        modal.classList.add('caonectou-aberto');
        modal.setAttribute('aria-hidden', 'false');
        scrollBloqueadoAntes = document.body.classList.contains('overflow-hidden');
        document.body.classList.add('overflow-hidden');
        cartao.classList.remove('cartao-visivel');
        modal.setAttribute('tabindex', '-1');
        modal.focus();
        temporizador = window.setTimeout(() => {
            modal.classList.add('caonectou-final');
            requestAnimationFrame(() => cartao.classList.add('cartao-visivel'));
            document.getElementById('fechar-caonectou').focus();
            marcarVisto();
        }, reduzirMovimento ? 0 : 1750);
    };
    const fechar = () => {
        window.clearTimeout(temporizador);
        marcarVisto();
        modal.classList.add('hidden');
        modal.classList.remove('caonectou-aberto', 'caonectou-final');
        modal.setAttribute('aria-hidden', 'true');
        if (!scrollBloqueadoAntes) document.body.classList.remove('overflow-hidden');
        if (retornoFoco?.isConnected) retornoFoco.focus();
        temporizador = window.setTimeout(proximo, 150);
    };
    document.querySelectorAll('[data-caonectou-id]').forEach(botao => {
        botao.addEventListener('click', () => abrir(botao.dataset.caonectouId, botao));
    });
    document.getElementById('fechar-caonectou').addEventListener('click', fechar);
    document.getElementById('continuar-caonectou').addEventListener('click', fechar);
    modal.addEventListener('click', evento => { if (evento.target === modal) fechar(); });
    modal.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') { evento.preventDefault(); fechar(); }
        if (evento.key !== 'Tab') return;
        const alvos = [...cartao.querySelectorAll('button, a[href]')].filter(el => el.getClientRects().length);
        if (!alvos.length) { evento.preventDefault(); return; }
        const primeiro = alvos[0], ultimo = alvos[alvos.length - 1];
        if (evento.shiftKey && document.activeElement === primeiro) { evento.preventDefault(); ultimo.focus(); }
        else if (!evento.shiftKey && document.activeElement === ultimo) { evento.preventDefault(); primeiro.focus(); }
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', proximo, { once: true });
    else proximo();
})();
