<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$tipoPerfil = $tipoPerfil ?? 'usuario';

$rotaHome = match ($tipoPerfil) {
    'adotante'      => '/feed',
    'administrador' => '/admin/dashboard',
    default         => '/perfil',
};

$placeholderBusca = match ($tipoPerfil) {
    'adotante'                    => 'Buscar animal, raça, ONG...',
    'protetor', 'ong'             => 'Buscar nos seus animais...',
    'administrador'               => 'Buscar usuário, ONG ou ID...',
    default                       => 'Buscar...',
};
?>

<div class="max-w-md lg:max-w-3xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6">
    <div class="pt-6 mb-4">
        <div class="relative flex items-center bg-roxo2 dark:bg-primary rounded-full px-5 py-3 shadow-sm">
            <span class="text-white/80 mr-3">🔍</span>
            <input type="text" id="input-pesquisa" placeholder="<?= htmlspecialchars($placeholderBusca) ?>" autocomplete="off"
                   class="flex-1 bg-transparent outline-none text-white placeholder-white/70 font-poppins text-sm">
            <button type="button" onclick="mostrarModalFeedback('informativo', 'Filtros de pesquisa avançados ainda estão sendo implementados.')"
                    class="flex items-center gap-1.5 text-white text-xs font-bold shrink-0 ml-2 hover:opacity-80 transition">
                Filtros <span class="text-base leading-none">⚙️</span>
            </button>
        </div>
        <?php if (!empty($tipoPerfil) && $tipoPerfil !== 'usuario'): ?>
            <p class="text-xs text-text-muted mt-2 ml-1">Atalho: digite para localizar <?= $tipoPerfil === 'administrador' ? 'cadastros e usuários' : ($tipoPerfil === 'adotante' ? 'animais e ONGs' : 'seus animais') ?>.</p>
        <?php endif; ?>
    </div>

    <!-- Histórico (localStorage — sem tabela nova só pra isso). Só aparece com o campo focado
         e vazio — no resto do tempo quem ocupa esse espaço é o feed ocioso ou os resultados. -->
    <div id="bloco-historico" class="mb-6 hidden">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-text-muted"></span>
            <button type="button" onclick="limparHistoricoPesquisa()" class="text-xs font-bold text-primary dark:text-roxinhoFofo underline hidden" id="btn-limpar-historico">Limpar histórico</button>
        </div>
        <ul id="lista-historico" class="space-y-3"></ul>
    </div>

    <!-- Feed ocioso — estilo TikTok: aparece quando o campo não está em foco/vazio, some assim
         que o usuário clica pra digitar. Renderizado com os mesmos templates dos resultados de
         busca (renderizarAdotante/Protetor/Admin), só que a partir de $feedInicial (sem termo). -->
    <div id="area-feed" class="space-y-6"></div>

    <div id="area-resultados" class="space-y-6 hidden"></div>

    <div id="pesquisa-vazio" class="hidden text-center py-12">
        <span class="text-4xl block mb-3">🔍</span>
        <p class="text-sm text-text-muted">Nenhum resultado encontrado.</p>
    </div>

    <div id="pesquisa-carregando" class="hidden text-center py-6 text-sm text-text-muted">Buscando...</div>
</div>

<!-- BARRA INFERIOR (mobile) — mesmo padrão do Feed, "Pesquisar" ativo -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-primary flex items-center justify-around h-16 shadow-[0_-4px_12px_rgba(0,0,0,0.2)]">
    <a href="<?= $urlBase . $rotaHome ?>" class="flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/home.svg" alt="Início" class="w-6 h-6 brightness-0 invert opacity-70">
    </a>
    <a href="<?= $urlBase ?>/pesquisar" class="flex flex-col items-center justify-center text-white">
        <img src="<?= $urlBase ?>/assets/icons/navbar/pesquisar.svg" alt="Pesquisar" class="w-6 h-6 brightness-0 invert">
    </a>
    <a href="<?= $urlBase ?>/chats" class="relative flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/chat.svg" alt="Chat" class="w-6 h-6 brightness-0 invert opacity-70">
        <?php if (!empty($naoLidasChat)): ?>
            <span class="absolute top-0.5 right-1/4 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-rosaAlerta text-white text-[10px] font-bold flex items-center justify-center leading-none"><?= $naoLidasChat > 9 ? '9+' : (int) $naoLidasChat ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= $urlBase ?>/perfil" class="flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/perfil.svg" alt="Perfil" class="w-6 h-6 brightness-0 invert opacity-70">
    </a>
</nav>

