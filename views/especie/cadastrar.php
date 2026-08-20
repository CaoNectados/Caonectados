<?php require_once __DIR__ . '/../templates/header.php'; ?>

<main class="mx-auto max-w-lg p-4 sm:p-6 min-h-[80vh] flex flex-col justify-center">
    <div class="card-padrao border border-rosa-3 shadow-xl rounded-2xl bg-rosa-1 dark:bg-preto2 p-6 sm:p-8">
        <h1 class="mb-2 text-text-dark dark:text-white flex items-center gap-2">
            <?= icone('paw', 'h-8 w-8') ?> Cadastrar Nova Espécie
        </h1>
        <p class="text-sm text-text-muted mb-6">Insira o nome da nova categoria raiz de animais.</p>

        <form action="<?= URL_BASE ?>/admin/especie/salvar" method="POST" class="space-y-5">
            <div>
                <label for="nome" class="label-padrao">Nome da Espécie <span class="text-rosaAlerta">*</span></label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Cão, Gato..." class="input-padrao bg-branco dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom/40">
            </div>

            <div class="pt-6 flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primario w-full hover:opacity-90 transition">
                    Salvar
                </button>
                <a href="<?= URL_BASE ?>/admin/especie" class="btn-secundario w-full text-center bg-white dark:bg-preto1 text-text-dark dark:text-white border-cinzaMarrom hover:bg-cinzaMarrom/10 transition">
                    Voltar
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>