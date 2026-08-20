<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- flex-col: seta de voltar fica fixa no topo; o bloco de conteúdo abaixo (flex-1) centraliza
     verticalmente no espaço restante da tela, em vez de nascer colado no topo -->
<div class="max-w-md mx-auto bg-background min-h-screen pb-20 flex flex-col">

    <div class="py-4 px-6 flex items-center gap-4 rounded-b-[2rem]">
        <a href="<?= URL_BASE ?>/perfil/editar" class="hover:scale-110 transition-transform text-text-dark"><?= icone('arrow-left', 'h-6 w-6') ?></a>
    </div>

    <div class="px-6 flex-1 flex flex-col justify-center text-center">
        <div class="w-20 h-20 bg-roxinhoFofo/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
            <?= icone('mail', 'h-9 w-9') ?>
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

</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>