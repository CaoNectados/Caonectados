<?php require __DIR__ . '/../templates/header.php'; ?>
<?php
$classificacaoDisponivel = array_key_exists('inadimplente', $protetor);
$estaInadimplente = !empty($protetor['inadimplente']);
$podeClassificar = $classificacaoDisponivel && !empty($protetor['validado']) && empty($protetor['deletado_em']);
$historico = $historico ?? [];
?>

<div class="mx-auto w-full max-w-2xl py-6 sm:py-10">
    <nav class="mb-4" aria-label="Navegação da classificação">
        <a href="<?= URL_BASE ?>/admin/gerenciar-usuarios"
            class="inline-flex min-h-11 items-center gap-2 rounded-full px-3 font-poppins font-semibold text-primary transition hover:bg-rosa-1 dark:text-white dark:hover:bg-preto2">
            <img src="<?= URL_BASE ?>/assets/icons/geral/seta-voltar.svg" alt="" class="h-6 w-7 dark:brightness-0 dark:invert">
            <span>Voltar para usuários</span>
        </a>
    </nav>

    <main class="card-padrao rounded-3xl border border-rosa-2 bg-rosaClaro2 p-6 shadow-lg dark:border-roxinhoFofo/60 dark:bg-surface sm:p-8">
        <header class="mb-6 text-center">
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">
                Classificação do protetor
            </h1>
            <p class="mt-2 font-poppins text-lg font-semibold text-primary dark:text-roxinhoFofo">
                <?= htmlspecialchars($protetor['nome_fantasia'] ?? 'Perfil', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php if ($classificacaoDisponivel): ?>
                <span class="mt-3 inline-flex rounded-full px-4 py-1 text-sm font-bold <?= $estaInadimplente ? 'bg-rosaAlerta/15 text-rosaAlerta' : 'bg-sucesso/15 text-sucesso' ?>">
                    Situação atual: <?= $estaInadimplente ? 'Inadimplente' : 'Regular' ?>
                </span>
            <?php endif; ?>
        </header>

        <p class="mb-6 rounded-2xl border border-rosa-2/70 bg-white p-4 text-center text-sm leading-relaxed text-text-dark dark:border-preto3 dark:bg-preto1 dark:text-white">
            Registre se o perfil está regular ou inadimplente e informe a justificativa. A decisão fica no histórico administrativo e não bloqueia a conta; o bloqueio global é uma ação separada.
        </p>

        <?php if (!$classificacaoDisponivel): ?>
            <p role="status" class="mb-5 rounded-xl border border-rosaAlerta/40 bg-rosaAlerta/10 p-3 text-sm text-text-dark dark:text-white">
                A classificação estará disponível após atualizar o banco de dados com o scripts.sql.
            </p>
        <?php endif; ?>

        <?php if (!$podeClassificar && $classificacaoDisponivel): ?>
            <p class="mb-5 rounded-xl border border-rosa-3 bg-surface p-4 text-sm text-text-dark dark:text-white">A classificação pode ser registrada depois da aprovação deste perfil.</p>
        <?php endif; ?>

        <?php if (!empty($protetor['motivo_inadimplencia'])): ?>
            <div class="mb-6 rounded-2xl border border-rosa-3 bg-surface p-4 text-sm text-text-dark dark:border-roxinhoFofo/60 dark:text-white">
                <h2 class="mb-2 font-poppins font-bold">Justificativa atual</h2>
                <p class="whitespace-pre-wrap break-words"><?= e($protetor['motivo_inadimplencia']) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= URL_BASE ?>/admin/protetores/classificacao" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="usuario_id" value="<?= (int) $usuarioId ?>">

            <div>
                <label for="classificacao" class="mb-1 block font-poppins text-sm font-semibold text-text-dark dark:text-white">Classificação</label>
                <select name="inadimplente" id="classificacao" class="input-padrao w-full" <?= !$podeClassificar ? 'disabled' : '' ?>>
                    <option value="0" <?= !$estaInadimplente ? 'selected' : '' ?>>Regular</option>
                    <option value="1" <?= $estaInadimplente ? 'selected' : '' ?>>Inadimplente</option>
                </select>
            </div>

            <div>
                <label for="motivo" class="mb-1 block font-poppins text-sm font-semibold text-text-dark dark:text-white">Justificativa</label>
                <textarea name="motivo" id="motivo" required maxlength="2000" rows="4" class="input-padrao w-full" placeholder="Explique o motivo desta decisão, inclusive ao voltar para Regular." <?= !$podeClassificar ? 'disabled' : '' ?>></textarea>
                <p class="mt-1 text-sm text-text-dark dark:text-white/85">Obrigatória em todas as decisões, inclusive na regularização. Até 2.000 caracteres.</p>
            </div>

            <div class="flex justify-center pt-2">
                <button class="btn-primario min-h-11 w-full sm:w-auto sm:min-w-48 disabled:opacity-50" type="submit" <?= !$podeClassificar ? 'disabled' : '' ?>>Registrar decisão</button>
            </div>
        </form>
    </main>

    <section class="mt-6 rounded-3xl border border-rosa-3 bg-surface p-5 text-text-dark shadow-sm dark:border-roxinhoFofo/60 dark:text-white sm:p-8" aria-labelledby="titulo-historico-classificacao">
        <h2 id="titulo-historico-classificacao" class="font-shantell text-xl font-bold">Histórico de decisões</h2>
        <p class="mt-2 text-sm text-primary dark:text-white/85">As decisões anteriores são preservadas, mesmo após a regularização.</p>
        <?php if (!$historico): ?>
            <p class="mt-4 rounded-xl border border-rosa-3 p-4 text-sm dark:border-roxinhoFofo/40">Nenhuma decisão registrada. A situação inicial do perfil é Regular.</p>
        <?php else: ?>
            <ol class="mt-5 space-y-4">
                <?php foreach ($historico as $decisao): ?>
                <li class="rounded-2xl border border-rosa-3 bg-background p-4 dark:border-roxinhoFofo/40">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <strong class="rounded-full bg-primary px-3 py-1 text-sm text-white"><?= $decisao['inadimplente'] ? 'Inadimplente' : 'Regular' ?></strong>
                        <time datetime="<?= e(str_replace(' ', 'T', $decisao['criado_em'])) ?>" class="text-xs font-medium"><?= e((new DateTimeImmutable($decisao['criado_em']))->format('d/m/Y H:i')) ?></time>
                    </div>
                    <p class="mt-3 whitespace-pre-wrap break-words text-sm"><?= e($decisao['motivo']) ?></p>
                    <p class="mt-3 break-words text-xs"><strong>Registrado por:</strong> <?= e($decisao['administrador']) ?></p>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php if ($paginasHistorico > 1): ?>
            <nav class="mt-5 flex flex-wrap justify-center gap-2" aria-label="Páginas do histórico">
                <?php for ($pagina = 1; $pagina <= $paginasHistorico; $pagina++): ?>
                <a href="<?= e(URL_BASE . '/admin/protetores/classificacao?' . http_build_query(['id' => $usuarioId, 'pagina' => $pagina])) ?>" <?= $pagina === $paginaHistorico ? 'aria-current="page"' : '' ?> class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-primary px-3 dark:border-roxinhoFofo <?= $pagina === $paginaHistorico ? 'bg-primary text-white' : 'dark:text-white' ?>"><?= $pagina ?></a>
                <?php endfor; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
