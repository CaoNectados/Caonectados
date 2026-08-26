<?php
$alvo = $alvo ?? null;
$solicitacaoId = $solicitacaoId ?? null;
$chatId = $chatId ?? null;
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$motivos = [
    'maus_tratos' => 'Maus-tratos',
    'abandono'    => 'Abandono',
    'fraude'      => 'Fraude',
    'assedio'     => 'Assédio',
    'outro'       => 'Outro',
];
?>

<div class="max-w-md lg:max-w-lg mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6">
        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Denúncias</h1>
        <p class="text-sm text-text-muted mt-1">Ajude a manter a comunidade segura</p>
    </div>

    <form id="form-denuncia" method="POST" action="<?= $urlBase ?>/denunciar/enviar" class="space-y-5">
        <input type="hidden" name="denunciado_id" id="input-denunciado-id" value="<?= $alvo ? (int) $alvo['usuario_id'] : '' ?>">
        <?php if ($solicitacaoId): ?><input type="hidden" name="solicitacao_id" value="<?= (int) $solicitacaoId ?>"><?php endif; ?>
        <?php if ($chatId): ?><input type="hidden" name="chat_id" value="<?= (int) $chatId ?>"><?php endif; ?>

        <div>
            <label class="block text-sm font-bold text-text-dark dark:text-white mb-2">Quem você está denunciando?</label>

            <div id="chip-alvo-selecionado" class="<?= $alvo ? '' : 'hidden' ?> flex items-center gap-2 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-full pl-1 pr-3 py-1 w-fit shadow-sm">
                <span class="w-8 h-8 rounded-full bg-rosa-1 dark:bg-preto2 flex items-center justify-center text-sm">👤</span>
                <span id="texto-alvo-selecionado" class="text-sm font-bold text-text-dark dark:text-white"><?= $alvo ? htmlspecialchars($alvo['nome']) : '' ?></span>
                <button type="button" onclick="limparAlvoSelecionado()" class="text-text-muted hover:text-erro text-sm font-bold ml-1">&times;</button>
            </div>

            <div id="bloco-busca-alvo" class="<?= $alvo ? 'hidden' : '' ?> relative">
                <input type="text" id="input-busca-alvo" autocomplete="off" placeholder="Buscar usuário ou ONG para denunciar"
                       class="w-full bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-full px-4 py-2.5 text-sm outline-none text-text-dark dark:text-white placeholder-text-muted">
                <ul id="lista-resultados-alvo" class="hidden absolute z-10 mt-1 w-full bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl shadow-lg overflow-hidden max-h-56 overflow-y-auto"></ul>
                <p class="text-xs text-text-muted mt-1">Ajuda a encontrar a pessoa/organização envolvida.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-text-dark dark:text-white mb-2">Motivo da denúncia</label>
            <input type="hidden" name="motivo" id="input-motivo" value="">
            <div class="flex flex-wrap gap-2" id="grupo-motivos">
                <?php foreach ($motivos as $valor => $label): ?>
                    <button type="button" data-motivo="<?= $valor ?>"
                            class="botao-motivo px-4 py-2 rounded-full text-xs font-bold border border-rosa-2 dark:border-preto3 bg-white dark:bg-preto1 text-text-dark dark:text-white transition">
                        <?= htmlspecialchars($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-text-muted mt-1">Selecione o motivo mais adequado.</p>
        </div>

        <div>
            <label for="descricao" class="block text-sm font-bold text-text-dark dark:text-white mb-2">Descrição do ocorrido</label>
            <textarea id="descricao" name="descricao" rows="4" required
                      placeholder="Descreva o que aconteceu, quando, onde e qualquer detalhe relevante..."
                      class="w-full p-3 border border-rosa-2 dark:border-preto3 rounded-xl bg-white dark:bg-preto1 text-text-dark dark:text-white outline-none focus:border-primary transition"></textarea>
            <p class="text-xs text-text-muted mt-1">Dica: inclua data aproximada e local. Evite dados sensíveis.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <a href="<?= $urlBase ?>/perfil" class="flex-1 text-center py-3 border border-text-dark dark:border-white/30 rounded-full text-sm font-bold text-text-dark dark:text-white hover:bg-rosa-1/30 transition">Cancelar</a>
            <button type="submit" class="flex-1 py-3 bg-erro hover:opacity-90 text-white rounded-full text-sm font-bold shadow transition">Enviar Denúncia</button>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';

    const urlBase = '<?= $urlBase ?>';
    const inputBusca = document.getElementById('input-busca-alvo');
    const listaResultados = document.getElementById('lista-resultados-alvo');
    const inputDenunciadoId = document.getElementById('input-denunciado-id');
    const chip = document.getElementById('chip-alvo-selecionado');
    const textoChip = document.getElementById('texto-alvo-selecionado');
    const blocoBusca = document.getElementById('bloco-busca-alvo');
    let timeoutBusca = null;

    window.limparAlvoSelecionado = function () {
        inputDenunciadoId.value = '';
        chip.classList.add('hidden');
        blocoBusca.classList.remove('hidden');
        inputBusca.value = '';
        inputBusca.focus();
    };

    function selecionarAlvo(usuario) {
        inputDenunciadoId.value = usuario.usuario_id;
        textoChip.textContent = usuario.nome;
        chip.classList.remove('hidden');
        blocoBusca.classList.add('hidden');
        listaResultados.classList.add('hidden');
    }

    if (inputBusca) {
        inputBusca.addEventListener('input', function () {
            const termo = inputBusca.value.trim();
            clearTimeout(timeoutBusca);

            if (termo.length < 2) {
                listaResultados.classList.add('hidden');
                listaResultados.innerHTML = '';
                return;
            }

            timeoutBusca = setTimeout(async () => {
                try {
                    const resposta = await fetch(`${urlBase}/denunciar/buscar?q=${encodeURIComponent(termo)}`, { headers: { Accept: 'application/json' } });
                    const resultado = await resposta.json();

                    if (resultado.status === 'sucesso' && resultado.resultados.length) {
                        listaResultados.innerHTML = resultado.resultados.map(u => `
                            <li>
                                <button type="button" data-id="${u.usuario_id}" data-nome="${u.nome.replace(/"/g, '&quot;')}"
                                        class="w-full text-left px-4 py-2.5 text-sm text-text-dark dark:text-white hover:bg-rosa-1/30 dark:hover:bg-preto2 transition">
                                    ${u.nome} <span class="text-xs text-text-muted">${u.email}</span>
                                </button>
                            </li>
                        `).join('');
                        listaResultados.classList.remove('hidden');

                        listaResultados.querySelectorAll('button').forEach(function (botao) {
                            botao.addEventListener('click', function () {
                                selecionarAlvo({ usuario_id: this.dataset.id, nome: this.dataset.nome });
                            });
                        });
                    } else {
                        listaResultados.innerHTML = '<li class="px-4 py-2.5 text-xs text-text-muted">Nenhum resultado encontrado.</li>';
                        listaResultados.classList.remove('hidden');
                    }
                } catch (erro) {
                    listaResultados.classList.add('hidden');
                }
            }, 350);
        });
    }

    document.querySelectorAll('.botao-motivo').forEach(function (botao) {
        botao.addEventListener('click', function () {
            document.querySelectorAll('.botao-motivo').forEach(b => b.classList.remove('bg-erro', 'text-white', 'border-erro'));
            botao.classList.add('bg-erro', 'text-white', 'border-erro');
            document.getElementById('input-motivo').value = botao.dataset.motivo;
        });
    });

    document.getElementById('form-denuncia').addEventListener('submit', function (evento) {
        if (!inputDenunciadoId.value) {
            evento.preventDefault();
            mostrarModalFeedback('aviso', 'Selecione quem você está denunciando.');
            return;
        }
        if (!document.getElementById('input-motivo').value) {
            evento.preventDefault();
            mostrarModalFeedback('aviso', 'Selecione o motivo da denúncia.');
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
