<?php require_once __DIR__ . '/../templates/header.php'; ?>

<main class="mx-auto max-w-figma p-4 sm:p-6 lg:p-8 min-h-screen">
    <!-- Cabeçalho -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-text-dark dark:text-white flex items-center gap-2">
                <?= renderIconeMenu('patinha.svg', '', 'hidden h-8 w-8 dark:h-10 dark:w-10 dark:brightness-0 dark:invert sm:inline-block') ?> Raças Cadastradas
            </h1>
            <p class="text-sm text-text-muted mt-1">Gerencie, aprove ou desative as raças do catálogo.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= URL_BASE ?>/admin/raca/cadastrar" class="btn-primario text-xs sm:text-sm whitespace-nowrap">
                + Cadastrar Nova Raça
            </a>
            <a href="<?= URL_BASE ?>/admin/especie" class="btn-secundario text-xs sm:text-sm whitespace-nowrap dark:text-white">
                Ir para Espécies
            </a>
        </div>
    </div>
<!-- Botão de Acesso Rápido / Alternância entre Espécies e Raças -->
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= URL_BASE ?>/admin/gerenciar-especies-racas" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface dark:bg-preto2 border border-rosa-2/60 text-text-dark dark:text-white text-xs sm:text-sm font-semibold shadow-sm hover:bg-rosa-1/50 dark:hover:bg-preto3 transition">
            <?= renderIconeMenu('especies.svg', '', 'h-5 w-5 text-primary dark:text-white dark:brightness-0 dark:invert') ?> Gerenciar Espécies e Raças (Dashboard)
        </a>
    </div>
    <!-- Filtros de Status e Busca -->
    <div class="card-padrao mb-6 border border-rosa-1 dark:border-preto3 p-4 bg-surface dark:bg-surface">
        <form method="GET" action="<?= URL_BASE ?>/admin/raca/" class="flex flex-col sm:flex-row items-center justify-between gap-4">
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
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-text-dark dark:text-white" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                </span>
                <input type="text" id="busca-raca" placeholder="Pesquisar raça..." class="input-padrao pl-10 py-1.5 text-sm w-full bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40" onkeyup="filtrarRaças()">
            </div>
        </form>
    </div>

    <!-- Grid de Cards de Raças com efeito Hover e Tom Rosa Escuro -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="grid-racas">
        <?php if (!empty($racas)): ?>
            <?php foreach ($racas as $r): ?>
                <?php 
                    $especieNomeObj = $r->getEspecie() ? $r->getEspecie()->getNome() : 'Geral';
                    $nomeLower = strtolower($especieNomeObj);

                    if (strpos($nomeLower, 'cão') !== false || strpos($nomeLower, 'cachorro') !== false || strpos($nomeLower, 'canina') !== false) {
                        $badgeBg = 'bg-amarelo/20 dark:bg-amarelo/30 text-laranja-2 dark:text-amarelo border-amarelo/40';
                    } elseif (strpos($nomeLower, 'gato') !== false || strpos($nomeLower, 'felino') !== false) {
                        $badgeBg = 'bg-azul/20 dark:bg-azul/30 text-azulEscuro dark:text-azul border-azul/40';
                    } else {
                        $badgeBg = 'bg-roxinhoFofo/20 dark:bg-roxinhoFofo/30 text-primary dark:text-roxinhoFofo border-roxinhoFofo/40';
                    }
                ?>
                <div class="card-raca-item rounded-2xl border border-rosa-3/60 bg-rosa-1/60 dark:border-rosa-4/50 dark:bg-preto1 shadow-sm dark:shadow-[0_12px_28px_rgba(0,0,0,0.55)] overflow-hidden flex flex-col justify-between transform hover:-translate-y-1 hover:shadow-lg hover:bg-rosa-1 dark:hover:bg-preto2 transition-all duration-300 p-5">
                    <div>
                        <!-- Cabeçalho do Card -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $badgeBg ?>">
                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="6.8" cy="7.2" r="2.1"/><circle cx="11.4" cy="5.2" r="2.1"/><circle cx="16.2" cy="6.2" r="2.1"/><circle cx="19.2" cy="10" r="1.9"/><path d="M12.4 10.2c-2.4 0-6.2 4.4-6.2 7.5 0 2 1.5 3.1 3.4 2.6 1.8-.5 3.5-.5 5.3 0 1.9.5 3.4-.6 3.4-2.6 0-3.1-3.6-7.5-5.9-7.5Z"/></svg> <?= htmlspecialchars($especieNomeObj); ?>
                            </span>
                            <span class="text-[11px] font-mono text-text-muted">ID #<?= $r->getId(); ?></span>
                        </div>
                        
                        <!-- Nome da Raça -->
                        <h3 class="card-nome-raca text-lg font-bold text-text-dark dark:text-white mb-2 leading-snug">
                            <?= htmlspecialchars($r->getNome()); ?>
                        </h3>

                        <!-- Status -->
                        <p class="text-xs font-semibold mb-4 <?= $r->isAtivo() ? 'text-sucesso' : 'text-rosaAlerta' ?>">
                            ● Status: <?= $r->isAtivo() ? 'Ativo' : 'Inativo'; ?>
                        </p>
                    </div>

                    <!-- Ações Inferiores -->
                    <div class="flex items-center gap-2 pt-3 border-t border-cinzaMarrom/20 mt-2">
                        <a href="<?= URL_BASE ?>/admin/raca/editar?id=<?= $r->getId(); ?>" class="flex-grow flex items-center justify-center gap-1.5 py-2 rounded-xl bg-amarelo/20 dark:bg-amarelo/20 text-text-dark dark:text-white font-bold text-xs hover:bg-amarelo/40 transition shadow-sm border border-amarelo/30">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5 4 4M4 20l4-.8L19.3 7.9a2.1 2.1 0 0 0-3-3L5 16.2 4 20Z"/></svg> Editar
                        </a>

                        <?php if ($r->isAtivo()): ?>
                            <a href="<?= URL_BASE ?>/admin/raca/excluir?id=<?= $r->getId(); ?>" class="flex h-9 px-3 items-center justify-center gap-1.5 rounded-xl bg-rosaAlerta/10 text-rosaAlerta font-bold text-xs hover:bg-rosaAlerta hover:text-white transition shadow-sm" title="Desativar">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/></svg>
                                Desativar
                            </a>
                        <?php else: ?>
                            <form action="<?= URL_BASE ?>/admin/raca/reativar" method="post">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id" value="<?= (int)$r->getId(); ?>">
                                <button type="submit" class="flex h-9 px-3 items-center justify-center rounded-xl bg-sucesso/10 text-sucesso font-bold text-xs hover:bg-sucesso hover:text-white transition shadow-sm" title="Ativar">
                                    Ativar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full p-10 text-center border-2 border-dashed border-cinzaMarrom/30 rounded-2xl bg-surface">
                <div class="mb-2 flex justify-center text-primary dark:text-roxinhoFofo"><?= renderIconeMenu('patinha.svg', '', 'h-10 w-10 dark:h-12 dark:w-12 dark:brightness-0 dark:invert') ?></div>
                <p class="text-text-muted font-poppins">Nenhuma raça encontrada.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    function filtrarRaças() {
        const termo = document.getElementById('busca-raca').value.toLowerCase();
        const cards = document.querySelectorAll('.card-raca-item');
        
        cards.forEach(card => {
            const nome = card.querySelector('.card-nome-raca').textContent.toLowerCase();
            if (nome.includes(termo)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
