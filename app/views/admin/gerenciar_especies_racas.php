<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="mx-auto max-w-figma p-4 sm:p-6 lg:p-8 min-h-screen">
    <!-- Cabeçalho da Página -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-text-dark dark:text-white flex items-center gap-2">
                <span class="text-3xl">📊</span> Gerenciamento Espécies/Raças
            </h1>
            <p class="text-sm text-text-muted mt-1">Aprove, edite ou gerencie as espécies e raças cadastradas no sistema.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= URL_BASE ?>/admin/especie/cadastrar" class="btn-secundario text-xs sm:text-sm whitespace-nowrap">
                + Nova Espécie
            </a>
            <a href="<?= URL_BASE ?>/admin/raca/cadastrar" class="btn-primario text-xs sm:text-sm whitespace-nowrap">
                + Nova Raça
            </a>
        </div>
    </div>

    <!-- Abas Principais -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <a href="?aba=sugestoes" class="flex items-center justify-center gap-3 p-4 rounded-2xl <?= (isset($_GET['aba']) && $_GET['aba'] === 'sugestoes') ? 'bg-primary text-white shadow-md' : 'bg-surface dark:bg-preto2 border border-cinzaMarrom/30 text-text-dark dark:text-white hover:bg-rosa-1/40' ?> font-poppins font-semibold transition">
            <span class="text-xl">💡</span> Sugestões da API
        </a>
        <a href="?aba=ativos" class="flex items-center justify-center gap-3 p-4 rounded-2xl <?= (!isset($_GET['aba']) || $_GET['aba'] === 'ativos') ? 'bg-verdeMusgo text-white shadow-md' : 'bg-surface dark:bg-preto2 border border-cinzaMarrom/30 text-text-dark dark:text-white hover:bg-rosa-1/40' ?> font-poppins font-semibold transition">
            <span class="text-xl">✅</span> Ativos no Banco
        </a>
    </div>

 <!-- Painel de Sugestões da API com Accordion -->
    <?php if (isset($_GET['aba']) && $_GET['aba'] === 'sugestoes'): ?>
        <div class="card-padrao mb-8 border border-rosa-3 bg-rosa-1/40 dark:bg-preto2 p-6 rounded-2xl">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-text-dark dark:text-white flex items-center gap-2">
                        <span>🌐</span> Sugestões Externas (TheDogAPI & TheCatAPI)
                    </h3>
                    <p class="text-sm text-text-muted mt-1">Essas sugestões só são buscadas quando você clicar no botão — a página não faz nenhuma chamada externa sozinha.</p>
                </div>
                <button type="button" id="btn-buscar-sugestoes" onclick="buscarSugestoesApi()" class="btn-primario text-xs sm:text-sm whitespace-nowrap self-start sm:self-auto">
                    🔎 Buscar Sugestões da API
                </button>
            </div>

            <!-- Estado de carregamento (spinner simples) -->
            <div id="loading-sugestoes" class="hidden flex flex-col items-center justify-center gap-3 py-10">
                <div class="w-10 h-10 border-4 border-rosa-3 border-t-primary rounded-full animate-spin"></div>
                <p class="text-sm text-text-muted">Consultando TheDogAPI e TheCatAPI, aguarde...</p>
            </div>

            <div id="container-sugestoes-api" class="space-y-4"></div>
        </div>
    <?php endif; ?>

    <!-- Atalhos e Listagem Ativa -->
    <div class="card-padrao mb-8 border border-rosa-1 dark:border-preto3 bg-surface dark:bg-surface">
        <h4 class="text-text-dark dark:text-white mb-3">Atalhos de Listagem</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="<?= URL_BASE ?>/admin/especie" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-branco dark:bg-preto1 border border-cinzaMarrom/30 text-text-dark dark:text-white font-medium hover:border-primary transition">
                🐾 Listar Espécies
            </a>
            <a href="<?= URL_BASE ?>/admin/raca" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-branco dark:bg-preto1 border border-cinzaMarrom/30 text-text-dark dark:text-white font-medium hover:border-primary transition">
                🐶 Listar Raças
            </a>
        </div>
    </div>

    <!-- Grid de Listagem Ativa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($especiesComRacas)): ?>
            <?php foreach ($especiesComRacas as $item): ?>
                <div class="card-padrao border border-rosa-3/60 bg-rosa-1/60 dark:bg-preto2 shadow-sm overflow-hidden flex flex-col justify-between transform hover:-translate-y-1 hover:shadow-lg hover:bg-rosa-1 dark:hover:bg-preto3 transition-all duration-300 p-5 rounded-2xl">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-rosa-2/50 dark:border-preto3 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">🐾</span>
                                <h2 class="text-xl font-bold text-text-dark dark:text-white">
                                    <?= htmlspecialchars($item['especie']->getNome()) ?>
                                </h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="<?= URL_BASE ?>/admin/especie/editar?id=<?= $item['especie']->getId(); ?>" title="Editar Espécie" class="text-text-muted hover:text-primary dark:hover:text-roxinhoFofo transition">✏️</a>
                                <a href="<?= URL_BASE ?>/admin/especie/excluir?id=<?= $item['especie']->getId(); ?>" title="Desativar/Excluir" class="text-text-muted hover:text-rosaAlerta transition">🗑️</a>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-2">Raças Cadastradas</h4>
                            <?php if (!empty($item['racas'])): ?>
                                <ul class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                    <?php foreach ($item['racas'] as $raca): ?>
                                        <li class="flex items-center justify-between p-2 rounded-lg bg-branco/60 dark:bg-preto1 hover:bg-rosa-2/30 dark:hover:bg-preto3 transition text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full <?= $raca->isAtivo() ? 'bg-sucesso' : 'bg-text-muted' ?>"></span>
                                                <span class="text-text-dark dark:text-white font-medium"><?= htmlspecialchars($raca->getNome()) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 opacity-80 hover:opacity-100">
                                                <a href="<?= URL_BASE ?>/admin/raca/editar?id=<?= $raca->getId(); ?>" class="text-xs text-primary dark:text-roxinhoFofo hover:underline">Editar</a>
                                                <span class="text-text-muted">|</span>
                                                <a href="<?= URL_BASE ?>/admin/raca/excluir?id=<?= $raca->getId(); ?>" class="text-xs text-rosaAlerta hover:underline">Excluir</a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-text-muted italic text-xs py-4 text-center">Nenhuma raça vinculada.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-rosa-2/50 dark:border-preto3">
                        <a href="<?= URL_BASE ?>/admin/raca/cadastrar?especie_id=<?= $item['especie']->getId(); ?>" class="w-full py-2 flex items-center justify-center text-xs font-semibold text-primary dark:text-roxinhoFofo hover:bg-rosa-2/40 dark:hover:bg-preto1 rounded-xl transition">
                            + Adicionar Raça a esta Espécie
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full p-8 text-center border-2 border-dashed border-cinzaMarrom/30 rounded-2xl bg-surface dark:bg-surface">
                <span class="text-4xl block mb-2 opacity-50">🐾</span>
                <p class="text-text-muted font-poppins">Nenhuma espécie ou raça encontrada.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleAccordion(id) {
        const el = document.getElementById(id);
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    function toggleTodas(mestreCheckbox, slug) {
        const checkboxes = document.querySelectorAll('.chk-raca-' + slug);
        checkboxes.forEach(chk => {
            chk.checked = mestreCheckbox.checked;
        });
    }

    function removerRaca(idElemento, slug) {
        document.getElementById(idElemento).remove();

        const aindaVisiveis = document.querySelectorAll('.chk-raca-' + slug).length;
        if(aindaVisiveis === 0) {
            document.getElementById('master-' + slug).checked = false;
        }
    }

    function escaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function renderizarGrupoSugestao(grupo) {
        const slug = grupo.especie.toLowerCase();
        const urlBase = '<?= URL_BASE ?>';

        let itensHtml = '';
        if (grupo.racas.length > 0) {
            grupo.racas.forEach(function (racaNome, idx) {
                const nomeSeguro = escaparHtml(racaNome);
                itensHtml += `
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-background dark:bg-preto2 border border-cinzaMarrom/20" id="raca-item-${slug}-${idx}">
                        <label class="flex items-center gap-2 text-sm font-medium text-text-dark dark:text-white cursor-pointer flex-grow truncate">
                            <input type="checkbox" name="racas_aceitas[]" value="${nomeSeguro}" checked class="chk-raca-${slug} rounded text-primary focus:ring-roxinhoFofo">
                            <span class="truncate" title="${nomeSeguro}">${nomeSeguro}</span>
                        </label>
                        <button type="button" onclick="removerRaca('raca-item-${slug}-${idx}', '${slug}');" class="text-xs px-2 py-1 rounded bg-rosaAlerta/10 text-rosaAlerta hover:bg-rosaAlerta hover:text-white transition font-semibold ml-2 flex-shrink-0" title="Rejeitar raça">
                            Rejeitar
                        </button>
                    </div>`;
            });

            itensHtml = `
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 max-h-64 overflow-y-auto pr-2">${itensHtml}</div>
                <div class="pt-3 flex items-center justify-end gap-3 border-t border-cinzaMarrom/20">
                    <button type="submit" class="btn-primario text-xs sm:text-sm">✅ Aceitar Espécie e Raças Marcadas</button>
                </div>`;
        } else {
            itensHtml = '<p class="text-text-muted italic text-xs py-4 text-center">Todas as raças desta espécie já foram importadas para o banco!</p>';
        }

        return `
            <form action="${urlBase}/admin/raca/importar" method="POST" class="border border-rosa-3/60 rounded-xl bg-branco dark:bg-preto1 overflow-hidden shadow-sm">
                <input type="hidden" name="especie_nome" value="${escaparHtml(grupo.especie)}">
                <button type="button" onclick="toggleAccordion('acc-${slug}')" class="w-full flex items-center justify-between p-4 bg-rosa-1/50 dark:bg-preto3 text-text-dark dark:text-white font-bold text-left transition">
                    <span class="flex items-center gap-2 text-base">
                        <span>${grupo.icon}</span> Espécie: ${escaparHtml(grupo.especie)}
                        <span class="text-xs px-2 py-0.5 rounded-full bg-primary text-white ml-2">${grupo.racas.length} novas raças disponíveis</span>
                    </span>
                    <span class="text-sm">▼</span>
                </button>
                <div id="acc-${slug}" style="display: none;" class="p-4 space-y-3 border-t border-rosa-2/30 dark:border-preto3">
                    <div class="flex items-center justify-between bg-surface dark:bg-preto2 p-2.5 rounded-lg border border-cinzaMarrom/20 mb-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-text-dark dark:text-white cursor-pointer select-none">
                            <input type="checkbox" id="master-${slug}" checked onchange="toggleTodas(this, '${slug}')" class="rounded text-primary focus:ring-roxinhoFofo">
                            Selecionar / Deselecionar Todas
                        </label>
                    </div>
                    ${itensHtml}
                </div>
            </form>`;
    }

    async function buscarSugestoesApi() {
        const btn = document.getElementById('btn-buscar-sugestoes');
        const loading = document.getElementById('loading-sugestoes');
        const container = document.getElementById('container-sugestoes-api');

        btn.disabled = true;
        btn.classList.add('opacity-60', 'cursor-not-allowed');
        loading.classList.remove('hidden');
        container.innerHTML = '';

        try {
            const resp = await fetch('<?= URL_BASE ?>/admin/raca/sugestoes-json');
            const res = await resp.json();

            if (res.status === 'sucesso' && Array.isArray(res.sugestoes)) {
                container.innerHTML = res.sugestoes.map(renderizarGrupoSugestao).join('');
                btn.textContent = '🔄 Atualizar Sugestões';
            } else {
                container.innerHTML = '<p class="text-erro text-sm text-center py-6">' + (res.mensagem || 'Não foi possível buscar as sugestões agora.') + '</p>';
            }
        } catch (e) {
            container.innerHTML = '<p class="text-erro text-sm text-center py-6">Erro de conexão ao buscar sugestões externas.</p>';
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-60', 'cursor-not-allowed');
            loading.classList.add('hidden');
        }
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>