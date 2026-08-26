<?php 
require_once __DIR__ . '/../templates/header.php';
?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <!-- Container alargado para max-w-4xl para melhor visualização no desktop -->
    <div class="w-full max-w-4xl bg-surface rounded-3xl shadow-xl border border-rosa-2 dark:border-preto3 p-6 md:p-8 transition-colors duration-200">

        <!-- Cabeçalho -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-text-dark dark:text-white tracking-wide flex items-center gap-2">
                    <span class="text-3xl">📍</span> Regiões Cadastradas
                </h1>
                <p class="text-sm text-text-muted mt-1">Gerencie os bairros e áreas de atuação disponíveis.</p>
            </div>
            <a href="<?= URL_BASE ?>/admin/regiao/cadastrar" class="btn-primario whitespace-nowrap">
                + Nova Região
            </a>
        </div>

        <!-- Busca -->
        <div class="mb-6 p-4 bg-rosa-1/20 dark:bg-preto2/50 rounded-2xl border border-rosa-2/60 dark:border-preto3">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-text-muted">🔍</span>
                <input type="text"
                       id="busca-regiao"
                       placeholder="Buscar região pelo nome..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-preto1 dark:border-white bg-branco dark:bg-preto1 text-text-dark dark:text-white focus:outline-none focus:border-primary transition"
                       onkeyup="filtrarRegioes()">
            </div>
        </div>

        <form action="<?= URL_BASE ?>/admin/regiao/deletar-multiplos" method="POST" id="form-regioes">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2 px-1">
                    <label class="block text-lg font-bold text-text-dark dark:text-white">
                        Selecionar para Exclusão
                    </label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-text-muted cursor-pointer select-none">
                        <input type="checkbox" id="master-checkbox" onchange="toggleTodasRegioes(this)" class="w-4 h-4 rounded border-2 border-preto1 dark:border-white text-primary focus:ring-roxinhoFofo dark:bg-preto1">
                        Marcar Todas
                    </label>
                </div>

                <div class="border-2 border-preto1 dark:border-white rounded-2xl overflow-hidden bg-rosa-1/10 dark:bg-preto1/50">
                    <div class="max-h-96 overflow-y-auto divide-y divide-rosa-1 dark:divide-preto3" id="lista-regioes">
                        <?php if (!empty($regioes)): ?>
                            <?php foreach ($regioes as $r): ?>
                                <div class="regiao-item flex items-center justify-between p-3.5 hover:bg-rosa-1/30 dark:hover:bg-preto2 transition"
                                     data-nome="<?= htmlspecialchars(strtolower($r->getNomeRegiao())); ?>">

                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs font-mono text-text-muted">
                                            #<?= $r->getRegiaoId(); ?>
                                        </span>
                                        <span class="text-base font-semibold text-text-dark dark:text-white">
                                            <?= htmlspecialchars($r->getNomeRegiao()); ?>
                                        </span>
                                    </div>

                                    <div class="flex items-center space-x-4">
                                        <a href="<?= URL_BASE ?>/admin/regiao/editar?id=<?= $r->getRegiaoId(); ?>"
                                           class="text-xs font-bold text-primary dark:text-roxinhoFofo hover:underline">
                                            Editar
                                        </a>
                                        <input type="checkbox"
                                               name="ids[]"
                                               value="<?= $r->getRegiaoId(); ?>"
                                               class="chk-regiao w-5 h-5 rounded border-2 border-preto1 dark:border-white text-primary focus:ring-roxinhoFofo dark:bg-preto1">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-8 text-center text-sm text-text-muted">
                                Nenhuma região cadastrada ainda.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($regioes)): ?>
                <div class="flex justify-center mt-6">
                    <button type="button"
                            onclick="confirmarExclusaoModal()"
                            class="px-8 py-2.5 bg-rosa-1 hover:bg-rosa-2 dark:bg-preto2 dark:hover:bg-preto3 text-text-dark dark:text-white font-bold rounded-2xl border-2 border-preto1 dark:border-white shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:translate-x-0.5 active:translate-y-0.5 transition-all cursor-pointer">
                        Deletar regiões marcadas
                    </button>
                </div>
            <?php endif; ?>
        </form>

    </div>
</div>

<script>
    // Filtragem dinâmica apenas pelo nome
    function filtrarRegioes() {
        const termoBusca = document.getElementById('busca-regiao').value.toLowerCase();
        const regioes = document.querySelectorAll('.regiao-item');

        regioes.forEach(item => {
            const nome = item.getAttribute('data-nome');
            const atendeBusca = nome.includes(termoBusca);

            if (atendeBusca) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
                const checkbox = item.querySelector('.chk-regiao');
                if (checkbox) checkbox.checked = false;
            }
        });
        
        document.getElementById('master-checkbox').checked = false;
    }

    // Selecionar/desmarcar todos os visíveis
    function toggleTodasRegioes(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.regiao-item');
        checkboxes.forEach(item => {
            if (item.style.display !== 'none') {
                const chk = item.querySelector('.chk-regiao');
                if (chk) chk.checked = masterCheckbox.checked;
            }
        });
    }

    // Valida seleção e dispara a modal do footer
    function confirmarExclusaoModal() {
        const selecionados = document.querySelectorAll('.chk-regiao:checked');

        if (selecionados.length === 0) {
            mostrarModalFeedback('aviso', 'Selecione ao menos uma região para excluir.');
            return;
        }

        abrirModalConfirmacao(
            'Confirmar Exclusão',
            `Deseja realmente excluir as ${selecionados.length} região(ões) selecionada(s)?`,
            () => document.getElementById('form-regioes').submit()
        );
    }
</script>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>