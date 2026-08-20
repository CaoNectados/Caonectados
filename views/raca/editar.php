<?php require_once __DIR__ . '/../templates/header.php'; 

/** @var \app\models\Raca $especies */
?>

<main class="mx-auto max-w-lg p-4 sm:p-6 min-h-[80vh] flex flex-col justify-center">
    <div class="card-padrao border border-rosa-3 shadow-xl rounded-2xl bg-rosa-1 dark:bg-preto2 p-6 sm:p-8">
        <h1 class="mb-2 text-text-dark dark:text-white flex items-center gap-2">
            <?= icone('pencil', 'h-8 w-8') ?> Editar Raça
        </h1>
        <p class="text-sm text-text-muted mb-6">Atualize os dados da raça selecionada.</p>

        <form action="<?= URL_BASE ?>/admin/raca/atualizar?id=<?= $raca->getId(); ?>" method="POST" autocomplete="off" class="space-y-5">
          
            <div>
                <label for="especie_id" class="label-padrao">Espécie Pertencente <span class="text-rosaAlerta">*</span></label>
                <select id="especie_id" name="especie_id" class="input-padrao bg-branco dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <?php foreach ($especies as $e): ?>
                        <option value="<?= $e->getId(); ?>" <?= $e->getId() === $raca->getEspecieId() ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($e->getNome()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="nome" class="label-padrao">Nome da Raça <span class="text-rosaAlerta">*</span></label>
                <input autocomplete="nope" type="text" id="nome" name="nome" value="<?= htmlspecialchars($raca->getNome()); ?>" class="input-padrao bg-branco dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom/40">
            </div>

            <div class="pt-6 flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primario w-full hover:opacity-90 transition">
                    Atualizar Raça
                </button>
                <a href="<?= URL_BASE ?>/admin/raca" class="btn-secundario w-full text-center bg-white dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom hover:bg-cinzaMarrom/10 transition">
                    Voltar
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>