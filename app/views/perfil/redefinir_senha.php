<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="max-w-md mx-auto bg-background dark:bg-corFundo-escuro min-h-screen pb-20">

    <!-- CABEÇALHO -->
    <div class=" py-4 px-6 flex items-center gap-4 rounded-b-[2rem]  mb-6">
        <a href="<?= URL_BASE ?>/perfil/editar" class="text-2xl hover:scale-110 transition-transform">&larr;</a>
    </div>

    <div class="px-6 my-8 text-center">
        <div class="w-20 h-20 bg-roxinhoFofo/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
            🔑
        </div>

        <h2 class="text-xl font-bold text-text-dark mb-2">Alteração Segura de Senha</h2>
        <p class="text-sm text-text-muted mb-6">
            Para sua segurança, enviaremos um código de verificação de 6 dígitos para o seu e-mail cadastrado: <br>
            <strong class="text-text-dark font-bold"><?= htmlspecialchars($emailMascarado ?? '') ?></strong>
        </p>

        <!-- ETAPA 1: SOLICITAR CÓDIGO -->
        <form action="<?= URL_BASE ?>/perfil/redefinir-senha/enviar-codigo" method="POST" id="form-enviar-codigo-senha" class="space-y-4">
            <button type="submit" class="btn-primario w-full">
                ✉️ Enviar Código para E-mail
            </button>
        </form>

        <!-- ETAPA 2: VALIDAR CÓDIGO E DIGITAR NOVA SENHA (INICIALMENTE OCULTA) -->
        <form action="<?= URL_BASE ?>/perfil/redefinir-senha/confirmar" method="POST" id="form-confirmar-nova-senha" class="space-y-4 mt-6 hidden text-left bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <label class="label-padrao">Código de 6 Dígitos *</label>
                <input type="text" name="codigo" maxlength="6" required placeholder="000000" class="input-padrao text-center text-2xl tracking-widest font-mono">
            </div>

            <div>
                <label class="label-padrao">Nova Senha *</label>
                <input type="password" name="nova_senha" required placeholder="Mínimo 8 caracteres" class="input-padrao">
                <p class="text-[10px] text-gray-400 mt-1">Exige maiúscula, minúscula, número e caractere especial.</p>
            </div>

            <div>
                <label class="label-padrao">Confirmar Nova Senha *</label>
                <input type="password" name="confirmar_senha" required placeholder="Repita a nova senha" class="input-padrao">
            </div>

            <button type="submit" class="btn-primario w-full mt-2">
                Salvar Nova Senha
            </button>
        </form>
    </div>
</div>

<script>
// Handler do envio do código
document.getElementById('form-enviar-codigo-senha').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'Enviando...';

    try {
        const response = await fetch(this.action, { method: 'POST' });
        const res = await response.json();

        if (res.status === 'sucesso') {
            mostrarModalFeedback('sucesso', res.mensagem);
            this.classList.add('hidden'); // Esconde o botão de enviar
            document.getElementById('form-confirmar-nova-senha').classList.remove('hidden'); // Abre os inputs da senha
        } else {
            mostrarModalFeedback('erro', res.mensagem);
            btn.disabled = false;
            btn.innerText = '✉️ Enviar Código para E-mail';
        }
    } catch (err) {
        mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        btn.disabled = false;
        btn.innerText = '✉️ Enviar Código para E-mail';
    }
});

// Handler da confirmação da nova senha
document.getElementById('form-confirmar-nova-senha').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');

    btn.disabled = true;
    btn.innerText = 'Salvando...';

    try {
        const response = await fetch(form.action, { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'sucesso') {
            mostrarModalFeedback('sucesso', res.mensagem);
            setTimeout(() => window.location.href = res.redirect_url, 1500);
        } else {
            mostrarModalFeedback('erro', res.mensagem);
            btn.disabled = false;
            btn.innerText = 'Salvar Nova Senha';
        }
    } catch (err) {
        mostrarModalFeedback('erro', 'Erro ao salvar a nova senha.');
        btn.disabled = false;
        btn.innerText = 'Salvar Nova Senha';
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>