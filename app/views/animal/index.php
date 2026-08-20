<?php
require_once __DIR__ . '/../templates/header.php';
$animais = $animais ?? [];
?>

<div class="min-h-screen bg-corFundo-claro dark:bg-corFundo-escuro text-perfilChats-claro dark:text-perfilChats-escuro transition-colors duration-200 p-6 md:p-10">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Cabeçalho e Botão de Ação -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-msgRespondida-claro dark:border-msgRespondida-escuro pb-5">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Animais Cadastrados</h1>
                <p class="text-sm opacity-70 mt-1">Gerencie a lista de animais, cadastre novos e controle a visibilidade.</p>
            </div>

            <a href="<?= URL_BASE ?>/animal/cadastrar"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-accent dark:bg-msgEnvia-escuro text-white hover:opacity-90 font-medium text-sm transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Cadastrar Novo Animal
            </a>
        </div>

        <!-- Filtros e Controles -->
        <div class="bg-fundoChat-claro dark:bg-fundoChat-escuro p-4 rounded-xl shadow-sm border border-msgRespondida-claro dark:border-msgRespondida-escuro flex flex-wrap items-center justify-between gap-4">
            <form method="GET" action="<?= URL_BASE ?>/animal" class="flex items-center gap-3 w-full sm:w-auto">
                <label for="status" class="text-sm font-semibold opacity-90 whitespace-nowrap">Filtrar por Status:</label>
                <select name="status" id="status" onchange="this.form.submit()"
                    class="bg-corFundo-claro dark:bg-corFundo-escuro border border-msgRespondida-claro dark:border-msgRespondida-escuro rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-msgEnvia-claro dark:focus:ring-msgEnvia-escuro">
                    <option value="todos" <?= ($_GET['status'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="disponivel" <?= ($_GET['status'] ?? '') === 'disponivel' ? 'selected' : '' ?>>Disponíveis</option>
                    <option value="em_analise" <?= ($_GET['status'] ?? '') === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                    <option value="adotado" <?= ($_GET['status'] ?? '') === 'adotado' ? 'selected' : '' ?>>Adotados</option>
                    <option value="desativado" <?= ($_GET['status'] ?? '') === 'desativado' ? 'selected' : '' ?>>Desativados</option>
                </select>
            </form>

            <span class="text-xs opacity-60">Total: <strong><?= count($animais) ?></strong> registro(s)</span>
        </div>

        <!-- Tabela Estilizada -->
        <div class="bg-fundoChat-claro dark:bg-fundoChat-escuro rounded-xl border border-msgRespondida-claro dark:border-msgRespondida-escuro shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-corFundo-claro dark:bg-corFundo-escuro border-b border-msgRespondida-claro dark:border-msgRespondida-escuro uppercase text-xs tracking-wider opacity-75">
                        <tr>
                            <th class="px-6 py-3.5">ID</th>
                            <th class="px-6 py-3.5">Foto</th>
                            <th class="px-6 py-3.5">Nome</th>
                            <th class="px-6 py-3.5">Sexo</th>
                            <th class="px-6 py-3.5">Porte</th>
                            <th class="px-6 py-3.5">Raça</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-msgRespondida-claro dark:divide-msgRespondida-escuro">
                        <?php if (!empty($animais)): ?>
                            <?php foreach ($animais as $animal): ?>
                                <tr class="hover:bg-corFundo-claro/50 dark:hover:bg-corFundo-escuro/50 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs opacity-70">
                                        #<?= $animal->getAnimalId(); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($animal->getFotoPrincipal()): ?>
                                            <img src="<?= URL_BASE ?>/<?= htmlspecialchars($animal->getFotoPrincipal()) ?>" alt="" class="w-10 h-10 object-cover rounded-lg">
                                        <?php else: ?>
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-corFundo-claro dark:bg-corFundo-escuro text-xs opacity-50">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold">
                                        <?= htmlspecialchars($animal->getNome()); ?>
                                    </td>
                                    <td class="px-6 py-4 capitalize">
                                        <?= htmlspecialchars($animal->getSexo()); ?>
                                    </td>
                                    <td class="px-6 py-4 capitalize">
                                        <?= htmlspecialchars($animal->getPorte()); ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        <?= htmlspecialchars($animal->getRacaNome() ?? 'Não informada'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $st = htmlspecialchars($animal->getStatus());
                                        $badgeClass = match ($st) {
                                            'disponivel' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                            'em_analise' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                            'adotado'    => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                            default      => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        };
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize <?= $badgeClass ?>">
                                            <?= str_replace('_', ' ', $st); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a href="<?= URL_BASE ?>/animal/editar?id=<?= $animal->getAnimalId(); ?>"
                                            class="font-medium text-msgEnvia-claro dark:text-msgEnvia-escuro hover:underline">
                                            Editar
                                        </a>

                                        <?php if ($animal->getStatus() !== 'desativado'): ?>
                                            <a href="<?= URL_BASE ?>/animal/excluir?id=<?= $animal->getAnimalId(); ?>"
                                                class="font-medium text-red-600 dark:text-red-400 hover:underline">
                                                Desativar
                                            </a>
                                        <?php else: ?>
                                            <form action="<?= URL_BASE ?>/animal/reativar" method="POST" class="inline">
                                                <input type="hidden" name="id" value="<?= $animal->getAnimalId(); ?>">
                                                <button type="submit"
                                                    class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline bg-transparent border-none p-0 cursor-pointer">
                                                    Ativar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center opacity-60">
                                    Nenhum animal encontrado para o filtro selecionado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>