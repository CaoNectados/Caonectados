<?php
require_once __DIR__.'/../templates/header.php';
$stats=$stats ?? [];
$cards=[
    ['nome'=>'Usuários ativos','valor'=>(int)($stats['usuarios_ativos'] ?? 0),'icone'=>'usuarios.svg','rota'=>'/admin/gerenciar-usuarios'],
    ['nome'=>'Cadastros pendentes','valor'=>(int)($stats['cadastros_pendentes'] ?? 0),'icone'=>'auditoria.svg','rota'=>'/admin/solicitacoes?status=pendentes'],
];
$atalhos=[['nome'=>'Gerenciar usuários','texto'=>'Contas, perfis e permissões','icone'=>'usuarios.svg','rota'=>'/admin/gerenciar-usuarios'],['nome'=>'Validar cadastros','texto'=>'Analise documentos de protetores e ONGs','icone'=>'auditoria.svg','rota'=>'/admin/solicitacoes'],['nome'=>'Regiões','texto'=>'Bairros e áreas de atuação','icone'=>'bairros.svg','rota'=>'/admin/regiao'],['nome'=>'Espécies e raças','texto'=>'Categorias disponíveis nos cadastros','icone'=>'especies.svg','rota'=>'/admin/gerenciar-especies-racas']];
?>
<section class="mx-auto max-w-4xl py-6 text-text-dark">
    <div class="mb-6 flex items-center justify-between gap-4 overflow-hidden rounded-3xl border border-rosa-3/50 bg-gradient-to-r from-rosa-1/60 via-surface to-surface px-5 py-4 shadow-sm dark:border-roxinhoFofo/50 dark:from-background dark:via-primary dark:to-surface sm:px-6 sm:py-5">
        <div class="min-w-0">
            <p class="mb-1 font-poppins text-[11px] font-bold uppercase tracking-[0.18em] text-primary dark:text-roxinhoFofo">Painel administrativo</p>
            <h1 class="font-shantell text-2xl font-bold sm:text-3xl">Visão geral</h1>
            <p class="mt-2 max-w-xl text-sm text-text-muted dark:text-white/80">Acompanhe os cadastros e cuide da comunidade CãoNectados.</p>
        </div>
        <div aria-hidden="true" class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-white/75 p-2 ring-1 ring-rosa-3/40 shadow-md dark:bg-white/10 dark:ring-roxinhoFofo/50 sm:h-28 sm:w-28 sm:p-3">
            <img src="<?= e(URL_BASE) ?>/assets/img/logo.png" alt="" class="h-full w-full object-contain transition-transform duration-300 hover:scale-110">
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <?php foreach($cards as $card): ?>
        <a href="<?= e(URL_BASE.$card['rota']) ?>" class="rounded-3xl border border-rosa-3 bg-surface p-5 shadow-md transition-all hover:border-primary hover:shadow-lg dark:shadow-[0_0_0_1px_rgba(255,255,255,0.08),0_8px_24px_rgba(139,130,184,0.28)] dark:hover:shadow-[0_0_0_1px_rgba(232,227,255,0.35),0_14px_30px_rgba(139,130,184,0.42)]">
            <div class="flex items-center justify-between gap-3"><span class="font-poppins text-base font-bold text-text-dark dark:text-white"><?= e($card['nome']) ?></span><span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 dark:bg-roxo1/35 dark:ring-1 dark:ring-roxinhoFofo/60"><?= renderIconeMenu($card['icone'],'','h-6 w-6 text-primary dark:text-white') ?></span></div>
            <p class="mt-4 font-poppins text-4xl font-extrabold text-text-dark dark:text-white"><?= $card['valor'] ?></p>
            <span class="mt-3 inline-flex min-h-11 items-center font-poppins text-sm font-semibold text-text-dark underline dark:text-white">Ver detalhes</span>
        </a>
        <?php endforeach; ?>
    </div>
    <h2 class="font-shantell text-xl font-bold mb-3">Atalhos de gestão</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <?php foreach($atalhos as $atalho): ?>
        <?php $classesIconeAtalho = $atalho['icone'] === 'especies.svg' ? 'h-6 w-6 text-primary dark:brightness-0 dark:invert' : 'h-6 w-6 text-primary dark:text-white'; ?>
        <a href="<?= e(URL_BASE.$atalho['rota']) ?>" class="flex min-h-20 items-center gap-3 rounded-2xl border border-primary/20 bg-surface p-4 shadow-md transition-all hover:border-primary hover:bg-primary/10 hover:shadow-lg dark:border-roxinhoFofo/45 dark:shadow-[0_0_0_1px_rgba(255,255,255,0.08),0_8px_24px_rgba(139,130,184,0.28)] dark:hover:border-roxinhoFofo/80 dark:hover:shadow-[0_0_0_1px_rgba(232,227,255,0.35),0_14px_30px_rgba(139,130,184,0.42)]">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-roxo1/40 dark:ring-1 dark:ring-roxinhoFofo/60"><?= renderIconeMenu($atalho['icone'],'',$classesIconeAtalho) ?></span>
            <span class="min-w-0"><strong class="block font-poppins text-sm font-bold text-text-dark dark:text-white"><?= e($atalho['nome']) ?></strong><span class="mt-1 block font-poppins text-xs font-medium text-text-muted dark:text-white/85"><?= e($atalho['texto']) ?></span></span>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__.'/../templates/footer.php'; ?>
