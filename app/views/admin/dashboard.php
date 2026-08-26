<?php
require_once __DIR__ . '/../templates/header.php';

$stats = $stats ?? [];

$mesesPt = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
$mesRotulo = $mesesPt[(int) date('n')] . ' de ' . date('Y');

$metricasGrafico = [
    ['rotulo' => 'Animais Disponíveis', 'valor' => $stats['animais_disponiveis'] ?? 0, 'cor' => 'bg-red-300'],
    ['rotulo' => 'Animais Adotados',    'valor' => $stats['animais_adotados'] ?? 0,    'cor' => 'bg-teal-400'],
    ['rotulo' => 'Usuários Ativos',     'valor' => $stats['usuarios_ativos'] ?? 0,     'cor' => 'bg-green-500'],
];
$maiorValorGrafico = max(1, ...array_column($metricasGrafico, 'valor')); // evita divisão por 0
?>

<div class="space-y-8 pb-10">

    <!-- CABEÇALHO: ANÁLISE GERAL E GRÁFICO -->
    <div class="text-center my-4">
        <div class="flex items-center justify-center gap-3 mb-6">
            <h1 class="text-3xl font-bold font-shantell text-primary">Análise Geral</h1>
            <span class="bg-gray-200 dark:bg-preto3 text-gray-700 dark:text-white text-xs px-3 py-1 rounded-full font-medium">📅 <?= htmlspecialchars($mesRotulo) ?></span>
        </div>

        <div class="card-padrao max-w-3xl mx-auto py-8">
            <div class="flex justify-center gap-6 mb-6 text-xs font-bold text-text-muted">
                <?php foreach ($metricasGrafico as $m): ?>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 <?= $m['cor'] ?> rounded-sm block"></span> <?= htmlspecialchars($m['rotulo']) ?></span>
                <?php endforeach; ?>
            </div>

            <!-- Sem Chart.js no projeto: comparação simples em barras horizontais
                 proporcionais ao maior valor do grupo, com os números reais ao lado. -->
            <div class="space-y-4 px-6 sm:px-10">
                <?php foreach ($metricasGrafico as $m): ?>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-6 bg-gray-100 dark:bg-preto3 rounded-full overflow-hidden">
                            <div class="h-full <?= $m['cor'] ?> rounded-full transition-all" style="width: <?= max(4, round($m['valor'] / $maiorValorGrafico * 100)) ?>%"></div>
                        </div>
                        <span class="w-10 text-right text-sm font-bold text-gray-800 dark:text-white"><?= (int) $m['valor'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- SEÇÃO: SAÚDE DO SISTEMA -->
    <div>
        <div class="mb-4">
            <h2 class="text-xl font-bold font-shantell text-text-dark">Saúde do Sistema CãoNectados</h2>
            <p class="text-xs text-text-muted">Resumo em tempo real</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Card 1 -->
            <a href="<?= URL_BASE ?>/admin/solicitacoes?status=pendentes" class="card-padrao text-center border-t-8 border-aviso hover:shadow-md transition block">
                <span class="text-xs font-bold uppercase bg-aviso text-white px-6 py-1 rounded-full inline-block mb-4">Revisar</span>
                <div class="my-3 text-4xl">🕒</div>
                <h3 class="text-sm font-bold text-text-dark dark:text-white"><?= (int) $stats['cadastros_pendentes'] ?></h3>
                <p class="text-xs text-text-muted">Cadastros Pendentes</p>
            </a>

            <!-- Card 2 -->
            <a href="<?= URL_BASE ?>/admin/denuncias" class="card-padrao text-center border-t-8 border-erro hover:shadow-md transition block">
                <span class="text-xs font-bold uppercase bg-erro text-white px-6 py-1 rounded-full inline-block mb-4">Prioridade</span>
                <div class="my-3 text-4xl">⚠️</div>
                <h3 class="text-sm font-bold text-text-dark dark:text-white"><?= (int) $stats['denuncias_abertas'] ?></h3>
                <p class="text-xs text-text-muted">Denúncias em Aberto</p>
            </a>

            <!-- Card 3 -->
            <a href="<?= URL_BASE ?>/admin/relatorios?periodo=mes_atual&status=adotado" class="card-padrao text-center border-t-8 border-informativo hover:shadow-md transition block">
                <span class="text-xs font-bold uppercase bg-informativo text-white px-6 py-1 rounded-full inline-block mb-4">Monitorar</span>
                <div class="my-3 text-4xl">🤍</div>
                <h3 class="text-sm font-bold text-text-dark dark:text-white"><?= (int) $stats['adocoes_concluidas_no_mes'] ?></h3>
                <p class="text-xs text-text-muted">Adoções Concluídas no Mês</p>
            </a>
        </div>

        <!-- BOTÕES DE RELATÓRIO (RF 12) -->
        <div class="flex flex-col items-center gap-2 max-w-md mx-auto">
            <a href="<?= URL_BASE ?>/admin/relatorios/exportar-csv" class="btn-secundario w-full">
                Exportar CSV
            </a>
            <a href="<?= URL_BASE ?>/admin/relatorios" class="btn-primario w-full">
                Gerar Relatório Geral do Sistema
            </a>
        </div>
    </div>

    <!-- SEÇÃO: ATALHOS DE VALIDAÇÃO -->
    <div class="mt-8">
        <div class="flex justify-between items-end mb-4 border-b border-cinzaMarrom/30 dark:border-preto3 pb-2">
            <div>
                <h2 class="text-xl font-bold font-shantell text-text-dark">Atalhos de Validação</h2>
                <p class="text-xs text-text-muted">Ações recomendadas para hoje</p>
            </div>
            <a href="<?= URL_BASE ?>/admin/solicitacoes" class="border border-cinzaMarrom dark:border-preto3 text-xs font-medium py-1 px-3 rounded hover:bg-rosa-1/20 dark:hover:bg-preto2 text-text-dark dark:text-white">Ver tudo ></a>
        </div>

        <div class="space-y-3">
            <!-- Atalho 1 -->
            <a href="<?= URL_BASE ?>/admin/solicitacoes?status=pendentes" class="flex items-center justify-between p-3 bg-surface border border-cinzaMarrom/20 dark:border-preto3 rounded-xl shadow-sm hover:bg-rosa-1/20 dark:hover:bg-preto2 transition">
                <div class="flex items-center gap-3">
                    <span class="text-2xl text-sucesso">✅</span>
                    <div>
                        <strong class="block text-sm text-text-dark dark:text-white">Aprovar cadastros pendentes</strong>
                        <span class="text-xs text-text-muted"><?= (int) $stats['cadastros_pendentes'] ?> ONGs/Protetores aguardando</span>
                    </div>
                </div>
                <span class="text-text-muted">🔍 📄</span>
            </a>

            <!-- Atalho 2 -->
            <a href="<?= URL_BASE ?>/admin/denuncias" class="flex items-center justify-between p-3 bg-surface border border-cinzaMarrom/20 dark:border-preto3 rounded-xl shadow-sm hover:bg-rosa-1/20 dark:hover:bg-preto2 transition">
                <div class="flex items-center gap-3">
                    <span class="text-2xl text-aviso">⚠️</span>
                    <div>
                        <strong class="block text-sm text-text-dark dark:text-white">Tratar denúncias em aberto</strong>
                        <span class="text-xs text-text-muted"><?= (int) $stats['denuncias_abertas'] ?> denúncia(s) aguardando análise</span>
                    </div>
                </div>
                <span class="text-text-muted">🔍 📄</span>
            </a>

            <!-- Atalho 3 -->
            <a href="<?= URL_BASE ?>/admin/relatorios?periodo=mes_atual&status=adotado" class="flex items-center justify-between p-3 bg-surface border border-cinzaMarrom/20 dark:border-preto3 rounded-xl shadow-sm hover:bg-rosa-1/20 dark:hover:bg-preto2 transition">
                <div class="flex items-center gap-3">
                    <span class="text-2xl text-text-muted">📊</span>
                    <div>
                        <strong class="block text-sm text-text-dark dark:text-white">Acompanhar adoções do mês</strong>
                        <span class="text-xs text-text-muted"><?= (int) $stats['adocoes_concluidas_no_mes'] ?> concluída(s) em <?= htmlspecialchars($mesRotulo) ?></span>
                    </div>
                </div>
                <span class="text-text-muted">🔍 📄</span>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
