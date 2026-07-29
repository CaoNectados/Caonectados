<?php 
require_once __DIR__ . '/../templates/header.php'; 
?>
    <header class="text-center my-6">
        <h1 class="text-2xl font-bold font-shantell text-primary">Cadastre-se</h1>
    </header>

    <form action="<?= URL_BASE ?>/cadastro" method="POST" class="max-w-md mx-auto p-4 space-y-4">

        <div>
            <label for="email" class="label-padrao">E-mail</label>
            <input type="email" name="email" id="email" required placeholder="Digite seu e-mail" class="input-padrao">
        </div>

        <div>
            <label for="senha" class="label-padrao">Crie uma senha</label>
            <div class="relative">
                <input type="password" name="senha" id="senha" required placeholder="Crie uma senha" class="input-padrao pr-10">
                <button type="button" onclick="toggleSenha('senha', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    👁️
                </button>
            </div>
        </div>

        <div>
            <label for="senha_confirmacao" class="label-padrao">Confirme sua senha</label>
            <div class="relative">
                <input type="password" name="senha_confirmacao" id="senha_confirmacao" required placeholder="Confirme sua senha" class="input-padrao pr-10">
                <button type="button" onclick="toggleSenha('senha_confirmacao', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    👁️
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primario w-full mt-2">Cadastrar</button>
    </form>

    <div class="text-center mt-4">
        <p class="text-sm text-text-muted">Já tem uma conta? <a href="<?= URL_BASE ?>/login" class="text-primary font-bold hover:underline">Faça Login Aqui!</a></p>
    </div>

<script>
function toggleSenha(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
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