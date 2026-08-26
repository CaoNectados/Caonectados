<?php 
require_once __DIR__ . '/../templates/header.php';
/** @var \app\models\Regiao $regiao */

?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg bg-surface rounded-3xl shadow-xl border border-rosa-2 dark:border-preto3 p-6 md:p-8 transition-colors duration-200">

        <h1 class="text-2xl font-extrabold text-text-dark dark:text-white mb-6 text-center">
            Editar Região
        </h1>

        <form action="<?= URL_BASE ?>/admin/regiao/atualizar?id=<?= $regiao->getRegiaoId(); ?>" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-text-dark dark:text-white mb-1">
                    ID:
                </label>
                <input type="text"
                       value="<?= $regiao->getRegiaoId(); ?>"
                       disabled
                       class="w-full px-4 py-2 rounded-xl border border-cinzaMarrom/40 dark:border-preto3 bg-cinzaMarrom/10 dark:bg-preto2 text-text-muted">
            </div>

            <div>
                <label for="nome_regiao" class="block text-sm font-semibold text-text-dark dark:text-white mb-1">
                    Nome da Região:
                </label>
                <input type="text"
                       id="nome_regiao"
                       name="nome_regiao"
                       value="<?= htmlspecialchars($regiao->getNomeRegiao()); ?>"
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-preto1 dark:border-white bg-branco dark:bg-preto1 text-text-dark dark:text-white focus:outline-none focus:border-primary">
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="<?= URL_BASE ?>/admin/regiao"
                   class="text-sm font-medium text-text-muted hover:text-text-dark dark:hover:text-white">
                    ← Cancelar
                </a>
                <button type="submit" class="btn-primario">
                    Atualizar Região
                </button>
            </div>
        </form>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>