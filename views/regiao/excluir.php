<?php 
require_once __DIR__ . '/../templates/header.php';
/** @var \app\models\Regiao $regiao */
?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-zinc-800 rounded-3xl shadow-xl border border-red-200 dark:border-red-900/50 p-6 md:p-8 text-center transition-colors duration-200">
        
        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">
            !
        </div>

        <h1 class="text-xl font-bold text-zinc-800 dark:text-zinc-100 mb-2">
            Excluir Região
        </h1>
        
        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-6">
            Tem certeza que deseja excluir a região <strong class="text-zinc-900 dark:text-white"><?= htmlspecialchars($regiao->getNomeRegiao()); ?></strong> (ID: <?= $regiao->getRegiaoId(); ?>)?
        </p>

        <form action="<?= URL_BASE ?>/admin/regiao/deletar?id=<?= $regiao->getRegiaoId(); ?>" method="POST" class="flex items-center justify-center space-x-4">
            <a href="<?= URL_BASE ?>/admin/regiao" 
               class="px-4 py-2 text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:underline">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow transition">
                Sim, Confirmar
            </button>
        </form>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>