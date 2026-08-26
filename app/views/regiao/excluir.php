<?php 
require_once __DIR__ . '/../templates/header.php';
/** @var \app\models\Regiao $regiao */
?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md bg-surface rounded-3xl shadow-xl border border-erro/30 dark:border-erro/40 p-6 md:p-8 text-center transition-colors duration-200">

        <div class="w-12 h-12 bg-erro/10 dark:bg-erro/20 text-erro rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">
            !
        </div>

        <h1 class="text-xl font-bold text-text-dark dark:text-white mb-2">
            Excluir Região
        </h1>

        <p class="text-sm text-text-muted mb-6">
            Tem certeza que deseja excluir a região <strong class="text-text-dark dark:text-white"><?= htmlspecialchars($regiao->getNomeRegiao()); ?></strong> (ID: <?= $regiao->getRegiaoId(); ?>)?
        </p>

        <form action="<?= URL_BASE ?>/admin/regiao/deletar?id=<?= $regiao->getRegiaoId(); ?>" method="POST" class="flex items-center justify-center space-x-4">
            <a href="<?= URL_BASE ?>/admin/regiao"
               class="px-4 py-2 text-sm font-semibold text-text-muted hover:underline">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 bg-erro hover:opacity-90 text-white text-sm font-bold rounded-xl shadow transition">
                Sim, Confirmar
            </button>
        </form>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>