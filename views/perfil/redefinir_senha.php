<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- flex-col: seta de voltar fica fixa no topo; o bloco de conteúdo abaixo (flex-1) centraliza
     verticalmente no espaço restante da tela, em vez de nascer colado no topo -->
<div class="max-w-md mx-auto bg-background min-h-screen pb-20 flex flex-col">

    <div class="py-4 px-6 flex items-center gap-4 rounded-b-[2rem]">
        <a href="<?= URL_BASE ?>/perfil/editar" class="hover:scale-110 transition-transform text-text-dark"><?= icone('arrow-left', 'h-6 w-6') ?></a>
    </div>

    <div class="px-6 flex-1 flex flex-col justify-center text-center">
        <div class="w-20 h-20 bg-roxinhoFofo/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
            <?= icone('key', 'h-9 w-9') ?>
        </div>

        <h2 class="text-xl font-bold text-text-dark mb-2">Alteração Segura de Senha</h2>
        <p class="text-sm text-text-muted mb-6">
            Para sua segurança, enviaremos um código de verificação de 6 dígitos para o seu e-mail cadastrado: <br>
            <strong class="text-text-dark font-bold"><?= htmlspecialchars($emailMascarado ?? '') ?></strong>
        </p>

        <!-- ETAPA 1 -->
        <form action="<?= URL_BASE ?>/perfil/redefinir-senha/enviar-codigo" method="POST" id="form-enviar-codigo-senha" class="space-y-4">
            <button type="submit" class="btn-primario w-full inline-flex items-center justify-center gap-2">
                <?= icone('mail', 'h-5 w-5') ?> Enviar Código para E-mail
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
    // (mesmo JavaScript, sem alterações)
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>