<?php
require_once __DIR__ . '/../templates/header.php';
$animais = $animais ?? [];
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$statusFiltro = $_GET['status'] ?? 'todos';

$statusLabels = ['disponivel' => 'Disponível', 'em_analise' => 'Em Análise', 'adotado' => 'Adotado', 'desativado' => 'Desativado'];
$statusBadge = [
    'disponivel' => 'bg-sucesso/90 text-white',
    'em_analise' => 'bg-laranja-1/90 text-white',
    'adotado'    => 'bg-primary/90 text-white',
    'desativado' => 'bg-gray-500/90 text-white',
];
?>

<div class="max-w-md lg:max-w-5xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Meus Animais</h1>
            <p class="text-sm text-text-muted mt-0.5">Gerencie a lista de animais e controle a visibilidade</p>
        </div>

        <form method="GET" action="<?= $urlBase ?>/gerenciar-animais" class="flex items-center gap-2">
            <label for="status" class="text-xs font-bold text-text-muted whitespace-nowrap">Filtrar:</label>
            <select name="status" id="status" onchange="this.form.submit()" class="input-padrao py-2 text-sm">
                <option value="todos" <?= $statusFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
                <option value="disponivel" <?= $statusFiltro === 'disponivel' ? 'selected' : '' ?>>Disponíveis</option>
                <option value="em_analise" <?= $statusFiltro === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                <option value="adotado" <?= $statusFiltro === 'adotado' ? 'selected' : '' ?>>Adotados</option>
                <option value="desativado" <?= $statusFiltro === 'desativado' ? 'selected' : '' ?>>Desativados</option>
            </select>
        </form>
    </div>

    <?php if (empty($animais)): ?>
        <div class="text-center py-16">
            <span class="text-5xl block mb-3">🐾</span>
            <p class="text-text-muted text-sm">Nenhum animal encontrado para o filtro selecionado.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($animais as $animal): ?>
                <?php
                    $status = $animal->getStatus();
                    $foto = $animal->getFotoPrincipal();
                ?>
                <div class="relative aspect-square rounded-2xl overflow-hidden shadow-md border border-rosa-2 dark:border-preto3 bg-rosa-1 dark:bg-preto2">
                    <?php if ($foto): ?>
                        <img src="<?= $urlBase ?>/<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($animal->getNome()) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-4xl">🐾</div>
                    <?php endif; ?>

                    <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded-full shadow <?= $statusBadge[$status] ?? 'bg-gray-500/90 text-white' ?>">
                        <?= htmlspecialchars($statusLabels[$status] ?? $status) ?>
                    </span>

                    <div class="absolute bottom-0 inset-x-0 bg-primary/90 dark:bg-preto1/95 backdrop-blur-sm px-3 py-2 flex items-center justify-between gap-2">
                        <span class="text-white text-sm font-bold truncate"><?= htmlspecialchars($animal->getNome()) ?></span>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="<?= $urlBase ?>/animal/editar?id=<?= $animal->getAnimalId() ?>"
                               title="Editar"
                               class="w-7 h-7 rounded-full bg-rosa-1 hover:bg-rosa-2 flex items-center justify-center text-sm transition">
                                ✏️
                            </a>

                            <?php if ($status !== 'desativado'): ?>
                                <a href="<?= $urlBase ?>/animal/excluir?id=<?= $animal->getAnimalId() ?>"
                                   title="Desativar"
                                   class="w-7 h-7 rounded-full bg-white/20 hover:bg-erro flex items-center justify-center text-xs transition">
                                    🚫
                                </a>
                            <?php else: ?>
                                <form action="<?= $urlBase ?>/animal/reativar" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $animal->getAnimalId() ?>">
                                    <button type="submit" title="Ativar" class="w-7 h-7 rounded-full bg-white/20 hover:bg-sucesso flex items-center justify-center text-xs transition">
                                        ✅
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<a href="<?= $urlBase ?>/animal/cadastrar"
   title="Cadastrar novo animal"
   class="fixed bottom-24 lg:bottom-8 right-6 z-30 w-14 h-14 rounded-full bg-rosa-2 hover:bg-rosa-1 text-white text-3xl font-bold flex items-center justify-center shadow-lg transition active:scale-95">
    +
</a>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
