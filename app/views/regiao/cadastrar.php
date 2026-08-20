<?php 
require_once __DIR__ . '/../templates/header.php';
?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white dark:bg-zinc-800 rounded-3xl shadow-xl border border-purple-200 dark:border-zinc-700 p-6 md:p-8 transition-colors duration-200">
        
        <h1 class="text-2xl font-extrabold text-zinc-800 dark:text-zinc-100 mb-6 text-center">
            Cadastrar Nova Região
        </h1>

        <form action="<?= URL_BASE ?>/admin/regiao/salvar" method="POST" class="space-y-5">
            <div>
                <label for="nome_regiao" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200 mb-2">
                    Nome da Região:
                </label>
                <input type="text" 
                       id="nome_regiao" 
                       name="nome_regiao"
                       placeholder="Ex: Polo Centro, Três Lagoas..."
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-zinc-900 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 focus:outline-none focus:border-purple-600 dark:focus:border-purple-400">
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="<?= URL_BASE ?>/admin/regiao" 
                   class="text-sm font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    ← Voltar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition">
                    Salvar Região
                </button>
            </div>
        </form>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>