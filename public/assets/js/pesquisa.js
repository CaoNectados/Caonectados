(() => {
    'use strict';
    const root = document.getElementById('pesquisa');
    if (!root) return;
    const base = root.dataset.base;
    const filtros = JSON.parse(root.dataset.filtros);
    const form = document.getElementById('form-pesquisa');
    const input = document.getElementById('termo-pesquisa');
    const historico = document.getElementById('historico-pesquisa');
    const resultados = document.getElementById('resultados-pesquisa');
    const chave = `caonectados-pesquisas-${root.dataset.usuario}`;
    let recentes = [];
    try {
        const salvo = JSON.parse(localStorage.getItem(chave) || '[]');
        if (Array.isArray(salvo)) recentes = salvo.filter(x => typeof x === 'string' && x.length <= 100).slice(0, 10);
    } catch (_) { /* Pesquisa continua disponível sem armazenamento local. */ }
    function salvarHistorico(termo) {
        termo = termo.trim().slice(0, 100);
        if (!termo) return;
        recentes = [termo, ...recentes.filter(x => x.toLocaleLowerCase() !== termo.toLocaleLowerCase())].slice(0, 10);
        try { localStorage.setItem(chave, JSON.stringify(recentes)); } catch (_) {}
    }
    function mostrarHistorico(mostrar) {
        historico.classList.toggle('hidden', !mostrar);
        resultados.classList.toggle('hidden', mostrar);
        input.setAttribute('aria-expanded', String(mostrar));
    }
    function desenharHistorico() {
        const lista = document.getElementById('lista-historico-pesquisa');
        lista.replaceChildren();
        const termo = input.value.trim().toLocaleLowerCase();
        const opcoes = recentes.filter(x => !termo || x.toLocaleLowerCase().includes(termo));
        opcoes.forEach(texto => {
            const item = document.createElement('li');
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.className = 'min-h-11 w-full rounded-lg px-1 text-left text-sm text-text-dark hover:bg-primary/10';
            botao.textContent = texto;
            botao.addEventListener('click', () => { input.value = texto; form.requestSubmit(); });
            item.append(botao); lista.append(item);
        });
        document.getElementById('historico-vazio-pesquisa').classList.toggle('hidden', opcoes.length > 0);
        document.getElementById('historico-vazio-pesquisa').textContent = recentes.length ? 'Nenhuma pesquisa recente corresponde ao termo.' : 'Suas pesquisas recentes aparecerão aqui.';
    }
    salvarHistorico(filtros.q || '');
    input.addEventListener('focus', () => { desenharHistorico(); mostrarHistorico(true); });
    input.addEventListener('input', desenharHistorico);
    form.addEventListener('submit', () => salvarHistorico(input.value));
    document.getElementById('limpar-historico-pesquisa').addEventListener('click', () => {
        recentes = [];
        try { localStorage.removeItem(chave); } catch (_) {}
        desenharHistorico(); input.focus();
    });
    document.addEventListener('pointerdown', e => {
        if (!form.contains(e.target) && !historico.contains(e.target)) mostrarHistorico(false);
    });

    const modal = document.getElementById('modal-filtros-pesquisa');
    const abrir = document.getElementById('abrir-filtros-pesquisa');
    const fechar = modal.querySelector('[aria-label="Fechar filtros"]');
    function fecharFiltros() { modal.classList.add('hidden'); abrir.focus(); }
    abrir.addEventListener('click', () => {
        mostrarHistorico(false);
        modal.querySelector('[name=q]').value = input.value;
        modal.classList.remove('hidden'); fechar.focus();
    });
    fechar.addEventListener('click', fecharFiltros);
    modal.addEventListener('click', e => { if (e.target === modal) fecharFiltros(); });
    document.addEventListener('keydown', e => {
        if (!modal.classList.contains('hidden')) {
            if (e.key === 'Escape') { e.preventDefault(); fecharFiltros(); }
            if (e.key === 'Tab') {
                const alvos = [...modal.querySelectorAll('a[href],button,input:not([type=hidden]),select')].filter(x => !x.disabled && x.getClientRects().length);
                const primeiro = alvos[0], ultimo = alvos[alvos.length - 1];
                if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
                if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
            }
        } else if (e.key === 'Escape') { mostrarHistorico(false); input.blur(); }
    });
    const bairro = document.getElementById('feed-input-busca-bairro');
    bairro.addEventListener('input', () => {
        const opcao = [...document.querySelectorAll('#feed-lista-regioes option')].find(x => x.value.toLocaleLowerCase() === bairro.value.trim().toLocaleLowerCase());
        document.getElementById('feed-regiao-id-hidden').value = opcao?.dataset.id || '';
    });
    const especie = document.getElementById('filtro-especie');
    const raca = document.getElementById('filtro-raca');
    let consultaRacas = 0;
    async function carregarRacas(valor, anterior = '') {
        const consulta = ++consultaRacas;
        raca.replaceChildren(new Option('Todas', ''));
        raca.disabled = false;
        if (!valor) return;
        raca.disabled = true;
        try {
            const resposta = await fetch(`${base}/raca/json?especie_id=${encodeURIComponent(valor)}`, {headers: {Accept:'application/json'}});
            const dados = await resposta.json();
            if (!resposta.ok || !Array.isArray(dados.dados || dados.data)) throw new Error();
            if (consulta !== consultaRacas) return;
            (dados.dados || dados.data).forEach(x => raca.add(new Option(x.nome, x.raca_id || x.id)));
            raca.value = anterior;
        } catch (_) {
            if (consulta === consultaRacas) raca.replaceChildren(new Option('Não foi possível carregar raças', ''));
        } finally { if (consulta === consultaRacas) raca.disabled = false; }
    }
    especie.addEventListener('change', () => carregarRacas(especie.value));
    if (especie.value) carregarRacas(especie.value, raca.dataset.oldValue);
    modal.querySelector('form').addEventListener('submit', () => salvarHistorico(modal.querySelector('[name=q]').value));

    const mais = document.getElementById('mais-pesquisa');
    let offset = Number(root.dataset.offset);
    const ids = new Set([...document.querySelectorAll('.resultado-animal')].map(x => x.dataset.animalId));
    mais?.addEventListener('click', async () => {
        if (mais.disabled) return;
        mais.disabled = true; mais.textContent = 'Carregando...';
        const status = document.getElementById('status-pesquisa');
        const params = new URLSearchParams();
        Object.entries(filtros).forEach(([nome,valor]) => { if (valor !== null && valor !== '') params.set(nome,valor); });
        const retorno = '/pesquisar?' + params.toString();
        params.set('offset', String(offset));
        try {
            const resposta = await fetch(`${base}/pesquisar/carregar-mais?${params}`, {headers:{Accept:'application/json'}});
            const dados = await resposta.json();
            if (!resposta.ok || dados.status !== 'sucesso') throw new Error();
            dados.animais.forEach(animal => {
                const id = String(animal.animal_id);
                if (ids.has(id)) return;
                ids.add(id);
                const link = document.createElement('a');
                link.className = 'resultado-animal group relative aspect-[3/4] overflow-hidden bg-surface';
                link.dataset.animalId = id;
                link.href = animal.url_detalhes + '&retorno=' + encodeURIComponent(retorno);
                link.setAttribute('aria-label', `Ver ${animal.nome}, ${animal.especie_nome}`);
                const foto = document.createElement('img');
                foto.src = animal.fotos[0] || `${base}/assets/img/perfil-placeholder.png`;
                foto.alt = animal.nome; foto.loading = 'lazy'; foto.className = 'h-full w-full object-cover transition-transform group-hover:scale-105 motion-reduce:transition-none';
                const nome = document.createElement('span'); nome.textContent = animal.nome;
                nome.className = 'absolute inset-x-0 bottom-0 hidden truncate bg-black/55 px-1 py-1 text-xs text-white sm:block sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus:opacity-100';
                link.append(foto,nome); document.getElementById('grade-pesquisa').append(link);
            });
            offset = dados.proximoOffset;
            mais.hidden = !dados.temMais;
            mais.classList.toggle('hidden', !dados.temMais);
            status.textContent = dados.temMais ? 'Mais animais carregados.' : 'Você viu todos os resultados.';
        } catch (_) { status.textContent = 'Não foi possível carregar mais animais. Tente novamente.'; }
        finally { mais.disabled = false; mais.textContent = 'Carregar mais animais'; }
    });
})();
