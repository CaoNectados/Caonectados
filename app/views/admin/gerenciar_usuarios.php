<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-8 min-h-screen pb-24">
    
    <!-- Cabeçalho -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold font-shantell text-gray-800 dark:text-white flex items-center gap-2">
                👥 Gerenciar Usuários
            </h1>
            <p class="text-xs text-gray-500">Controle global de contas e status individual de perfis.</p>
        </div>
        <a href="<?= URL_BASE ?>/admin/dashboard" class="text-sm text-roxinhoFofo font-bold hover:underline">
            &larr; Voltar ao Painel
        </a>
    </div>

    <!-- Barra de Filtros e Busca -->
    <form method="GET" action="<?= URL_BASE ?>/admin/gerenciar-usuarios" class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Buscar</label>
            <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Nome ou e-mail..." class="w-full text-xs p-2.5 border rounded-xl dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Status da Conta</label>
            <select name="status" class="w-full text-xs p-2.5 border rounded-xl bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600">
                <option value="">Todos</option>
                <option value="ativo" <?= (($filtros['status'] ?? '') === 'ativo') ? 'selected' : '' ?>>Ativos</option>
                <option value="inativo" <?= (($filtros['status'] ?? '') === 'inativo') ? 'selected' : '' ?>>Desativados</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Perfil</label>
            <select name="perfil" class="w-full text-xs p-2.5 border rounded-xl bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600">
                <option value="">Todos</option>
                <option value="adotante" <?= (($filtros['perfil'] ?? '') === 'adotante') ? 'selected' : '' ?>>Adotante</option>
                <option value="protetor" <?= (($filtros['perfil'] ?? '') === 'protetor') ? 'selected' : '' ?>>Protetor</option>
                <option value="ong" <?= (($filtros['perfil'] ?? '') === 'ong') ? 'selected' : '' ?>>ONG</option>
                <option value="administrador" <?= (($filtros['perfil'] ?? '') === 'administrador') ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-roxinhoFofo text-white text-xs font-bold py-2.5 rounded-xl hover:opacity-90 transition">
                Filtrar
            </button>
            <a href="<?= URL_BASE ?>/admin/gerenciar-usuarios" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold py-2.5 px-3 rounded-xl hover:bg-gray-300 transition">
                Limpar
            </a>
        </div>
    </form>

    <!-- Tabela / Cards de Usuários -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 text-gray-600 dark:text-gray-300 text-[11px] uppercase">
                        <th class="py-3 px-4">Usuário</th>
                        <th class="py-3 px-4">Status da Conta</th>
                        <th class="py-3 px-4">Perfis Cadastrados</th>
                        <th class="py-3 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-400 text-xs">Nenhum usuário encontrado com os filtros selecionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <?php 
                                $isAtivo = ($u['status_conta'] === 'ativo'); 
                                $perfisArr = array_filter(array_map('trim', explode(',', strtolower($u['perfis_ativos'] ?? ''))));
                            ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($u['nome'] ?? 'Sem Nome') ?></div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($u['email']) ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($isAtivo): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Desativado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php if ((int)$u['tem_adotante'] > 0): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= in_array('adotante', $perfisArr) ? 'bg-purple-100 text-purple-700' : 'bg-gray-200 text-gray-500 line-through' ?>">
                                                Adotante
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($u['tipo_protetor'])): ?>
                                            <?php $tag = ($u['tipo_protetor'] === 'cnpj') ? 'ong' : 'protetor'; ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= in_array($tag, $perfisArr) ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-500 line-through' ?>">
                                                <?= strtoupper($tag) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($u['tipo_atual'] === 'administrador' || in_array('administrador', $perfisArr)): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Admin
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" onclick="abrirModalGerenciar(<?= $u['usuario_id'] ?>)" class="bg-gray-100 dark:bg-gray-700 hover:bg-roxinhoFofo hover:text-white text-gray-700 dark:text-gray-200 font-bold text-xs px-3 py-1.5 rounded-xl transition cursor-pointer">
                                        ⚙️ Gerenciar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div class="flex justify-between items-center px-4 py-3 border-t border-gray-100 dark:border-gray-700 text-xs">
                <span class="text-gray-500">Página <?= $paginaAtual ?> de <?= $totalPaginas ?> (Total: <?= $total ?>)</span>
                <div class="flex gap-1">
                    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                        <a href="?pagina=<?= $p ?>&busca=<?= urlencode($filtros['busca']) ?>&status=<?= urlencode($filtros['status']) ?>&perfil=<?= urlencode($filtros['perfil']) ?>" class="px-3 py-1 rounded-lg font-bold <?= $p === $paginaAtual ? 'bg-roxinhoFofo text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL GERENCIAR USUÁRIO E PERFIS -->
