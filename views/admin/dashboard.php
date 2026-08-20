<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="space-y-8 pb-10">
    
    <!-- CABEÇALHO: ANÁLISE GERAL E GRÁFICO -->
    <div class="text-center my-4">
        <div class="flex items-center justify-center gap-4 mb-6">
            <h1 class="text-3xl font-bold font-shantell text-primary">Análise Geral</h1>
            <select class="bg-gray-300 text-gray-700 text-xs px-2 py-1 rounded cursor-pointer outline-none">
                <option>Nov 2027</option>
            </select>
            <?= icone('calendar', 'h-5 w-5 text-gray-500 -ml-2') ?>
        </div>

        <!-- MOCKUP DO GRÁFICO (Apenas a estrutura de textos) -->
        <div class="card-padrao max-w-3xl mx-auto py-8 bg-white rounded-xl shadow">
            <div class="flex justify-center gap-6 mb-4 text-xs font-bold text-gray-600">
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-200 rounded-sm block"></span> Animais Disponíveis</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-teal-200 rounded-sm block"></span> Animais adotados</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-500 rounded-sm block"></span> N. de usuários</span>
            </div>
            <div class="h-48 bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 text-sm italic">
                [ Área reservada para o Gráfico ]
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
            <div class="card-padrao text-center border-t-8 border-amber-500 bg-white shadow rounded-lg p-4">
                <span class="text-xs font-bold uppercase bg-amber-500 text-white px-6 py-1 rounded-full inline-block mb-4">Revisar</span>
                <div class="my-3 flex justify-center text-amber-500"><?= icone('clock', 'h-10 w-10') ?></div>
                <h3 class="text-sm font-bold text-gray-800">Cadastros Pendentes</h3>
                <p class="text-xs text-text-muted">12 ONGs aguardando</p>
            </div>

            <!-- Card 2 -->
            <div class="card-padrao text-center border-t-8 border-red-800 bg-white shadow rounded-lg p-4">
                <span class="text-xs font-bold uppercase bg-red-800 text-white px-6 py-1 rounded-full inline-block mb-4">Prioridade</span>
                <div class="my-3 flex justify-center text-red-800"><?= icone('warning', 'h-10 w-10') ?></div>
                <h3 class="text-sm font-bold text-gray-800">Denúncias em Aberto</h3>
                <p class="text-xs text-text-muted">5 alertas de segurança</p>
            </div>

            <!-- Card 3 -->
            <div class="card-padrao text-center border-t-8 border-blue-600 bg-white shadow rounded-lg p-4">
                <span class="text-xs font-bold uppercase bg-blue-600 text-white px-6 py-1 rounded-full inline-block mb-4">Monitorar</span>
                <div class="my-3 flex justify-center text-blue-600"><?= icone('heart', 'h-10 w-10') ?></div>
                <h3 class="text-sm font-bold text-gray-800">Adoções Concluídas no Mês</h3>
                <p class="text-xs text-text-muted">45 matches de sucesso</p>
            </div>
        </div>

        <!-- BOTÕES DE RELATÓRIO -->
        <div class="flex flex-col items-center gap-2 max-w-md mx-auto">
            <button type="button" class="w-full border border-gray-400 bg-white text-black font-bold py-2 rounded-lg shadow-sm hover:bg-gray-50">
                Exportar CSV
            </button>
            <button type="button" class="w-full bg-black text-white font-bold py-2 rounded-lg shadow-sm hover:bg-gray-800">
                Gerar Relatório Geral do Sistema
            </button>
        </div>
    </div>

    <!-- SEÇÃO: ATALHOS DE VALIDAÇÃO -->
    <div class="mt-8">
        <div class="flex justify-between items-end mb-4 border-b border-gray-200 pb-2">
            <div>
                <h2 class="text-xl font-bold font-shantell text-text-dark">Atalhos de Validação</h2>
                <p class="text-xs text-text-muted">Ações recomendadas para hoje</p>
            </div>
            <a href="<?= URL_BASE ?>/admin/validacao-cadastros" class="border border-gray-400 text-xs font-medium py-1 px-3 rounded hover:bg-gray-100 text-gray-700">Ver tudo ></a>
        </div>

        <div class="space-y-3">
            <!-- Atalho 1 -->
            <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="text-green-500"><?= icone('check-circle', 'h-7 w-7') ?></span>
                    <div>
                        <strong class="block text-sm text-gray-800">Aprovar cadastros pendentes</strong>
                        <span class="text-xs text-text-muted">12 ONGs aguardando</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= URL_BASE ?>/admin/validacao-cadastros" class="text-xs text-gray-600 font-bold hover:underline">Filtrar por data de envio</a>
                    <span class="text-gray-400 inline-flex items-center gap-1"><?= icone('search', 'h-4 w-4') ?> <?= icone('document', 'h-4 w-4') ?></span>
                </div>
            </div>

            <!-- Atalho 2 -->
            <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="text-amber-500"><?= icone('warning', 'h-7 w-7') ?></span>
                    <div>
                        <strong class="block text-sm text-gray-800">Tratar denúncias em aberto</strong>
                        <span class="text-xs text-text-muted">5 alertas de segurança</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= URL_BASE ?>/admin/denuncias" class="text-xs text-gray-600 font-bold hover:underline">Priorizar casos críticos</a>
                    <span class="text-gray-400 inline-flex items-center gap-1"><?= icone('search', 'h-4 w-4') ?> <?= icone('document', 'h-4 w-4') ?></span>
                </div>
            </div>

            <!-- Atalho 3 -->
            <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="text-gray-600"><?= icone('chart', 'h-7 w-7') ?></span>
                    <div>
                        <strong class="block text-sm text-gray-800">Acompanhar adoções do mês</strong>
                        <span class="text-xs text-text-muted">45 concluídas</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= URL_BASE ?>/admin/adocoes" class="text-xs text-gray-600 font-bold hover:underline">Comparar com mês anterior</a>
                    <span class="text-gray-400 inline-flex items-center gap-1"><?= icone('search', 'h-4 w-4') ?> <?= icone('document', 'h-4 w-4') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>