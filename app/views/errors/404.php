<?php 
require_once __DIR__ . '/../templates/header.php';

$title = '404';
$description = 'Page not found';
$keywords = '404, page not found';
?>

<!-- Centralizando melhor a caixa de erro no meio da tela com flex -->
<div class="flex items-center justify-center min-h-[70vh] p-4">
    <div class="text-center bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-gray-200">
        <div class="flex justify-center mb-4 text-amber-600"><?= icone('paw', 'h-16 w-16') ?></div>
        <h1 class="text-6xl font-extrabold text-amber-600 mb-2">404</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Au! Página não encontrada</h2>
        <p class="text-gray-600 mb-6">
            Parece que o caminho que você tentou acessar não existe ou foi movido.
        </p>
        <a href="<?= URL_BASE ?>/home" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-3 rounded-xl transition duration-200 shadow-md">
            Voltar para o Início
        </a>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>