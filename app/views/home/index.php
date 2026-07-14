<?php require __DIR__ . '/../templates/header.php'; ?>

<main id="conteudo-principal" class="flex-1">
    
    <section class="px-4 pt-0" aria-label="CãoNectados — Amor à primeira lambida">
        <div class="mx-auto max-w-6xl"> 
            <img src="<?= e(BASE_URL) ?>/assets/img/logo-caonectados.gif"
                 alt="CãoNectados — Amor à primeira lambida"
                 class="mx-auto w-full max-w-[900px] object-contain -mt-8 sm:-mt-12 lg:-mt-22"> 
        </div>
    </section>

    <section class="px-4  pb-8 sm:px-6 lg:px-8" aria-labelledby="titulo-sobre">
        <div class="mx-auto max-w-6xl">

            <div class="text-center">
                <h1 id="titulo-sobre"
                    class="font-shantell text-3xl font-bold text-text-dark dark:text-branco sm:text-4xl">
                    Sobre Nós
                </h1>
                <p class="mt-2 font-poppins text-sm font-semibold text-text-dark dark:text-branco/90 sm:text-base">
                    Muito mais que um match, um ato de amor.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-12">

                <article class="rounded-3xl bg-rosa-1 p-8 shadow-md dark:bg-fundoChat-escuro">
                    <h2 class="font-shantell text-2xl font-bold text-text-dark dark:text-branco">
                        Muito mais que um match, um ato de amor.
                    </h2>
                    <p class="mt-1 font-poppins text-sm font-bold text-text-muted dark:text-roxinhoFofo">
                        Adoção com propósito, do jeito certo.
                    </p>
                    <p class="mt-5 font-poppins text-base leading-relaxed text-text-dark dark:text-branco/90">
                        O CãoNectados nasceu em Foz do Iguaçu para resolver a superlotação
                        de ONGs. Conectamos pets que precisam de um lar com humanos
                        dispostos a dar muito amor, usando um sistema inteligente de
                        perfis e preferências.
                    </p>
                </article>

                <div class="hidden justify-center lg:flex">
                    <img src="<?= e(BASE_URL) ?>/assets/img/home.svg"
                         alt="Ilustração em traço de um cachorro e um gato"
                         class="w-full max-w-md dark:opacity-90 dark:invert" 
                         width="384" height="288" loading="lazy">
                </div>
            </div>

            <hr class="mt-12 border-cinzaMarrom/40 dark:border-branco/10" aria-hidden="true">
        </div>
    </section>

    <section class="px-4 pb-16 sm:px-6 lg:px-8" aria-labelledby="titulo-passos">
        
        <div class="mx-auto max-w-6xl rounded-[2.5rem] bg-marrom1 px-4 py-10 dark:bg-fundoChat-escuro sm:px-8 sm:py-14 shadow-lg dark:shadow-none">

            <div class="text-center">
                <h2 id="titulo-passos"
                    class="font-shantell text-2xl font-bold text-text-dark dark:text-branco sm:text-4xl">
                    Como encontrar seu novo melhor amigo?
                </h2>
                <p class="mt-3 font-poppins text-sm font-bold text-text-dark dark:text-branco/90 sm:text-base">
                    Um passo a passo simples, rápido e com segurança.
                </p>
            </div>

            <ol class="mt-12 grid list-none grid-cols-1 gap-6 md:grid-cols-3">

                <li>
                    <article class="flex h-full flex-col items-center rounded-2xl bg-branco p-8 text-center shadow-lg dark:bg-azulEscuro">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rosa-1 dark:bg-primary/50" aria-hidden="true">
                            <img src="<?= e(BASE_URL) ?>/assets/icons/geral/user-rosa.svg" alt="" class="h-10 w-10">
                        </span>
                        <h3 class="mt-5 font-shantell text-xl font-bold text-text-dark dark:text-branco">
                            Crie seu Perfil
                        </h3>
                        <p class="font-poppins text-xs font-semibold text-text-muted dark:text-roxinhoFofo">
                            Preferências em minutos
                        </p>
                        <p class="mt-3 font-poppins text-sm leading-snug text-text-dark dark:text-branco/80">
                            Diga se você mora em casa ou apartamento e o que busca em um pet.
                        </p>
                    </article>
                </li>

                <li>
                    <article class="flex h-full flex-col items-center rounded-2xl bg-branco p-8 text-center shadow-lg dark:bg-azulEscuro">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rosa-1 dark:bg-primary/50" aria-hidden="true">
                            <img src="<?= e(BASE_URL) ?>/assets/icons/geral/patinha-coracao.svg" alt="" class="h-10 w-10">
                        </span>
                        <h3 class="mt-5 font-shantell text-xl font-bold text-text-dark dark:text-branco">
                            Dê a Patinha
                        </h3>
                        <p class="font-poppins text-xs font-semibold text-text-muted dark:text-roxinhoFofo">
                            Match com carinho
                        </p>
                        <p class="mt-3 font-poppins text-sm leading-snug text-text-dark dark:text-branco/80">
                            Navegue pelos animais das ONGs da fronteira e dê match com os seus favoritos.
                        </p>
                    </article>
                </li>

                <li>
                    <article class="flex h-full flex-col items-center rounded-2xl bg-branco p-8 text-center shadow-lg dark:bg-azulEscuro">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rosa-1 dark:bg-primary/50" aria-hidden="true">
                            <img src="<?= e(BASE_URL) ?>/assets/icons/geral/casa-rosa.svg" alt="" class="h-10 w-10">
                        </span>
                        <h3 class="mt-5 font-shantell text-xl font-bold text-text-dark dark:text-branco">
                            Adoção Segura
                        </h3>
                        <p class="font-poppins text-xs font-semibold text-text-muted dark:text-roxinhoFofo">
                            Transparência e cuidado
                        </p>
                        <p class="mt-3 font-poppins text-sm leading-snug text-text-dark dark:text-branco/80">
                            Converse com a ONG e leve seu novo amigo para casa.
                        </p>
                    </article>
                </li>
            </ol>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../templates/footer.php'; ?>