<?php
$abaAtual = $abaAtual ?? 'pendentes';
$solicitacoes = $solicitacoes ?? [];
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

function montarUrlFotoSolicitacao(?string $caminho, string $urlBase): ?string
{
    if (empty($caminho)) {
        return null;
    }
    return $urlBase . '/' . ltrim($caminho, '/');
}

$statusLabels = [
    'pendente'   => ['texto' => 'Aguardando Análise', 'cor' => 'text-laranja-1'],
    'em_analise' => ['texto' => 'Aguardando Análise', 'cor' => 'text-laranja-1'],
    'aprovada'   => ['texto' => 'Aprovada', 'cor' => 'text-sucesso'],
    'reprovada'  => ['texto' => 'Recusada', 'cor' => 'text-erro'],
    'cancelada'  => ['texto' => 'Cancelada', 'cor' => 'text-text-muted'],
];
?>

<div class="min-h-screen bg-background text-text-dark p-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-figma bg-surface rounded-3xl md:rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20 transition-colors">

        <!-- Abas -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-8">
            <a href="<?= $urlBase ?>/solicitacoes?aba=pendentes"
               class="py-3 px-4 text-center font-poppins font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition-all active:scale-95 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] <?= $abaAtual === 'pendentes' ? 'bg-laranja-1 text-white' : 'bg-surface text-text-dark hover:bg-laranja-1/20' ?>">
               Pendentes
            </a>
            <a href="<?= $urlBase ?>/solicitacoes?aba=aprovadas"
               class="py-3 px-4 text-center font-poppins font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition-all active:scale-95 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] <?= $abaAtual === 'aprovadas' ? 'bg-verdeMusgo text-white' : 'bg-surface text-text-dark hover:bg-verdeMusgo/20' ?>">
               Aprovadas
            </a>
            <a href="<?= $urlBase ?>/solicitacoes?aba=recusadas"
               class="py-3 px-4 text-center font-poppins font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition-all active:scale-95 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] <?= $abaAtual === 'recusadas' ? 'bg-erro text-white' : 'bg-surface text-text-dark hover:bg-erro/20' ?>">
               Recusadas
            </a>
        </div>

        <div class="mb-6">
            <h2 class="font-shantell text-2xl font-bold text-text-dark tracking-tight">Solicitações de adoção</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-0.5">Clique em uma solicitação para ver os dados do interessado</p>
        </div>

        <div class="divide-y divide-cinzaMarrom/20">
            <?php if (empty($solicitacoes)): ?>
                <div class="py-16 text-center">
                    <span class="text-5xl block mb-3 opacity-40">🐾</span>
                    <p class="text-text-muted text-base font-semibold">Nenhuma solicitação nesta categoria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($solicitacoes as $solic): ?>
                    <?php
                        $status = $statusLabels[$solic['status_solicitacao']] ?? ['texto' => $solic['status_solicitacao'], 'cor' => 'text-text-muted'];
                        $foto = montarUrlFotoSolicitacao($solic['animal_foto'] ?? null, $urlBase);
                    ?>
                    <div class="py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-rosa-1/20 dark:hover:bg-preto2/50 transition px-3 sm:px-4 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <?php if ($foto): ?>
                                <img src="<?= htmlspecialchars($foto) ?>" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-rosa-3 dark:border-preto3 shrink-0 shadow-sm bg-white">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-full bg-rosa-1 dark:bg-preto2 border-2 border-rosa-3 dark:border-preto3 flex items-center justify-center shrink-0 shadow-sm text-lg">🐾</div>
                            <?php endif; ?>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-text-dark leading-snug"><?= htmlspecialchars($solic['animal_nome']) ?></h3>
                                <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                                    Interessado: <strong class="text-text-dark"><?= htmlspecialchars($solic['adotante_nome']) ?></strong> •
                                    Data: <strong class="text-text-dark"><?= !empty($solic['data_solicitacao']) ? date('d/m/Y', strtotime($solic['data_solicitacao'])) : '-' ?></strong> •
                                    Status: <span class="font-bold <?= $status['cor'] ?>"><?= htmlspecialchars($status['texto']) ?></span>
                                </p>
                            </div>
                        </div>

                        <a href="<?= $urlBase ?>/solicitacoes/detalhes?id=<?= (int) $solic['solicitacao_id'] ?>"
                           class="self-end sm:self-center text-xs sm:text-sm font-bold text-primary dark:text-roxinhoFofo underline hover:text-accent transition">
                            Clique para detalhes
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
