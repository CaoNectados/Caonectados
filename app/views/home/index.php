<?php 
require_once __DIR__ . '/../templates/header.php'; 
?>

<section class="px-4 " aria-label="CãoNectados — Amor à primeira lambida">
    <div class="mx-auto max-w-6xl">
        <img src="<?= e(asset('assets/img/logo-caonectados.gif')) ?>"
             alt="CãoNectados — Amor à primeira lambida"
             class="mx-auto w-full max-w-[700px] object-contain -my-4 sm:-my-8 lg:-my-16 dark:drop-shadow-[0_0_3px_rgba(255,255,255,0.9)]">
    </div>
</section>

<section class="relative z-10 px-4 pb-8 sm:px-6 lg:px-8" aria-labelledby="titulo-sobre">
    <div class="mx-auto max-w-6xl">

        <div class="text-center">
            <h1 id="titulo-sobre"
                class="font-shantell text-3xl font-bold text-text-dark dark:text-white sm:text-4xl">
                Sobre Nós
            </h1>
            <p class="mt-2 font-poppins text-sm font-semibold text-text-dark dark:text-white/90 sm:text-base">
                Muito mais que um match, um ato de amor.
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-12">
    
            <article class="card-destaque">
                <h2 class="font-shantell text-2xl font-bold text-text-dark">
                    Muito mais que um match, um ato de amor.
                </h2>
                <p class="mt-1 font-poppins text-sm font-bold text-text-muted dark:text-text-dark/70">
                    Adoção com propósito, do jeito certo.
                </p>
                <p class="mt-5 font-poppins text-base leading-relaxed text-text-dark">
                    O CãoNectados nasceu em Foz do Iguaçu para resolver a superlotação
                    de ONGs. Conectamos pets que precisam de um lar com humanos
                    dispostos a dar muito amor, usando um sistema inteligente de
                    perfis e preferências.
                </p>
            </article>

            <div class="hidden justify-center lg:flex">
                <img src="<?= e(URL_BASE) ?>/assets/img/home.svg"
                     alt="Ilustração em traço de um cachorro e um gato"
                     class="w-full max-w-md dark:opacity-90 dark:invert"
                     width="384" height="288" loading="lazy">
            </div>
        </div>

        <hr class="mt-12 border-cinzaMarrom/40 dark:border-branco/10" aria-hidden="true">
    </div>
</section>

