<?php
require_once __DIR__ . '/../templates/header.php';

$animal = $_SESSION['animal'] ?? null;

if (!$animal) {
    echo "<main class='flex flex-col items-center justify-center p-4'>";
    echo "<div class='bg-branco dark:bg-preto2 p-8 rounded-[2rem] shadow-lg text-center border border-cinzaMarrom/20 dark:border-branco/10'>";
    echo "<h2 class='font-shantell text-2xl font-bold text-text-dark dark:text-branco mb-4'>Erro: Animal não encontrado para exclusão.</h2>";
    echo '<a href="' . URL_BASE . '/animal" class="text-rosaAlerta hover:underline dark:text-rosa-1 font-bold">Voltar à lista</a>';
    echo "</div></main>";
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}
?>

<main class="flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-2xl flex flex-col items-center py-10 px-8 relative bg-branco dark:bg-fundoChat-escuro shadow-[0_8px_20px_rgba(0,0,0,0.12)] rounded-[2.5rem] border border-cinzaMarrom/20 dark:border-branco/10 transition-colors duration-300 text-center">
        
        <h1 class="font-shantell text-[32px] font-bold text-text-dark dark:text-branco mb-6 transition-colors duration-300">
            Desativar Animal
        </h1>
        
        <div class="bg-rosa-1/20 dark:bg-rosaAlerta/10 border-2 border-dashed border-rosaAlerta rounded-[1.5rem] p-6 mb-8 text-left max-w-xl w-full">
            <h2 class="text-rosaAlerta font-shantell font-bold text-xl flex items-center gap-2 mb-3">
                ⚠️ Atenção!
            </h2>
            <p class="font-poppins text-text-dark dark:text-branco/90 text-base leading-relaxed mb-3">
                Você tem certeza que deseja desativar o cadastro do animal <strong class="font-bold text-lg"><?= htmlspecialchars($animal->getNome()) ?></strong>?
            </p>
            <p class="font-poppins text-sm text-text-muted dark:text-branco/60 italic">
                Nota: Ele não será apagado definitivamente do banco de dados (exclusão lógica), mas ficará oculto das listas principais.
            </p>
        </div>

        <form action="<?= URL_BASE ?>/animal/excluir" method="POST">
            <input type="hidden" name="id" value="<?= $animal->getAnimalId() ?>">
            
            <button type="submit" class="px-8 py-4 bg-rosaAlerta hover:bg-rosa-2 text-white dark:hover:text-text-dark font-bold text-lg rounded-full shadow-md transition-all duration-300 hover:-translate-y-1">
                Sim, Desativar Animal
            </button>
        </form>

        <a href="<?= URL_BASE ?>/animal" class="inline-block mt-8 font-poppins text-base font-bold text-text-muted dark:text-branco/60 hover:text-text-dark dark:hover:text-branco transition-colors underline">
            Cancelar e Voltar
        </a>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>