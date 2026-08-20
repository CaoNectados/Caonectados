<?php require_once __DIR__ . '/../templates/header.php'; 
/** @var \app\models\Raca $raca */
$raca = $raca ?? new \app\models\Raca();
?>

<main class="mx-auto max-w-md p-4 sm:p-6 min-h-[80vh] flex flex-col justify-center">
    <div class="card-destaque text-center shadow-xl rounded-2xl border border-rosa-3 bg-rosa-1 dark:bg-preto2 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition duration-200 p-6 sm:p-8">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rosaAlerta/20 text-rosaAlerta">
            <?= icone('warning', 'h-9 w-9') ?>
        </div>

        <h2 class="text-2xl font-bold text-text-dark dark:text-white mb-2">Excluir Raça</h2>

        <p class="text-sm text-text-dark dark:text-white/80 mb-6">
            Tem certeza que deseja excluir a raça <br>
            <strong class="text-rosaAlerta text-lg block mt-2">"<?= htmlspecialchars(method_exists($raca, 'getNome') ? $raca->getNome() : ($raca['nome'] ?? '')); ?>"</strong>?
        </p>

        <form action="<?= URL_BASE ?>/admin/raca/deletar?id=<?= method_exists($raca, 'getId') ? $raca->getId() : ($raca['id'] ?? 0); ?>" method="POST" class="space-y-3">
            <button type="submit" class="btn-acao w-full hover:bg-rosa-2 hover:text-text-dark transition">
                Sim, Confirmar Exclusão
            </button>
            <a href="<?= URL_BASE ?>/admin/raca/" class="btn-secundario w-full text-center block bg-white dark:bg-preto1 hover:bg-cinzaMarrom/10 dark:hover:bg-preto3 text-text-dark dark:text-white border-cinzaMarrom transition">
                Cancelar
            </a>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>