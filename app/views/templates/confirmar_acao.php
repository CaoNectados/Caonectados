<?php
require_once __DIR__.'/header.php';
echo renderVoltar($confirmacao['voltar']);
?>
<section class="mx-auto flex min-h-[60vh] max-w-lg items-center py-8">
    <div class="w-full rounded-3xl border border-rosa-3 bg-surface p-6 sm:p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-erro/10"><img src="<?= e(asset('assets/icons/geral/recusado.svg')) ?>" alt="" class="h-8 w-8"></div>
        <h1 class="font-shantell text-2xl font-bold text-text-dark"><?= e($confirmacao['titulo']) ?></h1>
        <p class="mt-4 break-words text-text-dark">Confirmar esta ação para <strong><?= e($confirmacao['nome']) ?></strong>?</p>
        <p class="mt-3 text-sm text-text-muted"><?= e($confirmacao['mensagem']) ?></p>
        <form method="POST" action="<?= e(rtrim(URL_BASE,'/').$confirmacao['acao']) ?>" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= (int)$confirmacao['id'] ?>">
            <a href="<?= e(rtrim(URL_BASE,'/').$confirmacao['voltar']) ?>" class="btn-secundario flex min-h-11 items-center justify-center">Cancelar</a>
            <button type="submit" class="min-h-11 rounded-xl bg-erro px-4 py-3 font-semibold text-white hover:opacity-90"><?= e($confirmacao['botao']) ?></button>
        </form>
    </div>
</section>
<?php require_once __DIR__.'/footer.php'; ?>
