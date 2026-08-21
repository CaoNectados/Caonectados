<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Wrapper centraliza o card na altura visível da área de conteúdo (mesmo padrão de login/cadastro) -->
<div class="min-h-[75vh] flex flex-col items-center justify-center px-4 py-10">
<div class="w-full max-w-md p-6 bg-surface rounded-2xl shadow-md text-center">
    <div class="flex items-center justify-center gap-3 mb-6">
        <h1 class="font-shantell text-3xl font-bold text-primary">Nova Senha</h1>
    </div>

    <p class="text-sm text-text-muted mb-6">
        Crie uma nova senha para a conta vinculada ao e-mail: <br>
        <strong class="text-text-dark"><?= htmlspecialchars($email ?? '') ?></strong>
    </p>

    <form action="<?= URL_BASE ?>/redefinir-senha/processar" method="POST" class="space-y-4 text-left">
        <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo ?? '') ?>">
        <div>
            <label for="senha" class="label-padrao">Nova Senha</label>
            <input type="password" name="senha" id="senha" minlength="8" placeholder="Digite sua nova senha"
                class="input-padrao">
        </div>

        <div>
            <label for="senha_confirmacao" class="label-padrao">Confirme a Nova Senha</label>
            <input type="password" name="senha_confirmacao" id="senha_confirmacao" minlength="8" placeholder="Repita a senha"
                class="input-padrao">
        </div>

        <button type="submit" class="btn-primario w-full mt-2">
            Salvar e Entrar
        </button>
    </form>
</div>
</div>
<script>
document.querySelector('form').addEventListener('submit', async function(event) {
    event.preventDefault();

    const form = event.target;
    const senha = form.querySelector('#senha').value;
    const senhaConfirmacao = form.querySelector('#senha_confirmacao').value;

    if (!CaonectadosValidator.validarForcaSenha(senha)) {
        mostrarModalFeedback('aviso', 'A senha deve ter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial.');
        return;
    }

    if (senha !== senhaConfirmacao) {
        mostrarModalFeedback('aviso', 'As senhas não coincidem.');
        return;
    }

    const formData = new FormData(form);
    const btnSubmit = form.querySelector('button[type="submit"]');
    const btnTextoOriginal = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = 'Salvando...';

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
            setTimeout(() => { window.location.href = result.redirect_url; }, 1500);
        }
    } catch (error) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = btnTextoOriginal;
        mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
    }
});
</script>
<script src="<?= e(asset('assets/js/autosave.js')) ?>"></script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>