<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- flex-col: seta de voltar fica fixa no topo; o bloco de conteúdo abaixo (flex-1) centraliza
     verticalmente no espaço restante da tela, em vez de nascer colado no topo -->
<div class="max-w-md mx-auto bg-background min-h-screen pb-20 flex flex-col">

    <div class="py-4 px-6 flex items-center gap-4 rounded-b-[2rem]">
        <a href="<?= URL_BASE ?>/perfil/editar" class="text-2xl hover:scale-110 transition-transform text-text-dark">&larr;</a>
    </div>

    <div class="px-6 flex-1 flex flex-col justify-center text-center">
        <div class="w-20 h-20 bg-roxinhoFofo/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
            ✉️
        </div>

        <h2 class="text-xl font-bold text-text-dark mb-2">Alterar Endereço de E-mail</h2>
        <p class="text-xs text-text-muted mb-6">
            Seu e-mail atual é <strong><?= htmlspecialchars($emailAtual ?? '') ?></strong>. <br>
            Enviaremos um código de verificação para a <strong>nova caixa postal</strong> para confirmar a alteração.
        </p>

        <!-- ETAPA 1 -->
        <form action="<?= URL_BASE ?>/perfil/trocar-email/enviar-codigo" method="POST" id="form-solicitar-troca-email" class="space-y-4 text-left bg-surface p-5 rounded-2xl shadow-sm border border-rosa-2">
            <div>
                <label class="label-padrao">Novo E-mail *</label>
                <input type="email" name="novo_email" id="novo_email" required placeholder="seu.novo@email.com" class="input-padrao">
            </div>

            <div>
                <label class="label-padrao">Confirmar Novo E-mail *</label>
                <input type="email" name="confirmar_email" id="confirmar_email" required placeholder="Repita o novo e-mail" class="input-padrao">
            </div>

            <button type="submit" class="btn-primario w-full mt-2">
                Continuar e Enviar Código
            </button>
        </form>

        <!-- ETAPA 2 -->
        <form action="<?= URL_BASE ?>/perfil/trocar-email/confirmar" method="POST" id="form-confirmar-codigo-email" class="space-y-4 mt-6 hidden text-left bg-surface p-5 rounded-2xl shadow-sm border border-rosa-2">
            <p class="text-xs text-text-muted text-center mb-2">Digite o código de 6 dígitos que enviamos para o seu novo e-mail.</p>
            
            <div>
                <label class="label-padrao">Código de Verificação *</label>
                <input type="text" name="codigo" maxlength="6" required placeholder="000000" class="input-padrao text-center text-2xl tracking-widest font-mono">
            </div>

            <button type="submit" class="btn-primario w-full">
                Confirmar Novo E-mail
            </button>
        </form>
    </div>
</div>

<script>
    const formSolicitarTroca = document.getElementById('form-solicitar-troca-email');
    const formConfirmarCodigo = document.getElementById('form-confirmar-codigo-email');

    formSolicitarTroca.addEventListener('submit', async function(event) {
        event.preventDefault();

        const novoEmail = formSolicitarTroca.querySelector('#novo_email').value.trim();
        const confirmarEmail = formSolicitarTroca.querySelector('#confirmar_email').value.trim();

        if (!CaonectadosValidator.validarEmail(novoEmail)) {
            mostrarModalFeedback('aviso', 'Informe um e-mail válido.');
            return;
        }

        if (novoEmail !== confirmarEmail) {
            mostrarModalFeedback('aviso', 'Os e-mails informados não coincidem.');
            return;
        }

        const formData = new FormData(formSolicitarTroca);
        const btnSubmit = formSolicitarTroca.querySelector('button[type="submit"]');
        const txtBtn = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Enviando...';

        try {
            const response = await fetch(formSolicitarTroca.action, { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'erro') {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = txtBtn;
                mostrarModalFeedback('erro', result.mensagem);
                return;
            }

            mostrarModalFeedback('sucesso', result.mensagem);
            formSolicitarTroca.classList.add('hidden');
            formConfirmarCodigo.classList.remove('hidden');
        } catch (error) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = txtBtn;
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        }
    });

    formConfirmarCodigo.addEventListener('submit', async function(event) {
        event.preventDefault();

        const formData = new FormData(formConfirmarCodigo);
        const btnSubmit = formConfirmarCodigo.querySelector('button[type="submit"]');
        const txtBtn = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Confirmando...';

        try {
            const response = await fetch(formConfirmarCodigo.action, { method: 'POST', body: formData });
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