<?php require_once __DIR__ . '/../templates/header.php'; 

?>

<main class="mx-auto max-w-md p-4 sm:p-6 min-h-[80vh] flex flex-col justify-center">
    <div class="card-destaque text-center shadow-xl border border-rosa-2/60 bg-surface dark:bg-surface p-6 sm:p-8 rounded-2xl">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rosaAlerta/20 text-3xl">
            ⚠️
        </div>
        
        <h2 class="text-2xl font-bold text-text-dark dark:text-white mb-2">Excluir Espécie</h2>
        
        <p class="text-sm text-text-dark dark:text-white/80 mb-4">
            Tem certeza que deseja excluir a espécie <br>
            <strong class="text-rosaAlerta text-lg block mt-2">"<?= htmlspecialchars($especie->getNome()); ?>"</strong>?
        </p>

        <div class="p-3 mb-6 rounded-xl bg-erro/10 border border-erro text-erro dark:text-red-400 text-xs text-left">
            <strong>Atenção:</strong> Todas as raças vinculadas a esta espécie também serão excluídas!
        </div>

        <form action="<?= URL_BASE ?>/admin/especie/deletar?id=<?= $especie->getId(); ?>" method="POST" class="space-y-3">
            <button type="submit" class="btn-acao w-full hover:bg-erro transition">
                Sim, Confirmar Exclusão
            </button>
            <a href="<?= URL_BASE ?>/admin/especie" class="btn-secundario w-full text-center block bg-white dark:bg-preto2 hover:bg-cinzaMarrom/10 dark:hover:bg-preto3 text-text-dark dark:text-white border-cinzaMarrom">
                ⬅️ Cancelar
            </a>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>