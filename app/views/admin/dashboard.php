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
    <h1 class="font-shantell text-2xl sm:text-3xl font-bold">Visão geral</h1>
    <p class="mt-2 mb-6 text-sm text-text-muted">Acompanhe os cadastros e cuide da comunidade CãoNectados.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <?php foreach($cards as $card): ?>
        <a href="<?= e(URL_BASE.$card['rota']) ?>" class="rounded-3xl border border-rosa-3 bg-surface p-5 shadow-sm hover:border-primary transition-colors">
            <div class="flex items-center justify-between gap-3"><span class="text-sm font-semibold"><?= e($card['nome']) ?></span><span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10"><?= renderIconeMenu($card['icone'],'','h-6 w-6 text-primary') ?></span></div>
            <p class="mt-4 font-shantell text-4xl font-bold"><?= $card['valor'] ?></p>
            <span class="mt-3 inline-flex min-h-11 items-center text-sm underline">Ver detalhes</span>
        </a>
        <?php endforeach; ?>
    </div>
    <h2 class="font-shantell text-xl font-bold mb-3">Atalhos de gestão</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <?php foreach($atalhos as $atalho): ?>
        <a href="<?= e(URL_BASE.$atalho['rota']) ?>" class="flex min-h-20 items-center gap-3 rounded-2xl border border-primary/20 bg-surface p-4 hover:bg-primary/10 transition-colors">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10"><?= renderIconeMenu($atalho['icone'],'','h-6 w-6 text-primary') ?></span>
            <span class="min-w-0"><strong class="block text-sm"><?= e($atalho['nome']) ?></strong><span class="mt-1 block text-xs text-text-muted"><?= e($atalho['texto']) ?></span></span>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__.'/../templates/footer.php'; ?>