<script>
(function () {
    'use strict';

    const urlBase = '<?= $urlBase ?>';
    const tipoPerfil = <?= json_encode($tipoPerfil) ?>;
    const feedInicial = <?= json_encode($feedInicial ?? []) ?>;
    const CHAVE_HISTORICO = 'caonectados_pesquisa_historico';
    const input = document.getElementById('input-pesquisa');
    let timeoutBusca = null;

    // ---------- Estados da tela: ocioso (feed) / focado (histórico) / digitando (resultados) ----------
    function renderizarPorPerfil(resultados) {
        if (tipoPerfil === 'adotante') return renderizarAdotante(resultados);
        if (tipoPerfil === 'protetor' || tipoPerfil === 'ong') return renderizarProtetor(resultados);
        if (tipoPerfil === 'administrador') return renderizarAdmin(resultados);
        return '';
    }

    function estadoOcioso() {
        document.getElementById('area-feed').classList.remove('hidden');
        document.getElementById('bloco-historico').classList.add('hidden');
        document.getElementById('area-resultados').classList.add('hidden');
        document.getElementById('pesquisa-vazio').classList.add('hidden');
    }

    function estadoFocado() {
        document.getElementById('area-feed').classList.add('hidden');
        document.getElementById('area-resultados').classList.add('hidden');
        document.getElementById('pesquisa-vazio').classList.add('hidden');
        renderizarHistorico();
        document.getElementById('bloco-historico').classList.remove('hidden');
    }

    function estadoResultados() {
        document.getElementById('area-feed').classList.add('hidden');
        document.getElementById('bloco-historico').classList.add('hidden');
        document.getElementById('area-resultados').classList.remove('hidden');
    }

    const areaFeed = document.getElementById('area-feed');
    if (areaFeed) {
        areaFeed.innerHTML = renderizarPorPerfil(feedInicial);
    }

    input.addEventListener('focus', function () {
        if (input.value.trim().length < 2) {
            estadoFocado();
        }
    });

    // Delay curto: clicar num item do histórico dispara blur antes do click, então sem o delay
    // o campo voltaria pro feed ocioso antes do onclick do histórico conseguir rodar.
    input.addEventListener('blur', function () {
        setTimeout(() => {
            if (document.activeElement !== input && input.value.trim().length === 0) {
                estadoOcioso();
            }
        }, 150);
    });

    // ---------- Histórico local (por navegador — não existe tabela de histórico no banco) ----------
    function lerHistorico() {
        try {
            return JSON.parse(localStorage.getItem(CHAVE_HISTORICO) || '[]');
        } catch (e) {
            return [];
        }
    }

    function salvarNoHistorico(termo) {
        termo = termo.trim();
        if (!termo) return;
        try {
            let historico = lerHistorico().filter(t => t.toLowerCase() !== termo.toLowerCase());
            historico.unshift(termo);
            historico = historico.slice(0, 8);
            localStorage.setItem(CHAVE_HISTORICO, JSON.stringify(historico));
        } catch (e) { /* localStorage indisponível — ignora silenciosamente */ }
    }

    window.limparHistoricoPesquisa = function () {
        try { localStorage.removeItem(CHAVE_HISTORICO); } catch (e) {}
        renderizarHistorico();
    };

    function renderizarHistorico() {
        const historico = lerHistorico();
        const lista = document.getElementById('lista-historico');
        const btnLimpar = document.getElementById('btn-limpar-historico');

        if (!historico.length) {
            lista.innerHTML = '';
            btnLimpar.classList.add('hidden');
            return;
        }

        btnLimpar.classList.remove('hidden');
        lista.innerHTML = historico.map(termo => `
            <li>
                <button type="button" class="text-left w-full text-text-dark dark:text-white hover:text-primary dark:hover:text-roxinhoFofo transition" onclick="document.getElementById('input-pesquisa').value='${termo.replace(/'/g, "\\'")}'; document.getElementById('input-pesquisa').dispatchEvent(new Event('input'));">
                    ${termo}
                </button>
            </li>
        `).join('');
    }

    renderizarHistorico();

    // ---------- Busca ao vivo ----------
    input.addEventListener('input', function () {
        const termo = input.value.trim();

        clearTimeout(timeoutBusca);

        if (termo.length < 2) {
            document.getElementById('area-resultados').innerHTML = '';
            estadoFocado();
            return;
        }

        estadoResultados();
        timeoutBusca = setTimeout(() => executarBusca(termo), 350);
    });

    async function executarBusca(termo) {
        document.getElementById('pesquisa-carregando').classList.remove('hidden');
        document.getElementById('pesquisa-vazio').classList.add('hidden');

        try {
            const resposta = await fetch(`${urlBase}/pesquisar/buscar?q=${encodeURIComponent(termo)}`, {
                headers: { Accept: 'application/json' }
            });
            const resultado = await resposta.json();

            if (resultado.status === 'sucesso') {
                salvarNoHistorico(termo);
                renderizarResultados(resultado.resultados);
            }
        } catch (erro) {
            console.error('Erro na pesquisa:', erro);
        } finally {
            document.getElementById('pesquisa-carregando').classList.add('hidden');
        }
    }

    function estaVazio(resultados) {
        return Object.values(resultados).every(lista => !lista || lista.length === 0);
    }

    function renderizarResultados(resultados) {
        const area = document.getElementById('area-resultados');
        area.innerHTML = '';

        if (estaVazio(resultados)) {
            document.getElementById('pesquisa-vazio').classList.remove('hidden');
            return;
        }
        document.getElementById('pesquisa-vazio').classList.add('hidden');
        area.innerHTML = renderizarPorPerfil(resultados);
    }

    function renderizarAdotante(r) {
        let html = '';

        if (r.ongs && r.ongs.length) {
            html += `<div><h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white mb-2">Ongs</h2><div class="space-y-2">`;
            html += r.ongs.map(ong => `
                <div class="flex items-center gap-3 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-full pl-1 pr-2 py-1 shadow-sm">
                    <img src="${ong.foto_perfil || urlBase + '/assets/img/perfil-placeholder.png'}" alt="" class="w-10 h-10 rounded-full object-cover border border-rosa-2">
                    <span class="flex-1 text-sm font-bold text-text-dark dark:text-white truncate">${ong.nome_fantasia}</span>
                    <a href="${ong.url}" class="flex items-center gap-1 bg-text-dark dark:bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shrink-0 hover:opacity-90 transition">Ir para Página &rsaquo;</a>
                </div>
            `).join('');
            html += `</div></div>`;
        }

        if (r.animais && r.animais.length) {
            html += `<div><h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white mb-2">Animais</h2><div class="grid grid-cols-3 sm:grid-cols-4 gap-1">`;
            html += r.animais.map(a => `
                <a href="${a.url}" class="aspect-square block overflow-hidden rounded-lg">
                    <img src="${a.foto || urlBase + '/assets/img/perfil-placeholder.png'}" alt="${a.nome}" class="w-full h-full object-cover hover:scale-105 transition">
                </a>
            `).join('');
            html += `</div></div>`;
        }

        return html;
    }

    function renderizarProtetor(r) {
        if (!r.meus_animais || !r.meus_animais.length) return '';

        const statusCores = { 'Disponível': 'bg-sucesso/15 text-sucesso', 'Em Análise': 'bg-laranja-1/20 text-laranja-1', 'Adotado': 'bg-primary/15 text-primary', 'Desativado': 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted' };

        return `<div><h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white mb-2">Meus Animais</h2><div class="space-y-2">` +
            r.meus_animais.map(a => `
                <a href="${a.url}" class="flex items-center gap-3 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-xl p-2 shadow-sm hover:shadow-md transition">
                    <img src="${a.foto || urlBase + '/assets/img/perfil-placeholder.png'}" alt="" class="w-12 h-12 rounded-lg object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-text-dark dark:text-white truncate">${a.nome}</p>
                        <p class="text-xs text-text-muted truncate">${a.raca_nome || ''}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0 ${statusCores[a.status] || 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'}">${a.status}</span>
                </a>
            `).join('') + `</div></div>`;
    }

    function renderizarAdmin(r) {
        let html = '';

        if (r.usuarios && r.usuarios.length) {
            html += `<div><h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white mb-2">Usuários</h2><div class="space-y-2">`;
            html += r.usuarios.map(u => `
                <a href="${u.url}" class="flex items-center justify-between bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-xl px-4 py-2.5 shadow-sm hover:shadow-md transition">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-text-dark dark:text-white truncate">Usuário ${u.nome}</p>
                        <p class="text-xs text-text-muted truncate">${u.email} · #${u.usuario_id}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white shrink-0">${u.tipo_atual}</span>
                </a>
            `).join('');
            html += `</div></div>`;
        }

        if (r.protetores && r.protetores.length) {
            html += `<div><h2 class="font-shantell text-lg font-bold text-text-dark dark:text-white mb-2">ONGs / Protetores</h2><div class="space-y-2">`;
            html += r.protetores.map(p => `
                <a href="${p.url}" class="flex items-center justify-between bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-xl px-4 py-2.5 shadow-sm hover:shadow-md transition">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-text-dark dark:text-white truncate">Ong ${p.nome_fantasia}</p>
                        <p class="text-xs text-text-muted truncate">${p.email} · #${p.protetor_id}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0 ${p.validado ? 'bg-sucesso/15 text-sucesso' : 'bg-laranja-1/20 text-laranja-1'}">${p.validado ? 'Validado' : 'Pendente'}</span>
                </a>
            `).join('');
            html += `</div></div>`;
        }

        return html;
    }
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
