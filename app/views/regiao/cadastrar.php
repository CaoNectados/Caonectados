<?php 
require_once __DIR__ . '/../templates/header.php';
echo renderVoltar('/admin/regiao');
?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg bg-surface rounded-3xl shadow-xl border border-rosa-3 p-6 md:p-8 transition-colors duration-200">
        
        <h1 class="font-shantell text-2xl font-extrabold text-text-dark mb-6 text-center">
            Cadastrar Nova Região
        </h1>

        <form action="<?= URL_BASE ?>/admin/regiao/salvar" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div>
                <label for="nome_regiao" class="block text-sm font-semibold text-text-dark mb-2">
                    Nome da Região:
                </label>
                <input type="text" 
                       id="nome_regiao" 
                       name="nome_regiao"
                       placeholder="Ex: Polo Centro, Três Lagoas..."
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-primary/30 bg-surface text-text-dark focus:outline-none focus:border-primary">
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="<?= URL_BASE ?>/admin/regiao" 
                   class="text-sm font-medium text-text-muted hover:text-text-dark">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-primary hover:opacity-90 text-white font-bold rounded-xl shadow-md transition">
                    Salvar Região
                </button>
            </div>
        </form>

    </div>
</div>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>