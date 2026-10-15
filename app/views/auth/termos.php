<?php require_once __DIR__.'/../templates/header.php'; echo renderVoltar('/cadastro'); ?>
<article class="mx-auto max-w-2xl rounded-3xl border border-rosa-3 bg-surface p-5 sm:p-8 my-5 text-text-dark">
    <h1 class="font-shantell text-2xl font-bold mb-3">Termos de Uso e Privacidade</h1>
    <p class="text-sm text-text-muted mb-5">Versão <?= e(\app\services\ValidationService::VERSAO_TERMOS) ?></p>
    <?php require __DIR__.'/../templates/termos_conteudo.php'; ?>
</article>
<?php require_once __DIR__.'/../templates/footer.php'; ?>
