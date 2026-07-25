<div class="mt-12 flex items-center justify-between">

    <?php if (!empty($voltar)): ?>

        <a
            href="<?= e($voltar) ?>"
            class="rounded-full border-2 border-marrom bg-transparent px-8 py-3
                   font-poppins font-bold text-marrom transition
                   hover:bg-marrom hover:text-white">

            ← Voltar

        </a>

    <?php else: ?>

        <div></div>

    <?php endif; ?>

    <button
        type="submit"
        class="rounded-full bg-rosaAlerta px-10 py-3
               font-poppins font-bold text-white shadow-lg
               transition duration-300
               hover:scale-105 hover:bg-marrom">

        <?= e($textoBotao ?? 'Próximo') ?>

    </button>

</div>