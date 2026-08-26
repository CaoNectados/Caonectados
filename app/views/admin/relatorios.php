<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$relatorio = $relatorio ?? [];
$filtrosAplicados = $relatorio['filtros_aplicados'] ?? ['periodo' => 'todos', 'protetor_id' => null, 'status' => null];

$periodosOpcoes = [
    'todos'     => 'Todo o período',
    '30dias'    => 'Últimos 30 dias',
    'mes_atual' => 'Mês atual',
    'ano_atual' => 'Ano atual',
];

// URL de exportação carrega os mesmos filtros que já estão aplicados na tela.
$queryExportacao = http_build_query(array_filter([
    'periodo'     => $filtrosAplicados['periodo'] !== 'todos' ? $filtrosAplicados['periodo'] : null,
    'protetor_id' => $filtrosAplicados['protetor_id'],
    'status'      => $filtrosAplicados['status'],
]));
$urlExportarCsv = $urlBase . '/admin/relatorios/exportar-csv' . ($queryExportacao ? '?' . $queryExportacao : '');
?>

<div class="space-y-8 pb-10">
    <div class="my-4 flex flex-col items-center gap-3">
        <div class="text-center">
            <h1 class="text-3xl font-bold font-shantell text-primary">Relatórios e Estatísticas</h1>
            <p class="text-xs text-text-muted mt-1">Visão consolidada da plataforma CãoNectados</p>
        </div>
        <a href="<?= $urlExportarCsv ?>" class="btn-secundario">
            ⬇️ Exportar CSV<?= $queryExportacao ? ' (com os filtros atuais)' : '' ?>
        </a>
    </div>

    <!-- FILTROS AVANÇADOS -->
    <form method="GET" action="<?= $urlBase ?>/admin/relatorios" class="card-padrao flex flex-wrap items-end gap-4">
        <div class="min-w-[180px]">
            <label class="label-padrao" for="filtro-protetor">ONG / Protetor</label>
            <select name="protetor_id" id="filtro-protetor" class="input-padrao">
                <option value="">Todas as entidades</option>
                <?php foreach ($relatorio['protetores_filtro'] as $p): ?>
                    <option value="<?= $p['protetor_id'] ?>" <?= (int) $filtrosAplicados['protetor_id'] === (int) $p['protetor_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nome_fantasia']) ?> (<?= strtoupper($p['tipo_documento']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="min-w-[160px]">
            <label class="label-padrao" for="filtro-periodo">Período</label>
            <select name="periodo" id="filtro-periodo" class="input-padrao">
                <?php foreach ($periodosOpcoes as $valor => $rotulo): ?>
                    <option value="<?= $valor ?>" <?= $filtrosAplicados['periodo'] === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="min-w-[160px]">
            <label class="label-padrao" for="filtro-status">Status do animal</label>
            <select name="status" id="filtro-status" class="input-padrao">
                <option value="">Todos os status</option>
                <?php foreach ($relatorio['status_opcoes'] as $valor => $rotulo): ?>
                    <option value="<?= $valor ?>" <?= $filtrosAplicados['status'] === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-primario h-[42px]">Aplicar filtros</button>
        <?php if (!empty($filtrosAplicados['protetor_id']) || !empty($filtrosAplicados['status']) || $filtrosAplicados['periodo'] !== 'todos'): ?>
            <a href="<?= $urlBase ?>/admin/relatorios" class="text-xs font-bold text-text-muted underline hover:text-text-dark">Limpar filtros</a>
        <?php endif; ?>
    </form>

    <!-- KPIs GLOBAIS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card-padrao text-center border-t-8 border-primary">
            <div class="my-2 text-3xl">🐾</div>
            <p class="text-3xl font-bold text-text-dark dark:text-white"><?= (int) $relatorio['kpis']['total_animais'] ?></p>
            <h3 class="text-sm font-bold text-text-muted mt-1">Animais na Plataforma</h3>
        </div>
        <div class="card-padrao text-center border-t-8 border-sucesso">
            <div class="my-2 text-3xl">❤️</div>
            <p class="text-3xl font-bold text-text-dark dark:text-white"><?= (int) $relatorio['kpis']['total_adocoes'] ?></p>
            <h3 class="text-sm font-bold text-text-muted mt-1">Adoções Realizadas</h3>
        </div>
        <div class="card-padrao text-center border-t-8 border-laranja-1">
            <div class="my-2 text-3xl">🏠</div>
            <p class="text-3xl font-bold text-text-dark dark:text-white"><?= (int) $relatorio['kpis']['total_entidades'] ?></p>
            <h3 class="text-sm font-bold text-text-muted mt-1">ONGs/Protetores Ativos</h3>
        </div>
    </div>

    <!-- RANKING POR ENTIDADE -->
    <div>
        <h2 class="text-xl font-bold font-shantell text-text-dark mb-1">Desempenho por Entidade</h2>
        <p class="text-xs text-text-muted mb-4">Quem mais cadastra e quem tem a maior taxa de adoção concluída</p>

        <?php if (!empty($relatorio['ranking'])): ?>
            <div class="relative mb-3 max-w-sm">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-text-muted">🔍</span>
                <input type="text"
                       id="buscaEntidadeRanking"
                       placeholder="Buscar por nome da ONG ou Protetor..."
                       class="input-padrao pl-10">
            </div>
        <?php endif; ?>

        <div class="card-padrao overflow-x-auto p-0">
            <?php if (empty($relatorio['ranking'])): ?>
                <p class="text-sm text-text-muted italic p-6 text-center">Nenhuma entidade validada encontrada.</p>
            <?php else: ?>
                <table class="w-full text-sm" id="tabela-ranking-entidades">
                    <thead>
                        <tr class="bg-cinzaMarrom/15 dark:bg-preto2 text-left text-xs uppercase text-text-muted">
                            <th class="px-4 py-3 font-bold">Entidade</th>
                            <th class="px-4 py-3 font-bold text-right">Cadastrados</th>
                            <th class="px-4 py-3 font-bold text-right">Adotados</th>
                            <th class="px-4 py-3 font-bold text-right">Taxa de Sucesso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cinzaMarrom/15 dark:divide-preto3">
                        <?php foreach ($relatorio['ranking'] as $linha): ?>
                            <tr class="hover:bg-rosa-1/10 dark:hover:bg-preto2 transition">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-text-dark dark:text-white"><?= htmlspecialchars($linha['nome_fantasia']) ?></span>
                                    <span class="text-xs text-text-muted ml-1">(<?= strtoupper($linha['tipo_documento']) ?>)</span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-text-dark dark:text-white"><?= $linha['total_cadastrados'] ?></td>
                                <td class="px-4 py-3 text-right font-medium text-text-dark dark:text-white"><?= $linha['total_adotados'] ?></td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold <?= $linha['taxa_sucesso'] >= 50 ? 'text-sucesso' : ($linha['taxa_sucesso'] > 0 ? 'text-laranja-1' : 'text-text-muted') ?>">
                                        <?= number_format($linha['taxa_sucesso'], 1, ',', '.') ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="buscaEntidadeVazio" class="hidden text-sm text-text-muted italic p-6 text-center">Nenhuma entidade encontrada para essa busca.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- DEMOGRAFIA GLOBAL -->
    <div>
        <h2 class="text-xl font-bold font-shantell text-text-dark mb-1">Demografia Global</h2>
        <p class="text-xs text-text-muted mb-4">Cadastrados vs. Adotados na plataforma inteira</p>

        <div class="grid md:grid-cols-2 gap-4">
            <!-- Espécies -->
            <div class="card-padrao overflow-x-auto p-0">
                <h3 class="text-sm font-bold text-text-dark dark:text-white px-4 pt-4 pb-2">Por Espécie</h3>
                <?php if (empty($relatorio['especies'])): ?>
                    <p class="text-sm text-text-muted italic p-6 text-center">Sem dados no período.</p>
                <?php else: ?>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cinzaMarrom/15 dark:bg-preto2 text-left text-xs uppercase text-text-muted">
                                <th class="px-4 py-2 font-bold">Espécie</th>
                                <th class="px-4 py-2 font-bold text-right">Cadastrados</th>
                                <th class="px-4 py-2 font-bold text-right">Adotados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cinzaMarrom/15 dark:divide-preto3">
                            <?php foreach ($relatorio['especies'] as $linha): ?>
                                <tr>
                                    <td class="px-4 py-2 text-text-dark dark:text-white"><?= htmlspecialchars($linha['rotulo']) ?></td>
                                    <td class="px-4 py-2 text-right font-medium text-text-dark dark:text-white"><?= (int) $linha['total_cadastrados'] ?></td>
                                    <td class="px-4 py-2 text-right font-medium text-sucesso"><?= (int) $linha['total_adotados'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Portes -->
            <div class="card-padrao overflow-x-auto p-0">
                <h3 class="text-sm font-bold text-text-dark dark:text-white px-4 pt-4 pb-2">Por Porte</h3>
                <?php if (empty($relatorio['portes'])): ?>
                    <p class="text-sm text-text-muted italic p-6 text-center">Sem dados no período.</p>
                <?php else: ?>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cinzaMarrom/15 dark:bg-preto2 text-left text-xs uppercase text-text-muted">
                                <th class="px-4 py-2 font-bold">Porte</th>
                                <th class="px-4 py-2 font-bold text-right">Cadastrados</th>
                                <th class="px-4 py-2 font-bold text-right">Adotados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cinzaMarrom/15 dark:divide-preto3">
                            <?php foreach ($relatorio['portes'] as $linha): ?>
                                <tr>
                                    <td class="px-4 py-2 text-text-dark dark:text-white"><?= htmlspecialchars($linha['rotulo']) ?></td>
                                    <td class="px-4 py-2 text-right font-medium text-text-dark dark:text-white"><?= (int) $linha['total_cadastrados'] ?></td>
                                    <td class="px-4 py-2 text-right font-medium text-sucesso"><?= (int) $linha['total_adotados'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('buscaEntidadeRanking')?.addEventListener('input', function (e) {
        const termo = e.target.value.toLowerCase().trim();
        const linhas = document.querySelectorAll('#tabela-ranking-entidades tbody tr');
        let algumaVisivel = false;

        linhas.forEach(function (linha) {
            const corresponde = linha.innerText.toLowerCase().includes(termo);
            linha.style.display = corresponde ? '' : 'none';
            if (corresponde) algumaVisivel = true;
        });

        const avisoVazio = document.getElementById('buscaEntidadeVazio');
        if (avisoVazio) avisoVazio.classList.toggle('hidden', algumaVisivel);
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
