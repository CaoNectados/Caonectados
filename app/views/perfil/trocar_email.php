<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="max-w-md mx-auto bg-background dark:bg-corFundo-escuro min-h-screen pb-20">

    <!-- CABEÇALHO -->
    <div class="py-4 px-6 flex items-center gap-4 rounded-b-[2rem] mb-6">
        <a href="<?= URL_BASE ?>/perfil/editar" class="text-2xl hover:scale-110 transition-transform">&larr;</a>
    </div>

    <div class="px-6 my-6 text-center">
        <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
            ✉️
        </div>

        <h2 class="text-xl font-bold text-text-dark mb-2">Alterar Endereço de E-mail</h2>
        <p class="text-xs text-text-muted mb-6">
            Seu e-mail atual é <strong><?= htmlspecialchars($emailAtual ?? '') ?></strong>. <br>
            Enviaremos um código de verificação para a **nova caixa postal** para confirmar a alteração.
        </p>

        <!-- ETAPA 1: SOLICITAR NOVO E-MAIL -->
        <form action="<?= URL_BASE ?>/perfil/trocar-email/enviar-codigo" method="POST" id="form-solicitar-troca-email" class="space-y-4 text-left bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
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

        <!-- ETAPA 2: DIGITAR CÓDIGO RECEBIDO NO NOVO E-MAIL (INICIALMENTE OCULTA) -->
        <form action="<?= URL_BASE ?>/perfil/trocar-email/confirmar" method="POST" id="form-confirmar-codigo-email" class="space-y-4 mt-6 hidden text-left bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 text-center mb-2">Digite o código de 6 dígitos que enviamos para o seu novo e-mail.</p>
            
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
// Handler da etapa 1: Enviar código para o novo e-mail
document.getElementById('form-solicitar-troca-email').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const novoEmail = document.getElementById('novo_email').value.trim();
    const confEmail = document.getElementById('confirmar_email').value.trim();

    if (novoEmail !== confEmail) {
        mostrarModalFeedback('aviso', 'Os e-mails informados não coincidem.');
        return;
    }

    const form = e.target;
    const formData = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');

    btn.disabled = true;
    btn.innerText = 'Verificando e-mail...';

    try {
        const response = await fetch(form.action, { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'sucesso') {
            mostrarModalFeedback('sucesso', res.mensagem);
            form.classList.add('hidden'); // Oculta o form dos e-mails
            document.getElementById('form-confirmar-codigo-email').classList.remove('hidden'); // Mostra a caixa do código
        } else {
            mostrarModalFeedback('erro', res.mensagem);
            btn.disabled = false;
            btn.innerText = 'Continuar e Enviar Código';
        }
    } catch (err) {
        mostrarModalFeedback('erro', 'Erro de comunicação.');
        btn.disabled = false;
        btn.innerText = 'Continuar e Enviar Código';
    }
});

// Handler da etapa 2: Validar o código e efetivar o novo e-mail
document.getElementById('form-confirmar-codigo-email').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');

    btn.disabled = true;
    btn.innerText = 'Confirmando...';

    try {
        const response = await fetch(form.action, { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'sucesso') {
            mostrarModalFeedback('sucesso', res.mensagem);
            setTimeout(() => window.location.href = res.redirect_url, 1500);
        } else {
            mostrarModalFeedback('erro', res.mensagem);
            btn.disabled = false;
            btn.innerText = 'Confirmar Novo E-mail';
        }
    } catch (err) {
        mostrarModalFeedback('erro', 'Erro ao alterar e-mail.');
        btn.disabled = false;
        btn.innerText = 'Confirmar Novo E-mail';
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>