<?php
require_once __DIR__ . '/../templates/header.php';
$animais = $animais ?? [];
$rotulos = ['disponivel'=>'disponíveis','em_analise'=>'em análise','adotado'=>'adotados','desativado'=>'desativados'];
$filtro = is_string($_GET['status'] ?? null) ? $_GET['status'] : 'todos';
$grupos = [];
foreach ($animais as $animal) $grupos[$animal->getStatus()][] = $animal;
$admin = ($_SESSION['tipo_perfil'] ?? '') === 'administrador';
echo renderVoltar('/perfil');
?>
<section class="mx-auto max-w-5xl py-3 pb-28 sm:py-6 text-text-dark">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div><h1 class="font-shantell text-2xl sm:text-3xl font-bold"><?= $admin ? 'Animais cadastrados' : 'Meus animais' ?></h1>
            <p class="mt-1 text-sm text-text-muted">Cuide dos cadastros e acompanhe cada nova chance de adoção.</p></div>
        <a href="<?= e(URL_BASE) ?>/animal/cadastrar" class="btn-primario hidden sm:inline-flex min-h-11 items-center gap-2 text-sm">
            <?= renderIconeMenu('cadastrar-animal.svg','','h-5 w-5') ?> Novo animal
        </a>
    </div>
    <nav aria-label="Filtrar animais por status" class="flex flex-wrap gap-2 mb-6">
        <?php foreach (['todos'=>'Todos'] + $rotulos as $valor=>$rotulo): ?>
        <a href="<?= e(URL_BASE) ?>/animal?status=<?= e($valor) ?>" <?= $filtro === $valor ? 'aria-current="page"' : '' ?> class="inline-flex min-h-11 items-center rounded-full border border-primary/30 px-4 text-sm font-medium <?= $filtro === $valor ? 'bg-primary text-white' : 'bg-surface text-text-dark hover:bg-primary/10' ?>"><?= e(ucfirst($rotulo)) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php if (!$animais): ?>
    <div class="rounded-3xl border border-rosa-3 bg-surface py-12 px-5 text-center">
        <img src="<?= e(asset('assets/icons/geral/patinha-coracao.svg')) ?>" alt="" class="mx-auto h-12 w-12 mb-3">
        <h2 class="font-shantell text-xl font-bold">Nenhum animal nesta categoria</h2>
        <p class="mt-2 text-sm text-text-muted">Cadastre um animal ou escolha outro status para continuar.</p>
    </div>
    <?php endif; ?>
    <?php foreach ($rotulos as $estado=>$rotulo): if (empty($grupos[$estado])) continue; ?>
    <section class="mb-8" aria-labelledby="animais-<?= e($estado) ?>">
        <div class="mb-3 flex items-center justify-center sm:justify-start gap-2"><h2 id="animais-<?= e($estado) ?>" class="font-poppins text-lg font-medium"><?= $admin ? 'Animais ' : 'Meus animais ' ?><?= e($rotulo) ?></h2><span class="rounded-full bg-primary/10 px-2 py-1 text-xs"><?= count($grupos[$estado]) ?></span></div>
        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-px sm:gap-3">
            <?php foreach ($grupos[$estado] as $animal): $id=(int)$animal->getAnimalId(); ?>
            <article class="min-w-0 overflow-hidden bg-surface sm:rounded-2xl sm:border sm:border-rosa-3/50">
                <div class="relative aspect-[132/171] overflow-hidden bg-primary/10">
                    <a href="<?= e(URL_BASE) ?>/animal/mostrar?id=<?= $id ?>" class="block h-full" aria-label="Ver <?= e($animal->getNome()) ?>">
                        <img src="<?= e(rtrim(URL_BASE,'/').'/'.ltrim($animal->getFotoPrincipal() ?: 'assets/img/perfil-placeholder.png','/')) ?>" alt="<?= e($animal->getNome()) ?>" loading="lazy" class="h-full w-full object-cover">
                    </a>
                    <div class="absolute inset-x-0 bottom-0 flex min-h-11 items-center justify-between gap-1 bg-primary px-1 text-white">
                        <h3 class="min-w-0 truncate font-shantell text-base sm:text-lg font-bold"><?= e($animal->getNome()) ?></h3>
                        <a href="<?= e(URL_BASE) ?>/animal/editar?id=<?= $id ?>" aria-label="Editar <?= e($animal->getNome()) ?>" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-white">
                            <img src="<?= e(asset('assets/icons/geral/editar-animal.svg')) ?>" alt="">
                        </a>
                    </div>
                </div>
                <div class="p-1 sm:p-3">
                    <p class="hidden sm:block truncate text-xs text-text-muted"><?= e($animal->getRacaNome() ?: 'Raça não informada') ?></p>
                    <?php if (in_array($estado,['adotado','desativado'],true)): ?>
                    <form action="<?= e(URL_BASE) ?>/animal/reativar" method="POST" class="mt-2" onsubmit="if (typeof abrirModalConfirmacao === 'function') { abrirModalConfirmacao('Reativar animal', 'O animal voltará a ficar disponível para novas adoções.', () => this.submit(), 'Reativar', 'Voltar'); return false; }">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="min-h-11 w-full rounded-xl bg-primary px-1 py-2 text-[11px] sm:text-xs font-semibold text-white hover:opacity-90">Reativar animal</button>
                    </form>
                    <?php else: ?>
                    <a href="<?= e(URL_BASE) ?>/animal/excluir?id=<?= $id ?>" class="hidden sm:flex mt-2 min-h-11 items-center justify-center rounded-xl text-xs text-erro underline">Desativar</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
</section>
<a href="<?= e(URL_BASE) ?>/animal/cadastrar" aria-label="Novo animal" class="fixed right-4 z-20 flex items-center justify-center rounded-full bg-accent shadow-lg sm:hidden" style="bottom:calc(5rem + env(safe-area-inset-bottom))"><img src="<?= e(asset('assets/icons/geral/adicionar-animal.svg')) ?>" alt=""></a>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
