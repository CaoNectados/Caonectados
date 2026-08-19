<?php 
require_once __DIR__ . '/../templates/header.php';
?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <!-- Container alargado para max-w-4xl para melhor visualização no desktop -->
    <div class="w-full max-w-4xl bg-white dark:bg-zinc-800 rounded-3xl shadow-xl border border-purple-200 dark:border-zinc-700 p-6 md:p-8 transition-colors duration-200">
        
        <!-- Cabeçalho -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-zinc-800 dark:text-zinc-100 tracking-wide flex items-center gap-2">
                    <span class="text-3xl">📍</span> Regiões Cadastradas
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Gerencie os bairros e áreas de atuação disponíveis.</p>
            </div>
            <a href="<?= URL_BASE ?>/admin/regiao/cadastrar" 
               class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl transition duration-150 shadow hover:shadow-md whitespace-nowrap">
                + Nova Região
            </a>
        </div>

        <!-- Busca -->
        <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-200 dark:border-zinc-700">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">🔍</span>
                <input type="text" 
                       id="busca-regiao" 
                       placeholder="Buscar região pelo nome..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-zinc-900 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 focus:outline-none focus:border-purple-600 dark:focus:border-purple-400 transition"
                       onkeyup="filtrarRegioes()">
            </div>
        </div>

        <form action="<?= URL_BASE ?>/admin/regiao/deletar-multiplos" method="POST" id="form-regioes">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2 px-1">
                    <label class="block text-lg font-bold text-zinc-700 dark:text-zinc-200">
                        Selecionar para Exclusão
                    </label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-zinc-600 dark:text-zinc-300 cursor-pointer select-none">
                        <input type="checkbox" id="master-checkbox" onchange="toggleTodasRegioes(this)" class="w-4 h-4 rounded border-2 border-zinc-800 text-purple-600 focus:ring-purple-500 dark:bg-zinc-800 dark:border-zinc-500">
                        Marcar Todas
                    </label>
                </div>
                
                <div class="border-2 border-zinc-900 dark:border-zinc-600 rounded-2xl overflow-hidden bg-purple-50/30 dark:bg-zinc-900/50">
                    <div class="max-h-96 overflow-y-auto divide-y divide-purple-100 dark:divide-zinc-700" id="lista-regioes">
                        <?php if (!empty($regioes)): ?>
                            <?php foreach ($regioes as $r): ?>
                                <div class="regiao-item flex items-center justify-between p-3.5 hover:bg-purple-100/60 dark:hover:bg-zinc-700/50 transition" 
                                     data-nome="<?= htmlspecialchars(strtolower($r->getNomeRegiao())); ?>">
                                    
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
                                            #<?= $r->getRegiaoId(); ?>
                                        </span>
                                        <span class="text-base font-semibold text-zinc-800 dark:text-zinc-100">
                                            <?= htmlspecialchars($r->getNomeRegiao()); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <a href="<?= URL_BASE ?>/admin/regiao/editar?id=<?= $r->getRegiaoId(); ?>" 
                                           class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                                            Editar
                                        </a>
                                        <input type="checkbox" 
                                               name="ids[]" 
                                               value="<?= $r->getRegiaoId(); ?>" 
                                               class="chk-regiao w-5 h-5 rounded border-2 border-zinc-800 text-purple-600 focus:ring-purple-500 dark:bg-zinc-800 dark:border-zinc-500">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
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
                            class="px-8 py-2.5 bg-purple-200 hover:bg-purple-300 dark:bg-purple-900/60 dark:hover:bg-purple-800 text-zinc-900 dark:text-purple-100 font-bold rounded-2xl border-2 border-zinc-900 dark:border-zinc-600 shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:translate-x-0.5 active:translate-y-0.5 transition-all cursor-pointer">
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
            if (typeof abrirModalMensagem === 'function') {
                abrirModalMensagem('Aviso', 'Selecione ao menos uma região para excluir.');
            } else {
                alert('Selecione ao menos uma região para excluir.');
            }
            return;
        }

        // Se houver função de modal de confirmação no footer (ex: abrirModalConfirmacao)
        if (typeof abrirModalConfirmacao === 'function') {
            abrirModalConfirmacao(
                'Confirmar Exclusão',
                `Deseja realmente excluir as ${selecionados.length} região(ões) selecionada(s)?`,
                () => document.getElementById('form-regioes').submit()
            );
        } else {
            // Caso sua estrutura utilize abertura por elemento de modal
            const modalEl = document.getElementById('modal-confirmacao') || document.getElementById('modal-exclusao');
            if (modalEl) {
                modalEl.classList.remove('hidden');
                const btnConfirmar = modalEl.querySelector('#btn-confirmar-exclusao');
                if (btnConfirmar) {
                    btnConfirmar.onclick = () => document.getElementById('form-regioes').submit();
                }
            } else {
                // Fallback caso a modal não esteja instanciada
                document.getElementById('form-regioes').submit();
            }
        }
    }
</script>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>