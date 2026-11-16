<?php
require_once __DIR__ . '/../templates/header.php';
$rotulos = ['todos' => 'Todos', 'disponivel' => 'Disponíveis', 'em_analise' => 'Em análise', 'adotado' => 'Adotados', 'desativado' => 'Desativados'];
$statusAnimal = ['disponivel' => 'Disponível', 'em_analise' => 'Em análise', 'adotado' => 'Adotado', 'desativado' => 'Desativado'];
$fotoAnimal = static function (?string $foto): string {
    if (!$foto) return asset('assets/img/perfil-placeholder.png');
    return preg_match('#^https?://#i', $foto) ? $foto : rtrim(URL_BASE, '/') . '/' . ltrim($foto, '/');
};
echo renderVoltar('/admin/dashboard');
?>
<section class="mx-auto max-w-5xl px-4 py-4 pb-28 text-text-dark dark:text-white sm:px-6">
    <h1 class="font-shantell text-2xl font-bold sm:text-3xl">Gerenciar animais</h1>
    <p class="mt-2 text-sm text-primary dark:text-white/85">Consulte os anúncios de todas as ONGs e protetores e controle sua disponibilidade.</p>
    <form action="<?= e(URL_BASE) ?>/admin/animais" method="GET" class="my-6 grid gap-3 rounded-2xl border border-rosa-3 bg-surface p-4 shadow-sm dark:border-roxinhoFofo/60 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
        <label class="min-w-0">
            <span class="mb-1 block text-sm font-semibold">Animal ou responsável</span>
            <input name="busca" value="<?= e($busca) ?>" maxlength="150" class="input-padrao w-full" placeholder="Buscar por nome...">
        </label>
        <label>
            <span class="mb-1 block text-sm font-semibold">Status</span>
            <select name="status" class="input-padrao w-full">
                <?php foreach ($rotulos as $valor => $rotulo): ?>
                <option value="<?= e($valor) ?>" <?= $status === $valor ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn-primario min-h-11 self-end" type="submit">Filtrar</button>
        <a href="<?= e(URL_BASE) ?>/admin/animais" class="inline-flex min-h-11 items-center text-sm underline dark:text-white">Limpar filtros</a>
    </form>
    <p class="mb-4 font-poppins text-sm font-semibold" role="status"><?= (int) $total ?> <?= $total === 1 ? 'animal encontrado' : 'animais encontrados' ?></p>
    <?php if (!$animais): ?>
    <div class="rounded-2xl border border-rosa-3 bg-surface p-8 text-center">Nenhum animal encontrado. Experimente outros filtros.</div>
    <?php endif; ?>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($animais as $animal):
            $id = (int) $animal['animal_id'];
            $reativar = in_array($animal['status'], ['adotado', 'desativado'], true);
        ?>
        <article class="min-w-0 overflow-hidden rounded-2xl border border-rosa-3 bg-surface shadow-md dark:border-roxinhoFofo/60">
            <a href="<?= e(URL_BASE) ?>/animal/mostrar?id=<?= $id ?>" class="block" aria-label="Ver detalhes de <?= e($animal['nome']) ?>">
                <img src="<?= e($fotoAnimal($animal['foto'])) ?>" alt="<?= e($animal['nome']) ?>" loading="lazy" class="aspect-[4/3] w-full object-cover" data-fallback="<?= e(asset('assets/img/perfil-placeholder.png')) ?>" onerror="this.onerror=null;this.src=this.dataset.fallback">
            </a>
            <div class="space-y-3 p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="min-w-0 break-words font-shantell text-xl font-bold"><?= e($animal['nome']) ?></h2>
                    <span class="rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white"><?= e($statusAnimal[$animal['status']] ?? $animal['status']) ?></span>
                </div>
                <p class="text-sm"><?= e($animal['especie']) ?> · <?= e($animal['raca']) ?></p>
                <p class="break-words text-sm"><strong><?= $animal['tipo_documento'] === 'cnpj' ? 'ONG' : 'Protetor' ?>:</strong> <?= e($animal['responsavel']) ?></p>
                <p class="text-xs text-text-muted dark:text-white/80">Anúncio #<?= $id ?></p>
                <a href="<?= e(URL_BASE) ?>/animal/mostrar?id=<?= $id ?>" class="inline-flex min-h-11 items-center text-sm font-semibold underline dark:text-white">Ver detalhes</a>
                <?php foreach ($reativar ? ['reativar' => 'Reativar animal'] : ['desativar' => 'Desativar animal'] as $acao => $rotulo): ?>
                <form action="<?= e(URL_BASE . '/admin/animais/' . $acao) ?>" method="POST" data-confirmar-animal>
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button type="submit" class="min-h-11 w-full rounded-full border px-4 py-2 text-sm font-bold <?= $reativar ? 'border-primary bg-primary text-white' : 'border-rosaAlerta text-erro dark:text-rosa-2' ?>"
                        data-titulo="<?= e($rotulo) ?>"
                        data-mensagem="<?= $reativar ? 'O anúncio voltará a receber interessados. Se houver uma solicitação em análise, esse status será preservado.' : 'O anúncio sairá do catálogo. Os dados e as solicitações existentes serão preservados.' ?>"><?= e($rotulo) ?></button>
                </form>
                <?php endforeach; ?>
                <?php if ($animal['status'] === 'adotado'): ?>
                <p class="text-xs text-text-muted dark:text-white/80">Reative somente se o animal estiver novamente disponível para adoção.</p>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php if ($paginas > 1): ?>
    <nav class="mt-6 flex flex-wrap items-center justify-center gap-2" aria-label="Páginas de animais">
        <?php for ($numero = 1; $numero <= $paginas; $numero++):
            $query = http_build_query(['busca' => $busca, 'status' => $status, 'pagina' => $numero]);
        ?>
        <a href="<?= e(URL_BASE . '/admin/animais?' . $query) ?>" <?= $pagina === $numero ? 'aria-current="page"' : '' ?>
            class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-primary px-3 <?= $pagina === $numero ? 'bg-primary text-white' : 'dark:text-white' ?>"><?= $numero ?></a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
</section>
<script>
document.querySelectorAll('[data-confirmar-animal]').forEach(form => {
    form.addEventListener('submit', event => {
        if (typeof abrirModalConfirmacao !== 'function') {
            if (!window.confirm(form.querySelector('button').dataset.mensagem)) event.preventDefault();
            return;
        }
        event.preventDefault();
        const botao = form.querySelector('button');
        abrirModalConfirmacao(botao.dataset.titulo, botao.dataset.mensagem, () => {
            botao.disabled = true;
            form.submit();
        }, botao.textContent, 'Voltar');
    });
});
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>