<div id="modal-gerenciar" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="fecharModalGerenciar()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl font-bold">&times;</button>
        
        <div id="modal-conteudo">
            <div class="text-center py-6 text-gray-400 text-sm">Carregando dados do usuário...</div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAÇÃO DE AÇÃO -->
<div id="modal-confirmacao" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-5 shadow-2xl text-center">
        <div id="confirma-icone" class="text-4xl mb-2">⚠️</div>
        <h3 id="confirma-titulo" class="font-bold text-lg text-gray-800 dark:text-white mb-2">Confirmação</h3>
        <p id="confirma-texto" class="text-xs text-gray-500 mb-6">Você tem certeza desta ação?</p>

        <div class="flex gap-2">
            <button type="button" onclick="fecharModalConfirmacao()" class="flex-1 bg-gray-200 text-gray-700 font-bold py-2 rounded-xl text-xs hover:bg-gray-300">Cancelar</button>
            <button type="button" id="btn-executar-acao" class="flex-1 bg-roxinhoFofo text-white font-bold py-2 rounded-xl text-xs hover:opacity-90">Confirmar</button>
        </div>
    </div>
</div>

<script>
let acaoPendente = null;

async function abrirModalGerenciar(usuarioId) {
    const modal = document.getElementById('modal-gerenciar');
    const container = document.getElementById('modal-conteudo');
    modal.classList.remove('hidden');
    container.innerHTML = '<div class="text-center py-8 text-gray-400 text-sm">Carregando...</div>';

    try {
        const resp = await fetch(`<?= URL_BASE ?>/admin/usuarios/detalhes?id=${usuarioId}`);
        const res = await resp.json();

        if (res.status !== 'sucesso') {
            mostrarModalFeedback('erro', res.mensagem);
            fecharModalGerenciar();
            return;
        }

        renderizarDetalhesModal(res.dados);
    } catch (e) {
        mostrarModalFeedback('erro', 'Falha ao buscar dados do usuário.');
        fecharModalGerenciar();
    }
}

