<?php

require_once __DIR__ . '/../../templates/header.php';

$stepper = [
    'atual' => 1,
    'total' => 3,
    'titulos' => [
        'Localização',
        'Moradia',
        'Preferências'
    ]
];

$voltar = URL_BASE . '/onboarding';
$textoBotao = 'Próximo';

?>

<main class="mx-auto max-w-4xl py-14 px-4">

    <form
        action="<?= URL_BASE ?>/onboarding/adotante/etapa2"
        method="POST">

        <section
            class="relative overflow-hidden rounded-[2.5rem]
                   bg-marrom1 p-8 shadow-xl
                   dark:bg-preto2 md:p-12">

            <img
                src="<?= e(URL_BASE) ?>/assets/icons/geral/patinha-coracao.svg"
                alt=""
                class="absolute left-6 top-6 h-20 -rotate-12 opacity-10">

            <img
                src="<?= e(URL_BASE) ?>/assets/icons/geral/patinha-coracao.svg"
                alt=""
                class="absolute bottom-6 right-6 h-20 rotate-12 opacity-10">

            <?php require_once __DIR__ . '/../../templates/components/stepper.php'; ?>

            <div class="relative z-10">

                <h1
                    class="font-shantell text-4xl font-bold
                           text-text-dark dark:text-branco">

                    Onde você mora?

                </h1>

                <p
                    class="mt-3 max-w-xl
                           font-poppins text-base
                           text-text-dark/80 dark:text-branco/80">

                    Saber sua localização ajuda a encontrar animais
                    mais próximos de você e facilita o contato
                    com protetores e ONGs.

                </p>

                <fieldset class="mt-10">

                    <legend
                        class="mb-5 font-poppins text-lg font-bold
                               text-text-dark dark:text-branco">

                        Sua localização

                    </legend>

                    <label
                        for="regiao_id"
                        class="mb-2 block font-poppins font-semibold">

                        Bairro *

                    </label>

                    <select
                        name="regiao_id"
                        id="regiao_id"
                        required
                        class="w-full rounded-xl border border-cinzaMarrom
                               bg-white p-4
                               font-poppins
                               focus:border-rosaAlerta
                               focus:outline-none">

                        <option value="">
                            Selecione seu bairro
                        </option>

                        <?php foreach ($regioes as $regiao): ?>

                            <option
                                value="<?= $regiao->getRegiaoId(); ?>">

                                <?= e($regiao->getNomeRegiao()); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </fieldset>

            </div>

            <?php

            require_once __DIR__ .
                '/../../templates/components/botoes.php';

            ?>

        </section>

    </form>

</main>

<?php

require_once __DIR__ . '/../../templates/footer.php';

?>