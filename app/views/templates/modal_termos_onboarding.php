<div id="modal-termos-onboarding" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/70 p-4" role="presentation" onclick="if (event.target === this) fecharModalTermosOnboarding()">
    <section class="flex max-h-[88vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-rosa-3 bg-surface text-text-dark shadow-2xl dark:bg-preto1 dark:text-white" role="dialog" aria-modal="true" aria-labelledby="titulo-termos-onboarding">
        <header class="flex shrink-0 items-center justify-between border-b border-cinzaMarrom/20 p-5 sm:p-6">
            <h2 id="titulo-termos-onboarding" class="font-shantell text-xl font-bold">Termos de Uso e Privacidade</h2>
            <button type="button" onclick="fecharModalTermosOnboarding()" class="rounded-full px-2 text-3xl leading-none text-text-muted transition hover:text-erro dark:text-white" aria-label="Fechar termos">&times;</button>
        </header>
        <div class="overflow-y-auto px-5 py-4 font-poppins text-sm sm:px-6">
            <?php require __DIR__ . '/termos_conteudo.php'; ?>
        </div>
        <footer class="shrink-0 border-t border-cinzaMarrom/20 p-5 sm:p-6">
            <button type="button" onclick="fecharModalTermosOnboarding()" class="btn-primario min-h-11 w-full">Entendi</button>
        </footer>
    </section>
</div>

<script>
    let acionadorModalTermosOnboarding = null;

    function abrirModalTermosOnboarding() {
        const modal = document.getElementById('modal-termos-onboarding');
        acionadorModalTermosOnboarding = document.getElementById('abrir-termos-onboarding');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        modal.querySelector('[aria-label="Fechar termos"]').focus();
    }

    function fecharModalTermosOnboarding() {
        const modal = document.getElementById('modal-termos-onboarding');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        acionadorModalTermosOnboarding?.focus();
    }

    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('modal-termos-onboarding');
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            fecharModalTermosOnboarding();
        }
    });
</script>
