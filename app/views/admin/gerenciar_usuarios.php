<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-8 min-h-screen pb-24">
    
    <!-- Cabeçalho -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold font-shantell text-text-dark flex items-center gap-2">
                👥 Gerenciar Usuários
            </h1>
            <p class="text-xs text-text-muted">Controle global de contas e status individual de perfis.</p>
        </div>
        <a href="<?= URL_BASE ?>/admin/dashboard" class="text-sm text-roxinhoFofo font-bold hover:underline">
            &larr; Voltar ao Painel
        </a>
    </div>

    <!-- Barra de Filtros e Busca -->
    <form method="GET" action="<?= URL_BASE ?>/admin/gerenciar-usuarios" class="bg-surface p-4 rounded-2xl shadow-sm border border-rosa-2 mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-bold text-text-dark/70 mb-1">Buscar</label>
            <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Nome ou e-mail..." class="input-padrao text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-text-dark/70 mb-1">Status da Conta</label>
            <select name="status" class="input-padrao text-sm">
                <option value="">Todos</option>
                <option value="ativo" <?= (($filtros['status'] ?? '') === 'ativo') ? 'selected' : '' ?>>Ativos</option>
                <option value="inativo" <?= (($filtros['status'] ?? '') === 'inativo') ? 'selected' : '' ?>>Desativados</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-text-dark/70 mb-1">Perfil</label>
            <select name="perfil" class="input-padrao text-sm">
                <option value="">Todos</option>
                <option value="adotante" <?= (($filtros['perfil'] ?? '') === 'adotante') ? 'selected' : '' ?>>Adotante</option>
                <option value="protetor" <?= (($filtros['perfil'] ?? '') === 'protetor') ? 'selected' : '' ?>>Protetor</option>
                <option value="ong" <?= (($filtros['perfil'] ?? '') === 'ong') ? 'selected' : '' ?>>ONG</option>
                <option value="administrador" <?= (($filtros['perfil'] ?? '') === 'administrador') ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primario flex-1 text-sm py-2.5">
                Filtrar
            </button>
            <a href="<?= URL_BASE ?>/admin/gerenciar-usuarios" class="btn-secundario text-sm py-2.5 px-4">
                Limpar
            </a>
        </div>
    </form>

    <!-- Tabela / Cards de Usuários -->
    <div class="bg-surface rounded-2xl shadow-sm border border-rosa-2 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-rosa-2 bg-rosa-1/20 text-text-dark/70 text-[11px] uppercase">
                        <th class="py-3 px-4 font-semibold">Usuário</th>
                        <th class="py-3 px-4 font-semibold">Status da Conta</th>
                        <th class="py-3 px-4 font-semibold">Perfis Cadastrados</th>
                        <th class="py-3 px-4 text-right font-semibold">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rosa-2/30 text-sm">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-8 text-text-muted text-xs">Nenhum usuário encontrado com os filtros selecionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <?php 
                                $isAtivo = ($u['status_conta'] === 'ativo'); 
                                $perfisArr = array_filter(array_map('trim', explode(',', strtolower($u['perfis_ativos'] ?? ''))));
                            ?>
                            <tr class="hover:bg-rosa-1/10 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-text-dark"><?= htmlspecialchars($u['nome'] ?? 'Sem Nome') ?></div>
                                    <div class="text-xs text-text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($isAtivo): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sucesso/20 text-sucesso">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sucesso"></span> Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-erro/20 text-erro">
                                            <span class="w-1.5 h-1.5 rounded-full bg-erro"></span> Desativado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php if ((int)$u['tem_adotante'] > 0): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= in_array('adotante', $perfisArr) ? 'bg-roxinhoFofo/30 text-roxinhoFofo' : 'bg-surface/50 text-text-muted line-through' ?>">
                                                Adotante
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($u['tipo_protetor'])): ?>
                                            <?php $tag = ($u['tipo_protetor'] === 'cnpj') ? 'ong' : 'protetor'; ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= in_array($tag, $perfisArr) ? 'bg-primary/30 text-primary' : 'bg-surface/50 text-text-muted line-through' ?>">
                                                <?= strtoupper($tag) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($u['tipo_atual'] === 'administrador' || in_array('administrador', $perfisArr)): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-aviso/30 text-aviso">
                                                Admin
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" onclick="abrirModalGerenciar(<?= $u['usuario_id'] ?>)" class="bg-surface border border-rosa-2 hover:bg-roxinhoFofo hover:text-white text-text-dark font-bold text-xs px-3 py-1.5 rounded-xl transition cursor-pointer">
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
            <div class="flex justify-between items-center px-4 py-3 border-t border-rosa-2 text-xs">
                <span class="text-text-muted">Página <?= $paginaAtual ?> de <?= $totalPaginas ?> (Total: <?= $total ?>)</span>
                <div class="flex gap-1">
                    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                        <a href="?pagina=<?= $p ?>&busca=<?= urlencode($filtros['busca']) ?>&status=<?= urlencode($filtros['status']) ?>&perfil=<?= urlencode($filtros['perfil']) ?>" class="px-3 py-1 rounded-lg font-bold <?= $p === $paginaAtual ? 'bg-roxinhoFofo text-white' : 'bg-surface border border-rosa-2 text-text-dark hover:bg-rosa-1/20' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL GERENCIAR USUÁRIO E PERFIS -->
