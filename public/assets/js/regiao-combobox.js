// Combobox acessível de bairro para perfil/editar.php. Substitui o popup
// inconsistente de <datalist> por uma lista filtrável que também funciona ao toque.
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('input-busca-bairro');
    const datalist = document.getElementById('lista-regioes');
    const hiddenId = document.getElementById('regiao_id_hidden');
    const panel = document.getElementById('lista-regioes-combobox');
    if (!input || !datalist || !hiddenId || !panel) return;

    const regioes = [...datalist.options].map((option) => ({
        id: option.dataset.id || '',
        nome: option.value.trim(),
    })).filter((regiao) => regiao.id && regiao.nome);

    let regioesVisiveis = [];
    let indiceAtivo = -1;

    const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    const aberto = () => !panel.classList.contains('hidden');

    function fechar() {
        panel.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        indiceAtivo = -1;
    }

    function escolher(regiao) {
        input.value = regiao.nome;
        hiddenId.value = regiao.id;
        input.setCustomValidity('');
        fechar();
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function renderizar() {
        const consulta = normalizar(input.value.trim());
        regioesVisiveis = consulta
            ? regioes.filter((regiao) => normalizar(regiao.nome).includes(consulta))
            : regioes;
        panel.replaceChildren();
        indiceAtivo = -1;

        if (regioesVisiveis.length === 0) {
            const vazio = document.createElement('div');
            vazio.className = 'regiao-dropdown-vazio';
            vazio.setAttribute('role', 'option');
            vazio.textContent = 'Nenhum bairro encontrado.';
            panel.append(vazio);
        } else {
            regioesVisiveis.forEach((regiao, indice) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.id = `regiao-opcao-${regiao.id}`;
                item.className = 'regiao-dropdown-item w-full text-left';
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', 'false');
                item.textContent = regiao.nome;
                item.addEventListener('pointerdown', (event) => event.preventDefault());
                item.addEventListener('click', () => escolher(regiao));
                panel.append(item);

                if (input.value.trim() && normalizar(regiao.nome) === consulta) {
                    indiceAtivo = indice;
                }
            });
        }

        panel.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
        if (indiceAtivo >= 0) marcarAtivo(indiceAtivo);
        else input.removeAttribute('aria-activedescendant');
    }

    function marcarAtivo(indice) {
        indiceAtivo = indice;
        panel.querySelectorAll('[role="option"]').forEach((item, index) => {
            const ativo = index === indice;
            item.setAttribute('aria-selected', ativo ? 'true' : 'false');
            item.classList.toggle('bg-rosa-1/30', ativo);
            item.classList.toggle('dark:bg-preto2', ativo);
        });
        const itemAtivo = panel.querySelectorAll('[role="option"]')[indice];
        if (itemAtivo?.id) input.setAttribute('aria-activedescendant', itemAtivo.id);
    }

    input.addEventListener('focus', renderizar);
    input.addEventListener('click', () => {
        if (!aberto()) renderizar();
    });
    input.addEventListener('input', () => {
        const exata = regioes.find((regiao) => normalizar(regiao.nome) === normalizar(input.value.trim()));
        hiddenId.value = exata?.id || '';
        renderizar();
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            fechar();
        } else if (event.key === 'ArrowDown' && aberto() && regioesVisiveis.length) {
            event.preventDefault();
            marcarAtivo(Math.min(indiceAtivo + 1, regioesVisiveis.length - 1));
        } else if (event.key === 'ArrowUp' && aberto() && regioesVisiveis.length) {
            event.preventDefault();
            marcarAtivo(Math.max(indiceAtivo - 1, 0));
        } else if (event.key === 'Enter' && aberto() && regioesVisiveis.length) {
            event.preventDefault();
            escolher(regioesVisiveis[indiceAtivo >= 0 ? indiceAtivo : 0]);
        }
    });

    document.addEventListener('pointerdown', (event) => {
        if (!panel.contains(event.target) && event.target !== input) fechar();
    });

    const selecionada = regioes.find((regiao) => regiao.id === hiddenId.value);
    if (selecionada) input.value = selecionada.nome;
});
