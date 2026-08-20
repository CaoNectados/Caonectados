<?php
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
?>

<div class="min-h-[75vh] flex flex-col items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
         <div class="flex justify-end gap-6 mt-10 opacity-80">
            <img src="<?= $urlBase ?>/assets/img/patinha-baixo.png" alt="" class="w-8 h-8 -rotate-12">
            <img src="<?= $urlBase ?>/assets/img/patinha-cima.png" alt="" class="w-8 h-8 rotate-12 mt-4">
        </div>
        <h1 class="text-center text-3xl font-bold font-shantell text-text-dark dark:text-white mb-8">Cadastre-se</h1>

        <form action="<?= URL_BASE ?>/cadastro" method="POST" class="space-y-5">

            <div>
                <input type="email" name="email" id="email" placeholder="E-mail" class="input-padrao">
            </div>

            <div>
                <div class="relative">
                    <input type="password" name="senha" id="senha" placeholder="Crie uma senha" class="input-padrao pr-12">
                    <button type="button" onclick="togglePassword('senha', this)" class="absolute right-3 top-1/2 -translate-y-1/2 focus:outline-none" aria-label="Mostrar senha">
                        <img src="<?= $urlBase ?>/assets/icons/olho-aberto.svg" alt="Mostrar senha" class="w-5 h-5">
                    </button>
                </div>
            </div>

            <div>
                <div class="relative">
                    <input type="password" name="senha_confirmacao" id="senha_confirmacao" placeholder="Confirme sua senha" class="input-padrao pr-12">
                    <button type="button" onclick="togglePassword('senha_confirmacao', this)" class="absolute right-3 top-1/2 -translate-y-1/2 focus:outline-none" aria-label="Mostrar senha">
                        <img src="<?= $urlBase ?>/assets/icons/olho-aberto.svg" alt="Mostrar senha" class="w-5 h-5">
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth-primario text-white">Cadastrar</button>

            <a href="<?= URL_BASE ?>/login" class="btn-auth-secundario">Faça Login Aqui!</a>
        </form>
         <div class="flex justify-start gap-6 mt-10 opacity-80">
            <img src="<?= $urlBase ?>/assets/img/patinha-baixo.png" alt="" class="w-8 h-8 -rotate-12">
            <img src="<?= $urlBase ?>/assets/img/patinha-cima.png" alt="" class="w-8 h-8 rotate-12 mt-4">
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const img = btn.querySelector('img');
    const olhoAberto = '<?= $urlBase ?>/assets/icons/olho-aberto.svg';
    const olhoFechado = '<?= $urlBase ?>/assets/icons/olho-fechado.svg';

    if (input.type === 'password') {
        input.type = 'text';
        img.src = olhoFechado;
        img.alt = 'Ocultar senha';
        btn.setAttribute('aria-label', 'Ocultar senha');
    } else {
        input.type = 'password';
        img.src = olhoAberto;
        img.alt = 'Mostrar senha';
        btn.setAttribute('aria-label', 'Mostrar senha');
    }
}

document.querySelector('form').addEventListener('submit', async function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const btnSubmit = form.querySelector('button[type="submit"]');
    const btnTextoOriginal = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = 'Aguarde...';

    try {
        const response = await fetch(form.action, { method: 'POST', body: formData });
        const result = await response.json();

        if (result.status === 'erro') {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = btnTextoOriginal;
            mostrarModalFeedback('erro', result.mensagem);
        } else if (result.status === 'sucesso') {
            if (typeof limparAutoSave === 'function') limparAutoSave();
            window.location.href = result.redirect_url;
        }
    } catch (error) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = btnTextoOriginal;
        mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
    }
});
</script>
<script src="<?= URL_BASE ?>/assets/js/autosave.js"></script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
