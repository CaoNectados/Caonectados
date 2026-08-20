<?php 
$statusAtual = $statusAtual ?? 'pendentes';
require_once __DIR__ . '/../templates/header.php'; 
?>

<div class="min-h-screen bg-background text-text-dark p-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-figma bg-surface rounded-3xl md:rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20 transition-colors">
        
        <!-- Abas de Status com Estilo Figma Neo-brutalista -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-8">
            <a href="<?= URL_BASE ?>/admin/solicitacoes?status=pendentes" 
               class="py-3 px-4 text-center font-poppins font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition-all active:scale-95 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] <?= $statusAtual === 'pendentes' ? 'bg-laranja-1 text-white' : 'bg-surface text-text-dark hover:bg-laranja-1/20' ?>">
               Pendentes
            </a>
            <a href="<?= URL_BASE ?>/admin/solicitacoes?status=aprovados" 
               class="py-3 px-4 text-center font-poppins font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition-all active:scale-95 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] <?= $statusAtual === 'aprovados' ? 'bg-verdeMusgo text-white' : 'bg-surface text-text-dark hover:bg-verdeMusgo/20' ?>">
               Aprovados
            </a>
            <a href="<?= URL_BASE ?>/admin/solicitacoes?status=recusados" 
               class="py-3 px-4 text-center font-poppins font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition-all active:scale-95 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] <?= $statusAtual === 'recusados' ? 'bg-erro text-white' : 'bg-surface text-text-dark hover:bg-erro/20' ?>">
               Recusados
            </a>
        </div>

        <!-- Barra de Pesquisa Integrada -->
        <div class="mb-8">
            <form method="GET" action="<?= URL_BASE ?>/admin/solicitacoes" class="relative flex items-center">
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusAtual) ?>">
                <input type="text" 
                       id="inputBusca"
                       name="busca" 
                       value="<?= htmlspecialchars($busca ?? '') ?>"
                       placeholder="Buscar por nome da ONG, CNPJ ou cidade..." 
                       class="w-full bg-branco dark:bg-preto2 border-2 border-preto dark:border-cinzaMarrom rounded-2xl pl-5 pr-14 py-3.5 text-sm sm:text-base text-text-dark placeholder:text-text-muted focus:outline-none focus:border-primary transition">
                <button type="submit" aria-label="Buscar" class="absolute right-2.5 top-2.5 bottom-2.5 w-11 bg-preto dark:bg-primary text-white rounded-xl flex items-center justify-center hover:opacity-90 transition">
                    <?= icone('search', 'h-4 w-4') ?>
                </button>
            </form>
            <p class="text-xs text-text-muted mt-2 ml-1">Dica: digite para filtrar a lista</p>
        </div>

        <!-- Título da Seção -->
        <div class="mb-6">
            <h2 class="font-shantell text-2xl font-bold text-text-dark tracking-tight">Solicitações de ONGs</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5">Clique em um card para ver documentos e fotos</p>
        </div>

        <!-- Lista de Solicitações -->
        <div id="containerSolicitacoes" class="divide-y divide-cinzaMarrom/20">
            <?php if (empty($solicitacoes)): ?>
                <div class="py-16 text-center">
                    <span class="flex justify-center mb-3 opacity-40"><?= icone('folder', 'h-14 w-14') ?></span>
                    <p class="text-text-muted text-base font-semibold">Nenhuma solicitação encontrada nesta categoria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($solicitacoes as $solic): ?>
                    <?php 
                        $fotoNome = $solic['foto_perfil'] ?? null;
                        $caminhoFoto = null;

                        if (!empty($fotoNome)) {
                            $fotoLimpa = trim($fotoNome);
                            if (strpos($fotoLimpa, 'http') === 0) {
                                $caminhoFoto = $fotoLimpa;
                            } else {
                                $fotoLimpa = ltrim($fotoLimpa, '/');
                                $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
                                $caminhoFoto = URL_BASE . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
                            }
                        }
                    ?>
                    <div class="card-solicitacao py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-rosa-1/20 dark:hover:bg-preto2/50 transition px-3 sm:px-4 rounded-2xl">
                        <!-- Identificação -->
                        <div class="flex items-center gap-4">
                            <?php if ($caminhoFoto): ?>
                                <img src="<?= $caminhoFoto ?>" 
                                     alt="Perfil" 
                                     class="w-12 h-12 rounded-full object-cover border-2 border-rosa-3 dark:border-preto3 shrink-0 shadow-sm bg-white"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-12 h-12 rounded-full bg-rosa-1 dark:bg-preto2 border-2 border-rosa-3 dark:border-preto3 flex items-center justify-center text-primary dark:text-roxinhoFofo shrink-0 shadow-sm\'>' + <?= e(json_encode(icone('home', 'h-6 w-6'))) ?> + '</div>';">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-full bg-rosa-1 dark:bg-preto2 border-2 border-rosa-3 dark:border-preto3 flex items-center justify-center text-primary dark:text-roxinhoFofo shrink-0 shadow-sm">
                                    <?= icone('home', 'h-6 w-6') ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <h3 class="nome-ong text-base sm:text-lg font-bold text-text-dark leading-snug">
                                    <?= htmlspecialchars($solic['nome_fantasia'] ?: ($solic['usuario_nome'] ?? 'ONG')) ?>
                                </h3>
                                <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                                    Data da solicitação: <strong class="text-text-dark"><?= !empty($solic['criado_em']) ? date('d/m/Y', strtotime($solic['criado_em'])) : '-' ?></strong> • Status: 
                                    <span class="font-bold <?= ($solic['status'] ?? '') === 'aprovado' ? 'text-sucesso' : (($solic['status'] ?? '') === 'recusado' ? 'text-erro' : 'text-laranja-1') ?>">
                                        <?= ($solic['status'] ?? '') === 'aprovado' ? 'Aprovado' : (($solic['status'] ?? '') === 'recusado' ? 'Recusado' : 'Aguardando Análise') ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Link de Ação -->
                        <div class="flex items-center justify-end gap-3 self-end sm:self-center">
                            <a href="<?= URL_BASE ?>/admin/solicitacoes/detalhes?id=<?= $solic['protetor_id'] ?? '' ?>" 
                               class="text-xs sm:text-sm font-bold text-primary dark:text-roxinhoFofo underline hover:text-accent transition">
                                Clique para detalhes
                            </a>
                            <span class="text-text-muted select-none"><?= icone('document', 'h-4 w-4') ?></span>
                            <a href="<?= URL_BASE ?>/admin/solicitacoes/detalhes?id=<?= $solic['protetor_id'] ?? '' ?>"
                               class="w-8 h-8 rounded-full bg-rosa-1/40 dark:bg-preto2 flex items-center justify-center text-text-muted hover:text-text-dark dark:hover:text-white transition">
                                <?= icone('search', 'h-3.5 w-3.5') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    document.getElementById('inputBusca')?.addEventListener('input', function(e) {
        const termo = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.card-solicitacao');
        cards.forEach(card => {
            const texto = card.innerText.toLowerCase();
            card.style.display = texto.includes(termo) ? 'flex' : 'none';
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>