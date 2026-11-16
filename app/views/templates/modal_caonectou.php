<?php if (!empty($encontrosCaoNectou)):
$dadosCaoNectou = reset($encontrosCaoNectou);
?>
<section id="modal-caonectou" class="hidden fixed inset-0 z-[100] items-center justify-center overflow-y-auto bg-[#100b24]/90 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="titulo-caonectou" aria-hidden="true">
    <div id="etapa-petisco-caonectou" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <img id="gif-caonectou" src="<?= e(asset('assets/img/caonectou-animacao.gif')) ?>" data-src="<?= e(asset('assets/img/caonectou-animacao.gif')) ?>" alt="" class="gif-caonectou absolute inset-0 h-full w-full object-cover">
        <p class="absolute inset-x-4 top-[8vh] z-10 text-center font-shantell text-xl font-bold text-white drop-shadow-[0_2px_8px_rgba(0,0,0,.8)] sm:text-2xl">Uma nova amizade está começando...</p>
    </div>

    <article id="cartao-caonectou" class="relative z-10 my-auto hidden w-full max-w-lg scale-95 rounded-[2rem] border border-rosa-3 bg-white p-5 text-center text-[#30243c] opacity-0 shadow-[0_25px_90px_rgba(0,0,0,.55)] transition duration-500 dark:bg-[#21173b] dark:text-white sm:p-8">
        <button type="button" id="fechar-caonectou" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-2xl text-primary transition hover:bg-primary hover:text-white dark:bg-white/10 dark:text-white" aria-label="Fechar">&times;</button>
        <h2 id="titulo-caonectou" class="mt-9 font-shantell text-2xl font-bold text-primary dark:text-white sm:mt-0 sm:px-10 sm:text-3xl">CãoNectou!</h2>
        <p class="mt-2 text-sm text-[#6f6675] dark:text-white/75 sm:text-base">Uma nova história começa com carinho.</p>

        <div class="relative mx-auto mt-6 h-52 w-full max-w-[360px] sm:mt-7 sm:h-56">
            <div class="foto-encontro absolute left-0 sm:left-6 top-0 z-10 flex w-36 flex-col items-center">
                <img id="foto-animal-caonectou" src="<?= e($dadosCaoNectou['fotoAnimal']) ?>" alt="Animal adotado" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg dark:border-white/90">
                <span id="nome-animal-caonectou" class="mt-1 max-w-full truncate text-xs font-bold sm:text-sm"><?= e($dadosCaoNectou['animal']) ?></span>
            </div>
            <div class="foto-encontro foto-encontro-adotante absolute right-0 sm:right-6 top-0 z-10 flex w-36 flex-col items-center">
                <img id="foto-adotante-caonectou" src="<?= e($dadosCaoNectou['fotoAdotante']) ?>" alt="Adotante" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg dark:border-white/90">
                <span id="nome-adotante-caonectou" class="mt-1 max-w-full truncate text-xs font-bold sm:text-sm"><?= e($dadosCaoNectou['adotante']) ?></span>
            </div>
            <img src="<?= e(asset('assets/img/icone-adotado-patinha-coracao.png')) ?>" alt="Coração com uma patinha" class="absolute left-1/2 top-6 z-20 h-20 w-20 -translate-x-1/2 object-contain drop-shadow-[0_5px_9px_rgba(0,0,0,.3)] sm:h-24 sm:w-24">
            <div class="absolute bottom-0 left-1/2 z-20 flex -translate-x-1/2 flex-col items-center">
                <img id="foto-responsavel-caonectou" src="<?= e($dadosCaoNectou['fotoResponsavel']) ?>" alt="ONG ou protetor" class="h-12 w-12 rounded-full border-2 border-white bg-white object-cover shadow-md dark:border-white/80">
                <span id="nome-responsavel-caonectou" class="mt-1 max-w-40 truncate text-[11px] font-semibold text-[#51465a] dark:text-white/85 sm:text-xs"><?= e($dadosCaoNectou['responsavel']) ?></span>
            </div>
        </div>

        <p id="mensagem-caonectou" class="mt-3 text-sm leading-relaxed text-[#51465a] dark:text-white/85 sm:text-base"></p>
        <a id="whatsapp-caonectou" href="#" target="_blank" rel="noopener noreferrer" class="mt-6 hidden min-h-12 w-full items-center justify-center rounded-full bg-[#4a6645] px-5 py-3 font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#3d5939]">Conversar com a ONG pelo WhatsApp</a>
        <p id="whatsapp-indisponivel-caonectou" class="mt-5 hidden text-xs text-[#786f7e] dark:text-white/70">O responsável ainda não cadastrou um telefone para contato pelo WhatsApp.</p>
        <button type="button" id="continuar-caonectou" class="mt-4 w-full rounded-full border border-primary/35 px-5 py-3 font-bold text-primary transition hover:bg-primary/5 dark:border-white/50 dark:text-white dark:hover:bg-white/10">Continuar</button>
    </article>
</section>

<style>
    #modal-caonectou.caonectou-aberto { display: flex; }
    #modal-caonectou.caonectou-final #etapa-petisco-caonectou { display: none; }
    #modal-caonectou.caonectou-final #cartao-caonectou { display: block; }
    #modal-caonectou.caonectou-final #cartao-caonectou.cartao-visivel { opacity: 1; transform: scale(1); }
    .gif-caonectou { animation: gif-sobe-do-fundo .8s cubic-bezier(.2,.75,.25,1) both; }
    .foto-encontro { animation: fotos-encontro .65s cubic-bezier(.2,.8,.2,1) both; }
    .foto-encontro-adotante { animation-delay:.12s; }
    @keyframes gif-sobe-do-fundo { from { transform:translateY(100%); } to { transform:translateY(0); } }
    @keyframes fotos-encontro { from { opacity:0; transform:translateY(-55vh) scale(.65); } to { opacity:1; transform:translateY(0) scale(1); } }
    @media (min-width: 1024px) {
        #gif-caonectou { inset:15vh auto auto 15vw; width:70vw; height:70vh; object-fit:contain; }
    }
    @media (prefers-reduced-motion: reduce) {
        .gif-caonectou,.foto-encontro { animation-duration:.01ms !important; animation-iteration-count:1 !important; }
    }
</style>

<script type="application/json" id="dados-caonectou"><?= json_encode((object) $encontrosCaoNectou, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script src="<?= e(asset('assets/js/caonectou.js')) ?>" defer></script>
<?php endif; ?>

