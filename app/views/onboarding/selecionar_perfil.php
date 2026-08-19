<?php 
require_once __DIR__ . '/../templates/header.php'; 
?>

<main class="flex flex-col items-center justify-center min-h-[70vh] px-4 py-8">
    <div class="max-w-4xl w-full space-y-8 text-center">
        
        <!-- Cabeçalho -->
        <div>
            <h1 class="font-shantell text-4xl md:text-5xl font-bold text-primary">
                Bem‑vindo(a) ao CãoNectados 🐾
            </h1>
            <p class="text-lg text-text-muted mt-2 max-w-2xl mx-auto">
                Escolha o perfil que melhor descreve você e comece a transformar vidas.
            </p>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            
            <!-- Card Adotante -->
            <div class="bg-surface rounded-2xl shadow-lg p-6 border border-rosa-2 hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-rosa-1 flex items-center justify-center text-4xl mb-4">
                    🏠
                </div>
                <h2 class="font-shantell text-2xl font-bold text-text-dark">Quero Adotar</h2>
                <p class="text-text-muted text-sm mt-2 leading-relaxed">
                    Encontre seu novo melhor amigo. Navegue por animais disponíveis, dê petiscos e inicie o contato com protetores.
                </p>
                <a href="<?= URL_BASE ?>/onboarding/adotante" 
                   class="mt-6 btn-primario w-full sm:w-auto">
                    Escolher este perfil
                </a>
            </div>

            <!-- Card Protetor Independente -->
            <div class="bg-surface rounded-2xl shadow-lg p-6 border border-rosa-2 hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-rosa-1 flex items-center justify-center text-4xl mb-4">
                    🐕
                </div>
                <h2 class="font-shantell text-2xl font-bold text-text-dark">Protetor</h2>
                <p class="text-text-muted text-sm mt-2 leading-relaxed">
                    Cadastre os animais que você resgata, gerencie solicitações de adoção e compartilhe sua causa com a comunidade.
                </p>
                <a href="<?= URL_BASE ?>/onboarding/protetor" 
                   class="mt-6 btn-primario w-full sm:w-auto">
                    Escolher este perfil
                </a>
            </div>

            <!-- Card ONG -->
            <div class="bg-surface rounded-2xl shadow-lg p-6 border border-rosa-2 hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-rosa-1 flex items-center justify-center text-4xl mb-4">
                    🏢
                </div>
                <h2 class="font-shantell text-2xl font-bold text-text-dark">Sou uma ONG</h2>
                <p class="text-text-muted text-sm mt-2 leading-relaxed">
                    Cadastre sua instituição, mobilize voluntários, receba doações e encontre lares para muitos animais.
                </p>
                <a href="<?= URL_BASE ?>/onboarding/ong" 
                   class="mt-6 btn-primario w-full sm:w-auto">
                    Escolher este perfil
                </a>
            </div>

        </div>

        <!-- Rodapé informativo -->
        <p class="text-xs text-text-muted mt-8">
            Todos os perfis passam por validação para garantir a segurança da nossa comunidade.
        </p>
    </div>
</main>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>