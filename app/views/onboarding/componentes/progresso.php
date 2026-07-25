<?php

$atual = $progresso['atual'] ?? 1;
$total = $progresso['total'] ?? 3;
$titulos = $progresso['titulos'] ?? [];

?>

<div class="mb-10">

    <div class="mb-6 text-center">

        <h2 class="font-shantell text-3xl font-bold text-text-dark dark:text-branco">
            Complete seu cadastro
        </h2>

        <p class="mt-2 font-poppins text-sm font-medium text-text-dark/70 dark:text-branco/70">
            Etapa <?= $atual ?> de <?= $total ?>
        </p>

    </div>

    <div class="flex items-center">

        <?php foreach ($titulos as $i => $titulo):

            $numero = $i + 1;

            $ativo = $numero <= $atual;
        ?>

            <div class="flex flex-1 items-center">

                <div
                    class="<?= $ativo
                        ? 'bg-rosaAlerta text-white'
                        : 'bg-cinzaMarrom text-text-dark'; ?>

                    flex h-12 w-12 items-center justify-center rounded-full
                    font-poppins text-sm font-bold shadow transition-all">

                    <?= $numero ?>

                </div>

                <?php if ($numero < $total): ?>

                    <div
                        class="<?= ($numero < $atual)
                            ? 'bg-rosaAlerta'
                            : 'bg-cinzaMarrom'; ?>

                        mx-3 h-1 flex-1 rounded-full">
                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="mt-4 flex justify-between">

        <?php foreach ($titulos as $titulo): ?>

            <span
                class="w-full text-center font-poppins text-xs font-semibold text-text-dark dark:text-branco">

                <?= e($titulo) ?>

            </span>

        <?php endforeach; ?>

    </div>

</div>