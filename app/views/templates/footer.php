<?php
/**
 * Template: rodapé global + scripts da aplicação.
 */
?>
    </main>
    <footer class="relative border-t border-white/10 bg-primary text-white shadow-[0_-6px_18px_rgba(0,0,0,0.18)]">
        <img src="<?= e(BASE_URL) ?? '' ?>/assets/img/cachorrorodape.png"
             alt=""
             aria-hidden="true"
             class="pointer-events-none absolute left-0 top-1/2 h-14 w-auto -translate-y-1/2 object-contain sm:h-16">

        <img src="<?= e(BASE_URL) ?? '' ?>/assets/img/gatorodape.png"
             alt=""
             aria-hidden="true"
             class="pointer-events-none absolute right-0 top-1/2 h-14 w-auto -translate-y-1/2 object-contain sm:h-16">

        <div class="mx-auto flex h-16 max-w-figma items-center justify-center px-16 sm:px-20">
            <p class="truncate text-center font-poppins text-sm font-medium text-white/90 sm:text-base">
                &copy; Copyright <?= date('Y') ?> CãoNectados
            </p>
        </div>
    </footer>
    
    </div> 

    <script src="<?= e(BASE_URL) ?>/assets/js/menu.js" defer></script>
</body>
</html>