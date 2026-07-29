<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container mx-auto p-6 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($titulo) ?></h1>
        
        <!-- Botões de Gerenciamento -->
        <div class="flex gap-4 mt-4 md:mt-0">
            <a href="<?= URL_BASE ?>/admin/especie" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200">
                Gerenciar Espécies
            </a>
            <a href="<?= URL_BASE ?>/admin/raca" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200">
                Gerenciar Raças
            </a>
        </div>
    </div>

    <!-- Cards de Espécies e Raças Lado a Lado -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($especiesComRacas as $item): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition duration-200 overflow-hidden">
                <!-- Topo: Nome da Espécie -->
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-bold text-gray-700">
                        🐾 <?= htmlspecialchars($item['especie']->getNome()) ?>
                    </h2>
                </div>
                
                <!-- Corpo: Lista de Raças -->
                <div class="p-6">
                    <?php if (!empty($item['racas'])): ?>
                        <ul class="space-y-2">
                            <?php foreach ($item['racas'] as $raca): ?>
                                <li class="text-gray-600 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full inline-block"></span>
                                    <?= htmlspecialchars($raca->getNome()) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-400 italic text-sm">Nenhuma raça cadastrada para esta espécie.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>