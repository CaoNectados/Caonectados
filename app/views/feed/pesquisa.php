<?php
require_once __DIR__ . '/../templates/header.php';
$urlBase = rtrim(URL_BASE, '/');
$retornoPesquisa = '/pesquisar?' . http_build_query(array_filter($filtrosAtuais, fn($valor) => $valor !== null && $valor !== ''));
?>
<section id="pesquisa" class="mx-auto max-w-4xl py-3 sm:py-6" data-usuario="<?= (int)$_SESSION['usuario_id'] ?>" data-base="<?= e($urlBase) ?>" data-filtros="<?= e(json_encode($filtrosAtuais)) ?>" data-offset="<?= (int)$proximoOffset ?>">
    <h1 class="sr-only">Pesquisa de animais e ONGs</h1>
    <div class="sticky top-0 z-10 bg-background pb-2">
        <form id="form-pesquisa" action="<?= e($urlBase) ?>/pesquisar" method="GET" role="search" class="flex min-h-11 items-center gap-2 rounded-full bg-primary px-3 text-white shadow-sm">
            <button type="submit" aria-label="Pesquisar" class="flex h-11 w-8 shrink-0 items-center justify-center rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-white">
                <?= renderIconeMenu('pesquisar.svg', '', 'h-5 w-5 text-white') ?>
            </button>
            <label for="termo-pesquisa" class="sr-only">Pesquisar animais e ONGs</label>
            <input type="search" name="q" id="termo-pesquisa" value="<?= e($filtrosAtuais['q'] ?? '') ?>" maxlength="100" placeholder="Pesquisar" autocomplete="off" class="min-w-0 flex-1 bg-transparent py-2 text-sm text-white placeholder:text-white/90 focus:outline-none" aria-controls="historico-pesquisa" aria-expanded="false">
            <?php foreach ($filtrosAtuais as $nome => $valor): if ($nome !== 'q' && $valor !== null && $valor !== ''): ?>
                <input type="hidden" name="<?= e($nome) ?>" value="<?= e((string)$valor) ?>">
            <?php endif; endforeach; ?>
            <button type="button" id="abrir-filtros-pesquisa" aria-haspopup="dialog" aria-controls="modal-filtros-pesquisa" class="flex min-h-11 shrink-0 items-center gap-1 rounded-full px-1 text-xs sm:text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-white">
                Filtros <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/><circle cx="8" cy="6" r="2" fill="currentColor"/><circle cx="16" cy="12" r="2" fill="currentColor"/><circle cx="10" cy="18" r="2" fill="currentColor"/></svg>
            </button>
        </form>
    </div>
    <div id="historico-pesquisa" class="hidden min-h-[55vh] px-3 py-2" aria-label="Pesquisas recentes">
        <div class="mb-2 flex items-center justify-between gap-2"><h2 class="text-xs text-text-dark/70">Pesquisas recentes</h2><button type="button" id="limpar-historico-pesquisa" class="min-h-11 text-xs text-text-dark underline">Limpar histórico</button></div>
        <ul id="lista-historico-pesquisa" class="space-y-1"></ul>
        <p id="historico-vazio-pesquisa" class="hidden text-sm text-text-dark/70">Suas pesquisas recentes aparecerão aqui.</p>
    </div>
    <div id="resultados-pesquisa">
        <?php if ($ongs): ?>
        <section class="mb-3" aria-labelledby="titulo-ongs-pesquisa">
            <h2 id="titulo-ongs-pesquisa" class="mb-1 text-sm font-medium text-text-dark">ONGs</h2>
            <div class="max-w-md space-y-2">
                <?php foreach ($ongs as $ong): ?>
                <a href="<?= e($urlBase) ?>/pagina?id=<?= (int)$ong['protetor_id'] ?>" class="flex min-h-11 items-center gap-2 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" aria-label="Ir para página de <?= e($ong['nome_fantasia']) ?>">
                    <img src="<?= e($urlBase . '/' . ltrim($ong['foto_perfil'] ?: 'assets/img/perfil-placeholder.png','/')) ?>" alt="" class="h-10 w-10 shrink-0 rounded-full border border-rosa-3 bg-surface object-cover">
                    <span class="flex min-w-0 flex-1 items-center justify-between gap-2 rounded-full bg-preto2 px-3 py-1 text-xs text-white"><span class="truncate font-medium"><?= e($ong['nome_fantasia']) ?></span><span class="shrink-0">Ir para Página ›</span></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        <h2 class="mb-2 text-center text-sm font-medium text-text-dark">Animais pesquisados</h2>
        <div id="grade-pesquisa" class="grid grid-cols-3 gap-px overflow-hidden rounded-lg bg-surface sm:grid-cols-4 lg:grid-cols-6">
            <?php foreach ($animais as $animal):
                $foto = $animal['fotos'][0]['caminho_foto'] ?? $animal['foto_principal'] ?? 'assets/img/perfil-placeholder.png';
                if (!$foto) $foto = 'assets/img/perfil-placeholder.png';
            ?>
            <a class="resultado-animal group relative aspect-[3/4] overflow-hidden bg-surface focus-visible:outline focus-visible:outline-2 focus-visible:outline-accent" data-animal-id="<?= (int)$animal['animal_id'] ?>" href="<?= e($urlBase) ?>/animal/mostrar?id=<?= (int)$animal['animal_id'] ?>&amp;retorno=<?= e(rawurlencode($retornoPesquisa)) ?>" aria-label="Ver <?= e($animal['nome']) ?>, <?= e($animal['especie_nome']) ?>">
                <img src="<?= e($urlBase . '/' . ltrim($foto, '/')) ?>" alt="<?= e($animal['nome']) ?>" loading="lazy" class="h-full w-full object-cover transition-transform group-hover:scale-105 motion-reduce:transition-none">
                <span class="absolute inset-x-0 bottom-0 hidden truncate bg-black/55 px-1 py-1 text-xs text-white sm:block sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus:opacity-100"><?= e($animal['nome']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (!$animais): ?>
            <div class="rounded-xl border border-rosa-3 bg-surface px-4 py-10 text-center"><p class="font-medium text-text-dark">Nenhum animal encontrado.</p><p class="mt-2 text-sm text-text-dark/70">Tente outro termo ou ajuste os filtros.</p><a href="<?= e($urlBase) ?>/pesquisar" class="mt-4 inline-flex min-h-11 items-center text-sm text-text-dark underline">Limpar pesquisa e filtros</a></div>
        <?php endif; ?>
        <p id="status-pesquisa" role="status" aria-live="polite" class="py-3 text-center text-sm text-text-dark"></p>
        <?php if ($temMais): ?><button type="button" id="mais-pesquisa" class="btn-primario mx-auto mb-4 flex">Carregar mais animais</button><?php endif; ?>
    </div>
</section>
<?php $idModalFiltros = 'modal-filtros-pesquisa'; $destinoFiltros = '/pesquisar'; require __DIR__.'/filtros.php'; ?>
<script src="<?= e(asset('assets/js/pesquisa.js')) ?>" defer></script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
