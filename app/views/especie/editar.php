<?php require_once __DIR__ . '/../templates/header.php'; 
/** @var \app\models\Especie $especie */
$especie = $especie ?? new \app\models\Especie();
?>

<main class="mx-auto max-w-lg p-4 sm:p-6 min-h-[80vh] flex flex-col justify-center">
    <div class="card-padrao border border-rosa-3 shadow-xl rounded-2xl bg-rosa-1 dark:bg-preto2 p-6 sm:p-8">
        <h1 class="mb-2 text-text-dark dark:text-white flex items-center gap-2">
            <span class="text-3xl">✏️</span> Editar Espécie
        </h1>
        <p class="text-sm text-text-muted mb-6">Altere os dados da espécie selecionada.</p>

        <form action="<?= URL_BASE ?>/admin/especie/atualizar?id=<?= $especie->getId(); ?>" method="POST" class="space-y-5">
            <div>
                <label class="label-padrao opacity-60">ID</label>
                <input type="text" value="<?= $especie->getId(); ?>" class="input-padrao opacity-60 cursor-not-allowed bg-cinzaMarrom/10 dark:bg-preto3 text-text-dark dark:text-white border-cinzaMarrom/40" disabled>
            </div>

            <div>
                <label for="nome" class="label-padrao">Nome da Espécie <span class="text-rosaAlerta">*</span></label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($especie->getNome()); ?>" class="input-padrao bg-branco dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom/40">
            </div>

            <div class="pt-6 flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primario w-full hover:opacity-90 transition">
                    Atualizar
                </button>
                <a href="<?= URL_BASE ?>/admin/especie" class="btn-secundario w-full text-center bg-white dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom hover:bg-cinzaMarrom/10 transition">
                    Voltar
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>