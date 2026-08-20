<?php
require_once __DIR__ . '/../templates/header.php';

/** @var \app\models\Animal|null $animal */
$animal = $animal ?? null;

if (!$animal) {
    echo "<main class='flex flex-col items-center justify-center p-4'>";
    echo "<div class='bg-branco dark:bg-preto2 p-8 rounded-[2rem] shadow-lg text-center border border-cinzaMarrom/20 dark:border-branco/10'>";
    echo "<h2 class='font-shantell text-2xl font-bold text-text-dark dark:text-branco mb-4'>Erro: Animal não encontrado.</h2>";
    echo '<a href="' . URL_BASE . '/animal" class="text-rosaAlerta hover:underline dark:text-rosa-1 font-bold">Voltar</a>';
    echo "</div></main>";
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}

$statusLabels = [
    'disponivel' => 'Disponível',
    'em_analise' => 'Em Análise',
    'adotado'    => 'Adotado',
    'desativado' => 'Desativado',
];

$tipoPerfilSessao = $_SESSION['tipo_perfil'] ?? '';
$podeEditar = ($tipoPerfilSessao === 'administrador')
    || ((int) ($_SESSION['protetor_id'] ?? 0) > 0 && (int) $_SESSION['protetor_id'] === (int) $animal->getProtetorId());
?>

<main class="flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-2xl flex flex-col py-10 px-8 relative bg-branco dark:bg-fundoChat-escuro shadow-[0_8px_20px_rgba(0,0,0,0.12)] rounded-[2.5rem] border border-cinzaMarrom/20 dark:border-branco/10 transition-colors duration-300">

        <h1 class="font-shantell text-[32px] font-bold text-text-dark dark:text-branco mb-8 transition-colors duration-300">
            <?= htmlspecialchars($animal->getNome()) ?>
        </h1>

        <?php if (!empty($animal->getFotoPrincipal())): ?>
            <img src="<?= URL_BASE ?>/<?= htmlspecialchars($animal->getFotoPrincipal()) ?>" alt="Foto de <?= htmlspecialchars($animal->getNome()) ?>" class="w-full max-h-80 object-cover rounded-2xl mb-6 border-2 border-cinzaMarrom/20 dark:border-branco/10">
        <?php endif; ?>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 font-poppins text-text-dark dark:text-branco/90">
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Raça</dt>
                <dd><?= htmlspecialchars($animal->getRacaNome() ?? 'Não informada') ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Status</dt>
                <dd class="capitalize"><?= htmlspecialchars($statusLabels[$animal->getStatus()] ?? $animal->getStatus()) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Sexo</dt>
                <dd class="capitalize"><?= htmlspecialchars($animal->getSexo()) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Porte</dt>
                <dd class="capitalize"><?= htmlspecialchars($animal->getPorte()) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Data de nascimento</dt>
                <dd><?= htmlspecialchars($animal->getDtNasc() ?? 'Não informada') ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Vacinado / Castrado</dt>
                <dd><?= $animal->isVacinado() ? 'Vacinado' : 'Não vacinado' ?> · <?= $animal->isCastrado() ? 'Castrado' : 'Não castrado' ?></dd>
            </div>
            <?php if (!empty($animal->getComportamento())): ?>
            <div>
                <dt class="text-xs uppercase font-bold opacity-60">Comportamento</dt>
                <dd class="capitalize"><?= htmlspecialchars($animal->getComportamento()) ?></dd>
            </div>
            <?php endif; ?>
        </dl>

        <?php if (!empty($animal->getHistoricoSaude())): ?>
        <div class="mt-6">
            <h2 class="text-xs uppercase font-bold opacity-60 mb-1">Saúde</h2>
            <p class="font-poppins text-text-dark dark:text-branco/90 whitespace-pre-line"><?= htmlspecialchars($animal->getHistoricoSaude()) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($animal->getDescricao())): ?>
        <div class="mt-6">
            <h2 class="text-xs uppercase font-bold opacity-60 mb-1">Sobre</h2>
            <p class="font-poppins text-text-dark dark:text-branco/90 whitespace-pre-line"><?= htmlspecialchars($animal->getDescricao()) ?></p>
        </div>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row items-center gap-4 mt-10">
            <?php if ($podeEditar): ?>
                <a href="<?= URL_BASE ?>/animal/editar?id=<?= $animal->getAnimalId() ?>" class="w-full sm:w-auto text-center px-8 py-3 bg-rosaAlerta hover:bg-rosa-2 text-white dark:hover:text-text-dark font-bold rounded-full shadow-md transition-all duration-300 hover:-translate-y-1">
                    Editar
                </a>
            <?php endif; ?>
            <a href="<?= URL_BASE ?>/<?= $podeEditar ? 'animal' : 'feed' ?>" class="inline-flex items-center gap-1.5 font-poppins text-sm font-medium text-text-muted dark:text-branco/60 hover:text-rosaAlerta dark:hover:text-branco transition-colors underline">
                <?= icone('arrow-left', 'h-4 w-4') ?> Voltar
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
