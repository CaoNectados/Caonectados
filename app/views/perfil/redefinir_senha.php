<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- flex-col: seta de voltar fica fixa no topo; o bloco de conteúdo abaixo (flex-1) centraliza
     verticalmente no espaço restante da tela, em vez de nascer colado no topo -->
<div class="max-w-md mx-auto bg-background min-h-screen pb-20 flex flex-col">

    <div class="py-4 px-6 flex items-center gap-4 rounded-b-[2rem]">
        <a href="<?= URL_BASE ?>/perfil/editar" class="text-2xl hover:scale-110 transition-transform text-text-dark">&larr;</a>
    </div>

    <div class="px-6 flex-1 flex flex-col justify-center text-center">
        <div class="w-20 h-20 bg-roxinhoFofo/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
            🔑
        </div>

        <h2 class="text-xl font-bold text-text-dark mb-2">Alteração Segura de Senha</h2>
        <p class="text-sm text-text-muted mb-6">
            Para sua segurança, enviaremos um código de verificação de 6 dígitos para o seu e-mail cadastrado: <br>
            <strong class="text-text-dark font-bold"><?= htmlspecialchars($emailMascarado ?? '') ?></strong>
        </p>

        <!-- ETAPA 1 -->
        <form action="<?= URL_BASE ?>/perfil/redefinir-senha/enviar-codigo" method="POST" id="form-enviar-codigo-senha" class="space-y-4">
            <button type="submit" class="btn-primario w-full">
                ✉️ Enviar Código para E-mail
            </button>
        </form>

        <!-- ETAPA 2 -->
        <form action="<?= URL_BASE ?>/perfil/redefinir-senha/confirmar" method="POST" id="form-confirmar-nova-senha" class="space-y-4 mt-6 hidden text-left bg-surface p-5 rounded-2xl shadow-sm border border-rosa-2">
            <div>
                <label class="label-padrao">Código de 6 Dígitos *</label>
                <input type="text" name="codigo" maxlength="6" required placeholder="000000" class="input-padrao text-center text-2xl tracking-widest font-mono">
            </div>

            <div>
                <label class="label-padrao">Nova Senha *</label>
                <input type="password" name="nova_senha" required placeholder="Mínimo 8 caracteres" class="input-padrao">
                <p class="text-[10px] text-text-muted mt-1">Exige maiúscula, minúscula, número e caractere especial.</p>
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
    const formEnviarCodigo = document.getElementById('form-enviar-codigo-senha');
    const formConfirmarSenha = document.getElementById('form-confirmar-nova-senha');

    formEnviarCodigo.addEventListener('submit', async function(event) {
        event.preventDefault();

        const btnSubmit = formEnviarCodigo.querySelector('button[type="submit"]');
        const txtBtn = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Enviando...';

        try {
            const response = await fetch(formEnviarCodigo.action, { method: 'POST' });
            const result = await response.json();

            if (result.status === 'erro') {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = txtBtn;
                mostrarModalFeedback('erro', result.mensagem);
                return;
            }

            mostrarModalFeedback('sucesso', result.mensagem);
            formEnviarCodigo.classList.add('hidden');
            formConfirmarSenha.classList.remove('hidden');
        } catch (error) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = txtBtn;
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        }
    });

    formConfirmarSenha.addEventListener('submit', async function(event) {
        event.preventDefault();

        const novaSenha = formConfirmarSenha.querySelector('[name="nova_senha"]').value;
        const confirmarSenha = formConfirmarSenha.querySelector('[name="confirmar_senha"]').value;

        if (!CaonectadosValidator.validarForcaSenha(novaSenha)) {
            mostrarModalFeedback('aviso', 'A senha deve ter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial.');
            return;
        }

        if (novaSenha !== confirmarSenha) {
            mostrarModalFeedback('aviso', 'As senhas não coincidem.');
            return;
        }

        const formData = new FormData(formConfirmarSenha);
        const btnSubmit = formConfirmarSenha.querySelector('button[type="submit"]');
        const txtBtn = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Salvando...';

        try {
            const response = await fetch(formConfirmarSenha.action, { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'erro') {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = txtBtn;
                mostrarModalFeedback('erro', result.mensagem);
                return;
            }

            mostrarModalFeedback('sucesso', result.mensagem);
            setTimeout(() => { window.location.href = result.redirect_url; }, 1500);
        } catch (error) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = txtBtn;
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        }
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>