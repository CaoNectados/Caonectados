(() => {
    'use strict';
    const script = document.currentScript;
    const base = new URL(script.dataset.base, location.href).pathname.replace(/\/$/, '');
    const normalizar = texto => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const contas = {
        Adotante: 'adotante.beatriz.demo@example.com',
        ONG: 'ong.patinhas.demo@example.com',
        Protetor: 'protetor.mariana.demo@example.com'
    };
    const campo = (form, nome) => [...form.elements].find(el => el.name === nome);
    const digitos = quantidade => [...crypto.getRandomValues(new Uint8Array(quantidade))].map(n => n % 10);
    // Documentos de demonstração com dígitos verificadores; não representam uma entidade real.
    function documento(cnpj) {
        const numeros = cnpj ? [...digitos(8), 0, 0, 0, 1] : digitos(9);
        if (!cnpj && numeros.every(n => n === numeros[0])) numeros[0] = (numeros[0] + 1) % 10;
        const pesos = cnpj ? [[5,4,3,2,9,8,7,6,5,4,3,2], [6,5,4,3,2,9,8,7,6,5,4,3,2]]
            : [[10,9,8,7,6,5,4,3,2], [11,10,9,8,7,6,5,4,3,2]];
        pesos.forEach(lista => {
            const resto = numeros.reduce((soma, n, i) => soma + n * lista[i], 0) % 11;
            numeros.push(resto < 2 ? 0 : 11 - resto);
        });
        return numeros.join('');
    }
    function tipo(form) {
        const formId = form.getAttribute('id') || '';
        const rota = new URL(form.action, location.href).pathname.slice(base.length).replace(/\/$/, '');
        if (formId.startsWith('form-onboarding-')) return 'perfil';
        if (formId === 'form-editar-perfil') return 'perfil';
        if (formId === 'form-pagina' || rota === '/pagina-perfil/rede/adicionar') return 'pagina';
        if (rota === '/login') return 'login';
        if (rota === '/cadastro') return 'cadastro';
        if (campo(form, 'raca_id') && campo(form, 'nome') && form.method.toLowerCase() === 'post') return 'animal';
        if (/^\/admin\/(especie|raca|regiao)\/(salvar|atualizar)$/.test(rota)) return rota.split('/')[2];
        if (campo(form, 'justificativa') || campo(form, 'justificativa_recusa') || campo(form, 'motivo_recusa') || campo(form, 'motivo')) return 'justificativa';
        if (campo(form, 'nova_senha') || rota === '/redefinir-senha/processar') return 'senha';
        if (campo(form, 'novo_email') || rota === '/esqueci-senha/processar') return 'email';
        if (form.method.toLowerCase() === 'get' && [...form.elements].some(el => ['q','busca'].includes(el.name))) return 'pesquisa';
        return null;
    }
    function escrever(el, valor, substituir) {
        if (!el || el.disabled || el.readOnly || ['hidden','file','submit','button'].includes(el.type)) return false;
        if (!substituir && (el.type === 'checkbox' ? el.checked : el.value.trim() !== '')) return false;
        if (el instanceof HTMLSelectElement) {
            const opcoes = [...el.options].filter(o => o.value && !o.disabled);
            const opcao = opcoes.find(o => normalizar(o.value) === normalizar(String(valor)))
                || opcoes.find(o => normalizar(o.text).includes(normalizar(String(valor))));
            if (!opcao) return false;
            el.value = opcao.value;
        } else if (el.type === 'checkbox') el.checked = Boolean(valor);
        else el.value = String(valor).slice(0, el.maxLength > 0 ? el.maxLength : undefined);
        el.dispatchEvent(new Event('input', {bubbles:true}));
        // Alguns filtros antigos enviam o formulário no onchange. O exemplo só preenche.
        if (!/submit\s*\(/.test(el.getAttribute('onchange') || '')) el.dispatchEvent(new Event('change', {bubbles:true}));
        return true;
    }
    const escolher = (form, nome, texto, substituir) => {
        const el = campo(form, nome);
        if (!(el instanceof HTMLSelectElement)) return;
        const disponivel = [...el.options].find(o => o.value && !o.disabled && normalizar(o.text).includes(normalizar(texto)))
            || [...el.options].find(o => o.value && !o.disabled);
        if (disponivel) escrever(el, disponivel.value, substituir);
    };
    async function raca(form, substituir) {
        const el = campo(form, 'raca_id');
        if (!el) return;
        await new Promise(resolve => {
            if (!el.disabled && [...el.options].some(o => o.value)) return resolve();
            const observador = new MutationObserver(() => {
                if (!el.disabled && [...el.options].some(o => o.value)) terminar();
            });
            const terminar = () => { observador.disconnect(); clearTimeout(limite); resolve(); };
            const limite = setTimeout(terminar, 8000);
            observador.observe(el, {childList:true, attributes:true});
        });
        escolher(form, 'raca_id', 'sem raça definida', substituir);
    }
    async function preencher(form, categoria, substituir, ator) {
        const sufixo = Date.now().toString().slice(-6);
        const ong = campo(form, 'tipo_documento')?.value === 'cnpj';
        const dados = {
            nome: 'Marina Duarte Ribeiro', nome_usuario: 'Marina Duarte Ribeiro',
            nome_fantasia: ong ? `ONG Laços da Fronteira ${sufixo}` : 'Marina Duarte — Proteção Animal',
            telefone: '(45) 90000-0000', dt_nasc: '1995-06-18', data_abertura_cnpj: '2020-03-12',
            cnpj_cpf: documento(ong), codigo_documento: documento(ong),
            obs_casa: 'Avenida Morenitas', logradouro: 'Avenida Morenitas', numero: '100',
            tipo_moradia:'casa', tipo_morada:'casa', espaco_interior:'medio', tamanho_interno_morada:'medio',
            espaco_externo:'medio', possui_criancas:'nao', possui_outros_pets:'nao',
            descricao:'Cadastro fictício para apresentação. Tenho uma rotina tranquila e espaço seguro para acolher animais com carinho e responsabilidade.',
            chave_pix:'apresentacao@example.com', instagram:'https://www.instagram.com/caonectados2026/',
            facebook:'https://www.facebook.com/', tipo_rede:'instagram', link_rede:'https://www.instagram.com/caonectados2026/'
        };
        if (categoria === 'animal') Object.assign(dados, {
            nome:`Caju demonstração ${sufixo}`, dt_nasc:'2024-05-10', sexo:'macho', porte:'pequeno', status:'disponivel',
            descricao:'Animal fictício para a apresentação: Caju é um companheiro carinhoso e brincalhão. Procura um lar seguro e uma família com tempo para passeios e cuidados.',
            historico_saude:'Dados fictícios para demonstração. Histórico veterinário ainda não informado.', vacinado:false, castrado:false
        });
        if (categoria === 'login') {
            escrever(campo(form,'email'), contas[ator] || contas.Adotante, substituir);
            return 'E-mail de demonstração preenchido. Use a senha salva no navegador ou digite a senha da conta. Nenhuma senha real fica no código público.';
        }
        if (['cadastro','email','senha'].includes(categoria)) {
            const email = `apresentacao.${Date.now()}@example.com`;
            const exemplos = {email, novo_email:email, confirmar_email:email, senha:'CaoApresentacao2026!', senha_confirmacao:'CaoApresentacao2026!', nova_senha:'CaoApresentacao2026!', confirmar_senha:'CaoApresentacao2026!'};
            Object.entries(exemplos).forEach(([nome, valor]) => escrever(campo(form,nome), valor, substituir));
            return 'Exemplo preenchido. Senha fictícia: CaoApresentacao2026! Use um e-mail seu para receber os códigos e aceite os termos manualmente. Nada foi enviado.';
        }
        if (categoria === 'especie') dados.nome = `Coelho demonstração ${sufixo}`;
        if (categoria === 'raca') dados.nome = `Não definida demo ${sufixo}`;
        if (categoria === 'regiao') dados.nome_regiao = `Bairro demonstração ${sufixo}`;
        if (categoria === 'justificativa') Object.assign(dados, {
            motivo:'Exemplo para a apresentação: registrar a decisão administrativa após conferir os dados e a documentação.',
            justificativa:'Exemplo: o interessado precisa confirmar a disponibilidade para os cuidados antes de uma nova análise.',
            justificativa_recusa:'Exemplo: documentação incompleta. Solicitar correção dos dados antes de uma nova análise.',
            motivo_recusa:'Exemplo: documentação incompleta. Solicitar correção antes de uma nova análise.'
        });
        if (categoria === 'pesquisa') Object.assign(dados, {q:'Patinhas', busca:'Patinhas'});
        Object.entries(dados).forEach(([nome, valor]) => escrever(campo(form,nome), valor, substituir));
        escolher(form, 'busca_bairro_texto', 'Porto Meira', substituir);
        escolher(form, 'especie_id', 'cão', substituir);
        if (categoria === 'animal') await raca(form, substituir);
        for (const nome of ['preferencias_especie[]','preferencias_porte[]','preferencias_sexo[]','tracos[]']) {
            const opcoes = [...form.elements].filter(el => el.name === nome && el.type === 'checkbox' && !el.disabled);
            if (opcoes.length && !opcoes.some(el => el.checked)) escrever(opcoes[0], true, true);
        }
        return 'Dados fictícios preenchidos. Revise os campos, fotos, documentos e termos antes de salvar. Nada foi enviado.';
    }
    function iniciar() {
        if (!document.getElementById('estilos-apresentacao')) {
            const estilos = document.createElement('style');
            estilos.id = 'estilos-apresentacao';
            estilos.textContent = `
                .apresentacao-atalho{position:relative;align-self:flex-end;z-index:40;margin:0 0 .5rem}
                .apresentacao-bolha{display:grid;place-items:center;width:42px;height:42px;padding:0;border:2px solid rgb(var(--color-rosa-3));border-radius:9999px;background:#514870;color:#fff;font-size:1.25rem;line-height:1;box-shadow:0 4px 14px #20152c40;cursor:pointer;transition:transform .15s,box-shadow .15s}
                .apresentacao-bolha:hover{transform:scale(1.06);box-shadow:0 6px 18px #20152c55}
                .apresentacao-bolha:focus-visible{outline:3px solid #e05e97;outline-offset:3px}
                .apresentacao-painel[hidden]{display:none!important}
                .apresentacao-painel{position:absolute;right:0;top:calc(100% + .5rem);width:min(19rem,calc(100vw - 2rem));max-height:min(70vh,34rem);overflow:auto;padding:.75rem;border:1px solid rgb(var(--color-rosa-3));border-radius:1rem;background:rgb(var(--color-surface));color:rgb(var(--color-text-dark));box-shadow:0 12px 35px #20152c40}
            `;
            document.head.append(estilos);
        }
        document.querySelectorAll('form').forEach(form => {
            const categoria = tipo(form);
            if (!categoria || form.querySelector('[data-exemplos]')) return;
            const controles = document.createElement('div'); controles.dataset.exemplos = '';
            controles.className = 'apresentacao-atalho';
            const abrir = document.createElement('button'); abrir.type = 'button';
            abrir.className = 'apresentacao-bolha';
            abrir.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><ellipse cx="5.5" cy="8" rx="2.2" ry="3"/><ellipse cx="10" cy="5.5" rx="2.2" ry="3"/><ellipse cx="15" cy="5.5" rx="2.2" ry="3"/><ellipse cx="19" cy="8" rx="2.2" ry="3"/><path d="M12 10.5c-3.3 0-7.2 4.2-7.2 7.2a3 3 0 0 0 3.1 3.1c1.4 0 2.5-.8 4.1-.8s2.7.8 4.1.8a3 3 0 0 0 3.1-3.1c0-3-3.9-7.2-7.2-7.2Z"/></svg>';
            abrir.setAttribute('aria-label', 'Abrir ferramentas de apresentação');
            abrir.setAttribute('aria-expanded', 'false'); abrir.title = 'Ferramentas de apresentação';
            const painel = document.createElement('section');
            painel.className = 'apresentacao-painel text-sm'; painel.hidden = true;
            const titulo = document.createElement('p'); titulo.className = 'mb-2 font-bold'; titulo.textContent = 'Modo banca';
            const aviso = document.createElement('p'); aviso.className = 'mt-2 break-words text-xs'; aviso.setAttribute('role','status');
            aviso.textContent = 'Preenche apenas campos vazios. Não salva nem envia o formulário.';
            const label = document.createElement('label'); label.className = 'my-2 flex items-center gap-2 text-xs';
            const substituir = document.createElement('input'); substituir.type = 'checkbox';
            label.append(substituir, 'Substituir também os campos preenchidos');
            const botao = document.createElement('button'); botao.type = 'button'; botao.textContent = 'Preencher exemplo';
            botao.className = 'btn-primario min-h-11 w-full';
            let atores;
            painel.append(titulo);
            if (categoria === 'login') {
                atores = document.createElement('select'); atores.className = 'input-padrao mb-2'; atores.setAttribute('aria-label','Conta de demonstração');
                Object.keys(contas).forEach(nome => atores.add(new Option(nome,nome)));
                painel.append(atores);
            }
            painel.append(botao, label, aviso); controles.append(painel, abrir); form.prepend(controles);
            abrir.addEventListener('click', () => {
                painel.hidden = !painel.hidden;
                abrir.setAttribute('aria-expanded', String(!painel.hidden));
            });
            botao.addEventListener('click', async () => {
                botao.disabled = true;
                try { aviso.textContent = await preencher(form,categoria,substituir.checked,atores?.value); }
                catch { aviso.textContent = 'Algum campo não carregou. Confira as opções e complete-o manualmente.'; }
                finally { botao.disabled = false; }
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar, {once:true});
    else iniciar();
})();