<div id="modal-gerenciar" class="fixed inset-0 bg-preto/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface rounded-3xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto border border-rosa-2">
        <button type="button" onclick="fecharModalGerenciar()" class="absolute top-4 right-4 text-text-muted hover:text-text-dark text-xl font-bold transition">&times;</button>
        
        <div id="modal-conteudo">
            <div class="text-center py-6 text-text-muted text-sm">Carregando dados do usuário...</div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAÇÃO DE AÇÃO -->
<div id="modal-confirmacao" class="fixed inset-0 bg-preto/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface rounded-2xl max-w-sm w-full p-5 shadow-2xl text-center border border-rosa-2">
        <div id="confirma-icone" class="text-4xl mb-2">⚠️</div>
        <h3 id="confirma-titulo" class="font-bold text-lg text-text-dark mb-2">Confirmação</h3>
        <p id="confirma-texto" class="text-xs text-text-muted mb-6">Você tem certeza desta ação?</p>

        <div class="flex gap-2">
            <button type="button" onclick="fecharModalConfirmacao()" class="flex-1 btn-secundario text-sm py-2">Cancelar</button>
            <button type="button" id="btn-executar-acao" class="flex-1 btn-primario text-sm py-2">Confirmar</button>
        </div>
    </div>
</div>

<script>
let acaoPendente = null;

async function abrirModalGerenciar(usuarioId) {
    const modal = document.getElementById('modal-gerenciar');
    const container = document.getElementById('modal-conteudo');
    modal.classList.remove('hidden');
    container.innerHTML = '<div class="text-center py-8 text-text-muted text-sm">Carregando...</div>';

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
            <div class="p-3 bg-surface/50 rounded-xl flex items-center justify-between border border-rosa-2">
                <div>
                    <div class="font-bold text-xs text-text-dark">${p.nome}</div>
                    <div class="text-[10px] text-text-muted">${p.info}</div>
                    <span class="inline-block mt-1 text-[10px] font-bold ${p.ativo ? 'text-sucesso' : 'text-erro'}">
                        ● ${p.ativo ? 'ATIVO' : 'DESATIVADO'}
                    </span>
                </div>
                <div>
                    <button type="button" onclick="confirmarAlterarPerfil(${u.usuario_id}, '${p.tipo}', '${p.nome}', '${p.ativo ? 'desativar' : 'ativar'}')" class="text-[11px] font-bold px-3 py-1.5 rounded-lg transition ${p.ativo ? 'bg-erro/20 text-erro hover:bg-erro/30' : 'bg-sucesso/20 text-sucesso hover:bg-sucesso/30'}">
                        ${p.ativo ? 'Desativar ' + p.nome : 'Reativar ' + p.nome}
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = `
        <h2 class="font-shantell text-xl font-bold text-text-dark mb-1">${u.nome || 'Sem Nome'}</h2>
        <p class="text-xs text-text-muted mb-4">${u.email} &bull; Cadastrado em ${u.criado_em}</p>

        <!-- Status Global -->
        <div class="p-4 bg-roxinhoFofo/20 rounded-2xl mb-4 border border-roxinhoFofo/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-text-dark block">Status Global da Conta</span>
                <span class="text-xs font-extrabold ${isAtivo ? 'text-sucesso' : 'text-erro'}">
                    ● ${isAtivo ? 'USUÁRIO ATIVO' : 'USUÁRIO DESATIVADO'}
                </span>
            </div>
            <button type="button" onclick="confirmarAlterarUsuario(${u.usuario_id}, '${isAtivo ? 'desativar' : 'ativar'}')" class="text-xs font-bold px-3 py-2 rounded-xl transition ${isAtivo ? 'bg-erro text-white hover:bg-erro/80' : 'bg-sucesso text-white hover:bg-sucesso/80'}">
                ${isAtivo ? 'Desativar Conta' : 'Reativar Conta'}
            </button>
        </div>

        <!-- Lista de Perfis -->
        <h3 class="font-bold text-xs text-text-dark/70 uppercase tracking-wide mb-2">Perfis Vinculados</h3>
        <div class="space-y-2 mb-4">
            ${perfisHtml || '<p class="text-xs text-text-muted">Nenhum perfil específico vinculado.</p>'}
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