function renderizarDetalhesModal(dados) {
    const u = dados.usuario;
    const isAtivo = (u.status_conta === 'ativo');
    const container = document.getElementById('modal-conteudo');

    let perfisHtml = '';
    dados.perfis.forEach(p => {
        perfisHtml += `
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl flex items-center justify-between border border-gray-100 dark:border-gray-600">
                <div>
                    <div class="font-bold text-xs text-gray-800 dark:text-white">${p.nome}</div>
                    <div class="text-[10px] text-gray-400">${p.info}</div>
                    <span class="inline-block mt-1 text-[10px] font-bold ${p.ativo ? 'text-green-600' : 'text-red-500'}">
                        ● ${p.ativo ? 'ATIVO' : 'DESATIVADO'}
                    </span>
                </div>
                <div>
                    <button type="button" onclick="confirmarAlterarPerfil(${u.usuario_id}, '${p.tipo}', '${p.nome}', '${p.ativo ? 'desativar' : 'ativar'}')" class="text-[11px] font-bold px-3 py-1.5 rounded-lg transition ${p.ativo ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100'}">
                        ${p.ativo ? 'Desativar ' + p.nome : 'Reativar ' + p.nome}
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = `
        <h2 class="font-shantell text-xl font-bold text-gray-800 dark:text-white mb-1">${u.nome || 'Sem Nome'}</h2>
        <p class="text-xs text-gray-400 mb-4">${u.email} &bull; Cadastrado em ${u.criado_em}</p>

        <!-- Status Global -->
        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-2xl mb-4 border border-purple-100 dark:border-purple-800 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Status Global da Conta</span>
                <span class="text-xs font-extrabold ${isAtivo ? 'text-green-600' : 'text-red-600'}">
                    ● ${isAtivo ? 'USUÁRIO ATIVO' : 'USUÁRIO DESATIVADO'}
                </span>
            </div>
            <button type="button" onclick="confirmarAlterarUsuario(${u.usuario_id}, '${isAtivo ? 'desativar' : 'ativar'}')" class="text-xs font-bold px-3 py-2 rounded-xl transition ${isAtivo ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-green-600 text-white hover:bg-green-700'}">
                ${isAtivo ? 'Desativar Conta' : 'Reativar Conta'}
            </button>
        </div>

        <!-- Lista de Perfis -->
        <h3 class="font-bold text-xs text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-2">Perfis Vinculados</h3>
        <div class="space-y-2 mb-4">
            ${perfisHtml || '<p class="text-xs text-gray-400">Nenhum perfil específico vinculado.</p>'}
        </div>
    `;
}

function fecharModalGerenciar() {
    document.getElementById('modal-gerenciar').classList.add('hidden');
}

function confirmarAlterarUsuario(usuarioId, acao) {
    const isDesativar = (acao === 'desativar');
    document.getElementById('confirma-icone').innerText = isDesativar ? '🚫' : '✅';
    document.getElementById('confirma-titulo').innerText = isDesativar ? 'Desativar Usuário?' : 'Reativar Usuário?';
    document.getElementById('confirma-texto').innerText = isDesativar 
        ? 'Ao desativar este usuário, ele não poderá mais realizar login nem utilizar qualquer funcionalidade na plataforma.' 
        : 'O usuário poderá voltar a acessar a plataforma com seus perfis ativos normalmente.';
    
    acaoPendente = async () => {
        const formData = new FormData();
        formData.append('usuario_id', usuarioId);
        formData.append('acao', acao);
        
        const resp = await fetch('<?= URL_BASE ?>/admin/usuarios/alterar-status', { method: 'POST', body: formData });
        const res = await resp.json();
        
        fecharModalConfirmacao();
        if (res.status === 'sucesso') {
            mostrarModalFeedback('sucesso', res.mensagem);
            setTimeout(() => window.location.reload(), 1200);
        } else {
            mostrarModalFeedback('erro', res.mensagem);
        }
    };

    document.getElementById('modal-confirmacao').classList.remove('hidden');
}

function confirmarAlterarPerfil(usuarioId, tipoPerfil, nomePerfil, acao) {
    const isDesativar = (acao === 'desativar');
    document.getElementById('confirma-icone').innerText = isDesativar ? '⚠️' : '🔄';
    document.getElementById('confirma-titulo').innerText = `${isDesativar ? 'Desativar' : 'Reativar'} Perfil ${nomePerfil}?`;
    document.getElementById('confirma-texto').innerText = isDesativar
        ? `O usuário não poderá mais utilizar os recursos deste perfil, mas continuará acessando a plataforma caso possua outros perfis ativos.`
        : `O perfil ${nomePerfil} voltará a ficar disponível para o usuário.`;

    acaoPendente = async () => {
        const formData = new FormData();
        formData.append('usuario_id', usuarioId);
        formData.append('tipo_perfil', tipoPerfil);
        formData.append('acao', acao);

        const resp = await fetch('<?= URL_BASE ?>/admin/usuarios/alterar-status-perfil', { method: 'POST', body: formData });
        const res = await resp.json();

        fecharModalConfirmacao();
        if (res.status === 'sucesso') {
            mostrarModalFeedback('sucesso', res.mensagem);
            abrirModalGerenciar(usuarioId); // Recarrega os dados dentro da modal
        } else {
            mostrarModalFeedback('erro', res.mensagem);
        }
    };

    document.getElementById('modal-confirmacao').classList.remove('hidden');
}

function fecharModalConfirmacao() {
    document.getElementById('modal-confirmacao').classList.add('hidden');
    acaoPendente = null;
}

document.getElementById('btn-executar-acao').addEventListener('click', () => {
    if (acaoPendente) acaoPendente();
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

