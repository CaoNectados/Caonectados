<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="max-w-md mx-auto p-6 bg-surface rounded-2xl shadow-md my-10 text-center">
    <div class="flex items-center justify-center gap-3 mb-6">
        <h1 class="font-shantell text-3xl font-bold text-primary">Esqueci a Senha</h1>
    </div>

    <p class="text-sm text-text-muted mb-6">
        Digite o e-mail cadastrado na sua conta. Enviaremos um link para você redefinir sua senha.
    </p>

    <form action="<?= URL_BASE ?>/esqueci-senha/processar" method="POST" class="space-y-5 text-left">
        <div>
            <label for="email" class="label-padrao">Seu E-mail</label>
            <input type="email" name="email" id="email" required placeholder="exemplo@gmail.com"
                   class="input-padrao">
        </div>

        <button type="submit" class="btn-primario w-full">
            Enviar Link de Recuperação
        </button>
    </form>

    <div class="mt-6 border-t border-rosa-2 pt-4">
        <a href="<?= URL_BASE ?>/login" class="text-sm text-primary hover:underline font-medium inline-flex items-center gap-1">
            &#129144; Voltar para o Login
        </a>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', async function(event) {
    event.preventDefault(); 

    const form = event.target;
    const formData = new FormData(form);
    const btnSubmit = form.querySelector('button[type="submit"]');
    const btnTextoOriginal = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = 'Enviando...';

    try {
        const response = await fetch(form.action, { method: 'POST', body: formData });
        const result = await response.json();

        if (result.status === 'erro') {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = btnTextoOriginal;
            mostrarModalFeedback('erro', result.mensagem); 
        } else if (result.status === 'sucesso') {
            if (typeof limparAutoSave === 'function') limparAutoSave();
            
            mostrarModalFeedback('sucesso', result.mensagem);
            setTimeout(() => { window.location.href = result.redirect_url; }, 2000);
        }
    } catch (error) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = btnTextoOriginal;
        mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
    }
});
</script>
<script src="<?= URL_BASE ?>/assets/js/autosave.js"></script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>