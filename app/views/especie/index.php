<?php require_once __DIR__ . '/../templates/header.php'; ?>

<main class="mx-auto max-w-figma p-4 sm:p-6 lg:p-8 min-h-screen">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-text-dark dark:text-white flex items-center gap-2">
                <span class="hidden sm:inline"><?= icone('paw', 'h-8 w-8') ?></span> Espécies Cadastradas
            </h1>
            <p class="text-sm text-text-muted mt-1">Gerencie as categorias raiz do sistema para cruzamento de raças.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="<?= URL_BASE ?>/admin/especie/cadastrar" class="btn-primario text-xs sm:text-sm whitespace-nowrap">
                + Nova Espécie
            </a>
            <a href="<?= URL_BASE ?>/admin/raca" class="btn-secundario text-xs sm:text-sm whitespace-nowrap">
                Ir para Raças
            </a>
            
        </div>
        
    </div>
    <!-- Botão de Acesso Rápido / Alternância entre Espécies e Raças -->
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= URL_BASE ?>/admin/gerenciar-especies-racas" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface dark:bg-preto2 border border-rosa-2/60 text-text-dark dark:text-white text-xs sm:text-sm font-semibold shadow-sm hover:bg-rosa-1/50 dark:hover:bg-preto3 transition">
            <?= icone('chart', 'h-5 w-5') ?> Gerenciar Espécies e Raças (Dashboard)
        </a>
    </div>
    <!-- Filtros de Status e Busca -->
    <div class="card-padrao mb-6 border border-rosa-1 dark:border-preto3 p-4 bg-surface dark:bg-surface">
        <form method="GET" action="<?= URL_BASE ?>/admin/especie" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label for="status" class="text-sm font-semibold text-text-dark dark:text-white whitespace-nowrap">Filtrar por Status:</label>
                <select name="status" id="status" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-sm bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="todos" <?= ($_GET['status'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="ativos" <?= ($_GET['status'] ?? '') === 'ativos' ? 'selected' : '' ?>>Ativos</option>
                    <option value="inativos" <?= ($_GET['status'] ?? '') === 'inativos' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>

            <!-- Busca instantânea -->
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-text-muted"><?= icone('search', 'h-4 w-4') ?></span>
                <input type="text" id="busca-especie" placeholder="Pesquisar espécie..." class="input-padrao pl-10 py-1.5 text-sm w-full bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40" onkeyup="filtrarEspecies()">
            </div>
        </form>
    </div>

    <!-- Informativo "Atenção" -->
    <div class="mb-8 p-4 rounded-xl border-l-4 border-aviso bg-amarelo/20 text-text-dark dark:text-white">
        <h4 class="font-poppins font-bold text-sm mb-1 flex items-center gap-2">
            <span class="bg-aviso text-white text-[10px] px-2 py-0.5 rounded shadow-sm inline-flex items-center gap-1"><?= icone('warning', 'h-3 w-3') ?> Importante</span>
            Atenção
        </h4>
        <p class="text-xs">As Espécies aprovadas/ativas ficarão visíveis para os tutores nos filtros de busca e cadastro de pets imediatamente.</p>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-lg font-poppins font-bold text-text-dark dark:text-white">
            Resultados (<span id="count-resultados"><?= count($especies ?? []) ?></span>)
        </h2>
        <a href="<?= URL_BASE ?>/admin/especie" class="text-xs font-semibold text-primary dark:text-roxinhoFofo hover:underline">Limpar Filtros</a>
    </div>

    <!-- Grid de Cards de Espécies com efeito Hover e Tom Rosa Escuro -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="grid-especies">
        <?php if (!empty($especies)): ?>
            <?php foreach ($especies as $e): ?>
                <?php 
                    $nome = strtolower($e->getNome());
                    if (strpos($nome, 'cão') !== false || strpos($nome, 'cachorro') !== false || strpos($nome, 'canina') !== false) {
                        $badgeBg = 'bg-amarelo/25 dark:bg-amarelo/30 text-laranja-2 dark:text-amarelo border-amarelo/40';
                        $iconEsp = icone('paw', 'h-4 w-4');
                    } elseif (strpos($nome, 'gato') !== false || strpos($nome, 'felin') !== false) {
                        $badgeBg = 'bg-azul/25 dark:bg-azul/30 text-azulEscuro dark:text-azul border-azul/40';
                        $iconEsp = icone('paw', 'h-4 w-4');
                    } else {
                        $badgeBg = 'bg-roxinhoFofo/25 dark:bg-roxinhoFofo/30 text-primary dark:text-roxinhoFofo border-roxinhoFofo/40';
                        $iconEsp = icone('paw', 'h-4 w-4');
                    }
                ?>
                <div class="card-especie-item rounded-2xl border border-rosa-3/60 bg-rosa-1/60 dark:bg-preto2 shadow-sm overflow-hidden flex flex-col justify-between transform hover:-translate-y-1 hover:shadow-lg hover:bg-rosa-1 dark:hover:bg-preto3 transition-all duration-300 p-5">
                    <div>
                        
                        
                        <h3 class="card-nome-especie text-lg font-bold text-text-dark dark:text-white mb-2 leading-snug">
                            <?= htmlspecialchars($e->getNome()); ?>
                        </h3>

                        <p class="text-xs font-semibold mb-4 <?= $e->isAtivo() ? 'text-sucesso' : 'text-rosaAlerta' ?>">
                            ● Status: <?= $e->isAtivo() ? 'Ativo' : 'Inativo'; ?>
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-3 border-t border-cinzaMarrom/20 mt-2">
                        <a href="<?= URL_BASE ?>/admin/especie/editar?id=<?= $e->getId(); ?>" class="flex-grow flex items-center justify-center gap-1.5 py-2 rounded-xl bg-amarelo/20 dark:bg-amarelo/20 text-text-dark dark:text-white font-bold text-xs hover:bg-amarelo/40 transition shadow-sm border border-amarelo/30">
                            <?= icone('pencil', 'h-4 w-4') ?> Editar
                        </a>

                        <?php if ($e->isAtivo()): ?>
                            <a href="<?= URL_BASE ?>/admin/especie/excluir?id=<?= $e->getId(); ?>" class="flex h-9 px-3 items-center justify-center rounded-xl bg-rosaAlerta/10 text-rosaAlerta font-bold text-xs hover:bg-rosaAlerta hover:text-white transition shadow-sm" title="Desativar">
                                Desativar
                            </a>
                        <?php else: ?>
                            <a href="<?= URL_BASE ?>/admin/especie/reativar?id=<?= $e->getId(); ?>" class="flex h-9 px-3 items-center justify-center rounded-xl bg-sucesso/10 text-sucesso font-bold text-xs hover:bg-sucesso hover:text-white transition shadow-sm" title="Ativar">
                                Ativar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full p-10 text-center border-2 border-dashed border-cinzaMarrom/30 rounded-2xl bg-surface dark:bg-surface">
                <span class="flex justify-center mb-2 opacity-50"><?= icone('paw', 'h-10 w-10') ?></span>
                <p class="text-text-muted font-poppins">Nenhuma espécie encontrada.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    function filtrarEspecies() {
        const termo = document.getElementById('busca-especie').value.toLowerCase();
        const cards = document.querySelectorAll('.card-especie-item');
        let visiveis = 0;
        
        cards.forEach(card => {
            const nome = card.querySelector('.card-nome-especie').textContent.toLowerCase();
            if (nome.includes(termo)) {
                card.style.display = 'flex';
                visiveis++;
            } else {
                card.style.display = 'none';
            }
        });

        const countEl = document.getElementById('count-resultados');
        if (countEl) countEl.textContent = visiveis;
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>