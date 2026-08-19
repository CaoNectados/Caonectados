<?php
require_once __DIR__ . '/../templates/header.php';
?>

<div class="max-w-md mx-auto p-6 text-center mt-10">
    
    <div class="w-24 h-24 bg-aviso/20 text-aviso rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
        <span class="text-4xl">&#8987;</span> 
    </div>

    <h1 class="text-3xl font-bold text-text-dark mb-4 font-shantell">Em Análise</h1>
    
    <p class="text-text-muted mb-6 leading-relaxed">
        Seu cadastro e comprovante de atividade foram enviados com sucesso! Nossa equipe analisará os dados para garantir a segurança da plataforma.
    </p>

    <div class="bg-surface border border-rosa-2 rounded-lg p-4 mb-8 text-sm text-text-muted text-left shadow-sm">
        <strong class="text-text-dark">O que acontece agora?</strong><br>
        1. A equipe do CãoNectados vai conferir o seu documento.<br>
        2. Você receberá uma notificação ou e-mail assim que o perfil for aprovado.<br>
        3. Após a aprovação, você poderá cadastrar animais e receber pedidos de adoção.
    </div>

    <a href="<?= URL_BASE ?>/logout" class="btn-secundario w-full block text-center">
        Sair da conta
    </a>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>