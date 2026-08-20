<?php 
require_once __DIR__ . '/../templates/header.php';
/** @var \app\models\Regiao $regiao */

?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white dark:bg-zinc-800 rounded-3xl shadow-xl border border-purple-200 dark:border-zinc-700 p-6 md:p-8 transition-colors duration-200">
        
        <h1 class="text-2xl font-extrabold text-zinc-800 dark:text-zinc-100 mb-6 text-center">
            Editar Região
        </h1>

        <form action="<?= URL_BASE ?>/admin/regiao/atualizar?id=<?= $regiao->getRegiaoId(); ?>" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200 mb-1">
                    ID:
                </label>
                <input type="text" 
                       value="<?= $regiao->getRegiaoId(); ?>" 
                       disabled 
                       class="w-full px-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400">
            </div>

            <div>
                <label for="nome_regiao" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200 mb-1">
                    Nome da Região:
                </label>
                <input type="text" 
                       id="nome_regiao" 
                       name="nome_regiao" 
                       value="<?= htmlspecialchars($regiao->getNomeRegiao()); ?>"
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-zinc-900 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 focus:outline-none focus:border-purple-600 dark:focus:border-purple-400">
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="<?= URL_BASE ?>/admin/regiao"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <?= icone('arrow-left', 'h-4 w-4') ?> Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition">
                    Atualizar Região
                </button>
            </div>
        </form>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>