<section class="px-4 pb-12 sm:px-6 lg:px-8" aria-labelledby="titulo-passos">

    <div class="mx-auto max-w-6xl rounded-[2.5rem] border border-rosa-3 bg-rosa-3/55 px-4 py-10 shadow-xl transition-all duration-300 hover:border-rosa-4 hover:shadow-2xl dark:border-rosa-1 dark:bg-rosa-1/70 sm:px-8 sm:py-14 motion-reduce:transition-none">

        <div class="text-center">
            <h2 id="titulo-passos" class="font-shantell text-2xl font-bold text-text-dark dark:text-white sm:text-4xl">
                Como encontrar seu novo melhor amigo?
            </h2>
            <p class="mt-3 font-poppins text-sm font-bold text-text-dark dark:text-white/80 sm:text-base">
                Um passo a passo simples, rápido e com segurança.
            </p>
        </div>

        <ol class="mt-12 grid list-none grid-cols-1 gap-6 md:grid-cols-3 ">

            <li>
                <article class="group flex h-full flex-col items-center rounded-[2rem] border border-transparent bg-branco p-8 text-center shadow-[0_8px_20px_rgba(0,0,0,0.12)] transition-all duration-300 hover:-translate-y-2 hover:border-rosa-3 hover:shadow-2xl dark:bg-roxo1/25 dark:hover:border-roxo1 motion-reduce:transition-none motion-reduce:hover:transform-none">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rosa-1 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 dark:bg-white dark:ring-2 dark:ring-rosa-1 motion-reduce:transition-none" aria-hidden="true">
                        <img src="<?= e(URL_BASE) ?>/assets/icons/geral/user-rosa.svg" alt="" class="h-14 w-14 transition-transform duration-300 group-hover:scale-105 motion-reduce:transition-none">
                    </span>
                    <h3 class="mt-5 font-shantell text-xl font-bold text-text-dark dark:text-white ">
                        Crie seu Perfil
                    </h3>
                    <p class="font-poppins text-xs font-semibold text-text-muted dark:text-white/60">
                        Preferências em minutos
                    </p>
                    <p class="mt-3 font-poppins text-sm leading-snug text-text-dark dark:text-white/80">
                        Diga se você mora em casa ou apartamento e o que busca em um pet.
                    </p>
                </article>
            </li>

            <li>
                <article class="group flex h-full flex-col items-center rounded-[2rem] border border-transparent bg-branco p-8 text-center shadow-[0_8px_20px_rgba(0,0,0,0.12)] transition-all duration-300 hover:-translate-y-2 hover:border-rosa-3 hover:shadow-2xl dark:bg-roxo1/25 dark:hover:border-roxo1 motion-reduce:transition-none motion-reduce:hover:transform-none">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rosa-1 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 dark:bg-white dark:ring-2 dark:ring-rosa-1 motion-reduce:transition-none" aria-hidden="true">
                        <img src="<?= e(URL_BASE) ?>/assets/icons/geral/patinha-coracao.svg" alt="" class="h-14 w-14 transition-transform duration-300 group-hover:scale-105 motion-reduce:transition-none">
                    </span>
                    <h3 class="mt-5 font-shantell text-xl font-bold text-text-dark dark:text-white">
                        Dê a Patinha
                    </h3>
                    <p class="font-poppins text-xs font-semibold text-text-muted dark:text-white/60">
                        Match com carinho
                    </p>
                    <p class="mt-3 font-poppins text-sm leading-snug text-text-dark dark:text-white/80">
                        Navegue pelos animais das ONGs da fronteira e dê match com os seus favoritos.
                    </p>
                </article>
            </li>

            <li>
                <article class="group flex h-full flex-col items-center rounded-[2rem] border border-transparent bg-branco p-8 text-center shadow-[0_8px_20px_rgba(0,0,0,0.12)] transition-all duration-300 hover:-translate-y-2 hover:border-rosa-3 hover:shadow-2xl dark:bg-roxo1/25 dark:hover:border-roxo1 motion-reduce:transition-none motion-reduce:hover:transform-none">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rosa-1 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 dark:bg-white dark:ring-2 dark:ring-rosa-1 motion-reduce:transition-none" aria-hidden="true">
                        <img src="<?= e(URL_BASE) ?>/assets/icons/geral/casa-rosa.svg" alt="" class="h-14 w-14 transition-transform duration-300 group-hover:scale-105 motion-reduce:transition-none">
                    </span>
                    <h3 class="mt-5 font-shantell text-xl font-bold text-text-dark dark:text-white">
                        Adoção Segura
                    </h3>
                    <p class="font-poppins text-xs font-semibold text-text-muted dark:text-white/60">
                        Transparência e cuidado
                    </p>
                    <p class="mt-3 font-poppins text-sm leading-snug text-text-dark dark:text-white/80">
                        Converse com a ONG e leve seu novo amigo para casa.
                    </p>
                </article>
            </li>
        </ol>
    </div>
</section>

<section class="px-4 pb-16 sm:px-6 lg:px-8" aria-labelledby="titulo-instagram">
    <div class="relative mx-auto flex max-w-4xl flex-col items-center overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-rosa-1 to-rosa-3 px-6 py-12 text-center shadow-lg dark:from-primary dark:to-roxo1 sm:px-12 sm:py-16">
        
        <img src="<?= e(URL_BASE) ?>/assets/icons/geral/patinha-coracao.svg" alt="" class="absolute -left-6 -top-6 h-24 w-24 opacity-40 rotate-[-15deg] transition-transform duration-500 hover:scale-110 dark:opacity-80 motion-reduce:transition-none">
        <img src="<?= e(URL_BASE) ?>/assets/icons/geral/patinha-coracao.svg" alt="" class="absolute -bottom-8 -right-8 h-32 w-32 opacity-40 rotate-[15deg] transition-transform duration-500 hover:scale-110 dark:opacity-80 motion-reduce:transition-none">

        <h2 id="titulo-instagram" class="relative z-10 font-shantell text-3xl font-bold text-text-dark dark:text-white sm:text-4xl">
            Acompanhe nosso Projeto!
        </h2>
        
        <p class="relative z-10 mt-4 max-w-2xl font-poppins text-base font-medium text-text-dark/80 dark:text-white/80 sm:text-lg">
            Siga-nos no Instagram para ficar por dentro de tudo, ver histórias com finais felizes e apoiar nossa causa.
        </p>

        <a href="https://www.instagram.com/caonectados2026/" target="_blank" rel="noopener noreferrer"
           class="relative z-10 mt-8 flex items-center gap-3 rounded-full bg-rosaAlerta px-8 py-4 font-poppins text-lg font-bold text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:scale-105 hover:bg-marrom hover:shadow-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/70 active:scale-95 dark:hover:bg-rosa-2 dark:hover:text-text-dark motion-reduce:transition-none motion-reduce:hover:transform-none">
            
            <img src="<?= e(URL_BASE) ?>/assets/icons/social/instagram.svg" alt="Logotipo do Instagram" class="h-7 w-7 brightness-0 invert">
            
            Seguir @caonectados2026
        </a>
    </div>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>
