<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="mx-auto max-w-figma p-4 sm:p-6 lg:p-8 min-h-screen">
    <!-- Cabeçalho -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-text-dark dark:text-white flex items-center gap-2">
                <?= icone('users', 'h-8 w-8') ?> Gerenciar Usuários
            </h1>
            <p class="text-sm text-text-muted mt-1">Controle global de contas e status individual de perfis.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= URL_BASE ?>/admin/dashboard" class="btn-secundario text-xs sm:text-sm whitespace-nowrap inline-flex items-center gap-1.5">
                <?= icone('arrow-left', 'h-4 w-4') ?> Voltar ao Painel
            </a>
        </div>
    </div>

    <!-- Filtros Rápidos (Estilo Figma - Cards Rosa Pastel) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <a href="?perfil=adotante" class="p-4 rounded-2xl border border-rosa-3 bg-rosa-1/60 dark:bg-preto2 text-center shadow-sm hover:scale-105 transition duration-200">
            <span class="flex justify-center mb-1"><?= icone('users', 'h-6 w-6') ?></span>
            <span class="text-xs font-bold text-text-dark dark:text-white">Adotantes</span>
        </a>
        <a href="?perfil=ong" class="p-4 rounded-2xl border border-rosa-3 bg-rosa-1/60 dark:bg-preto2 text-center shadow-sm hover:scale-105 transition duration-200">
            <span class="flex justify-center mb-1"><?= icone('building', 'h-6 w-6') ?></span>
            <span class="text-xs font-bold text-text-dark dark:text-white">ONGs</span>
        </a>
        <a href="?perfil=protetor" class="p-4 rounded-2xl border border-rosa-3 bg-rosa-1/60 dark:bg-preto2 text-center shadow-sm hover:scale-105 transition duration-200">
            <span class="flex justify-center mb-1"><?= icone('paw', 'h-6 w-6') ?></span>
            <span class="text-xs font-bold text-text-dark dark:text-white">Protetores</span>
        </a>
        <a href="?status=inativo" class="p-4 rounded-2xl border border-rosaAlerta/40 bg-rosaAlerta/10 dark:bg-preto2 text-center shadow-sm hover:scale-105 transition duration-200">
            <span class="flex justify-center mb-1"><?= icone('ban', 'h-6 w-6') ?></span>
            <span class="text-xs font-bold text-rosaAlerta">Banidos</span>
        </a>
    </div>

    <!-- Barra de Busca e Filtros Integrada -->
    <div class="card-padrao mb-6 border border-rosa-1 dark:border-preto3 p-4 bg-surface dark:bg-surface">
        <form method="GET" action="<?= URL_BASE ?>/admin/gerenciar-usuarios" class="space-y-4">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-text-muted"><?= icone('search', 'h-4 w-4') ?></span>
                <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Buscar por nome, e-mail ou instituição..." class="input-padrao pl-11 py-2.5 text-sm w-full bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-cinzaMarrom/20">
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <!-- Status da Conta -->
                    <select name="status" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                        <option value="">Status: Todos</option>
                        <option value="ativo" <?= (($filtros['status'] ?? '') === 'ativo') ? 'selected' : '' ?>>Contas Ativas</option>
                        <option value="inativo" <?= (($filtros['status'] ?? '') === 'inativo') ? 'selected' : '' ?>>Banidos / Inativos</option>
                    </select>

                    <!-- Perfil -->
                    <select name="perfil" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                        <option value="">Perfil: Todos</option>
                        <option value="adotante" <?= (($filtros['perfil'] ?? '') === 'adotante') ? 'selected' : '' ?>>Adotante</option>
                        <option value="protetor" <?= (($filtros['perfil'] ?? '') === 'protetor') ? 'selected' : '' ?>>Protetor</option>
                        <option value="ong" <?= (($filtros['perfil'] ?? '') === 'ong') ? 'selected' : '' ?>>ONG</option>
                        <option value="administrador" <?= (($filtros['perfil'] ?? '') === 'administrador') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="btn-primario text-xs py-2 px-4 whitespace-nowrap">
                        Filtrar
                    </button>
                    <a href="<?= URL_BASE ?>/admin/gerenciar-usuarios" class="btn-secundario text-xs py-2 px-4 whitespace-nowrap bg-white dark:bg-preto1 text-text-dark dark:text-white">
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de Usuários Cadastrados -->
    <div class="mb-4">
        <h2 class="text-lg font-poppins font-bold text-text-dark dark:text-white">Usuários cadastrados</h2>
        <p class="text-xs text-text-muted">Clique em um usuário para gerenciar ações administrativas.</p>
    </div>

    <div class="bg-surface dark:bg-surface rounded-2xl border border-rosa-3/60 shadow-sm overflow-hidden divide-y divide-cinzaMarrom/20">
        <?php if (empty($usuarios)): ?>
            <div class="p-10 text-center">
                <span class="flex justify-center mb-2 opacity-50"><?= icone('users', 'h-10 w-10') ?></span>
                <p class="text-text-muted font-poppins">Nenhum usuário encontrado com os filtros selecionados.</p>
            </div>
        <?php else: ?>
            <?php foreach ($usuarios as $u): ?>
                <?php
                $isAtivo = ($u['status_conta'] === 'ativo');
                $perfisArr = array_filter(array_map('trim', explode(',', strtolower($u['perfis_ativos'] ?? ''))));
                ?>
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-rosa-2 dark:hover:bg-preto3 transition">
                    <div class="flex items-center gap-3.5">
                        <?php
                        $fotoNome = $u['foto_perfil'] ?? null;
                        if (!empty($fotoNome)) {
                            $fotoLimpa = ltrim(trim($fotoNome), '/');
                            $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
                            $caminhoFoto = URL_BASE . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
                        } else {
                            $caminhoFoto = URL_BASE . '/assets/img/perfil-placeholder.png';
                        }
                        ?>
                        <img src="<?= $caminhoFoto ?>"
                            alt="<?= htmlspecialchars($u['nome'] ?? 'Usuário') ?>"
                            class="h-12 w-12 rounded-full object-cover border border-rosa-3 flex-shrink-0 bg-white"
                            onerror="this.onerror=null; this.src='<?= URL_BASE ?>/assets/img/perfil-placeholder.png';">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-text-dark dark:text-white">
                                    <?= htmlspecialchars($u['nome'] ?? 'Sem Nome') ?>
                                </h3>
                                <?php if ($isAtivo): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sucesso/20 text-sucesso">Ativo</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rosaAlerta/20 text-rosaAlerta">Banido</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-text-muted"><?= htmlspecialchars($u['email']) ?></p>

                            <div class="flex flex-wrap gap-1 mt-1.5">
                                <?php if ((int)$u['tem_adotante'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amarelo/20 text-laranja-2">Adotante</span>
                                <?php endif; ?>
                                <?php if (!empty($u['tipo_protetor'])): ?>
                                    <?php $tag = ($u['tipo_protetor'] === 'cnpj') ? 'ONG' : 'Protetor'; ?>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-azul/20 text-azulEscuro dark:text-azul"><?= $tag ?></span>
                                <?php endif; ?>
                                <?php if ($u['tipo_atual'] === 'administrador' || in_array('administrador', $perfisArr)): ?>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-roxinhoFofo/20 text-primary dark:text-roxinhoFofo">Admin</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-cinzaMarrom/20">
                        <div class="text-xs text-text-muted text-right">
                            <span class="block">Cadastrado em</span>
                            <strong class="text-text-dark dark:text-white"><?= date('d/m/Y', strtotime($u['criado_em'] ?? 'now')) ?></strong>
                        </div>

                        <button type="button" onclick="abrirModalGerenciar(<?= $u['usuario_id'] ?>)" class="btn-primario text-xs py-2 px-3.5 whitespace-nowrap inline-flex items-center gap-1.5">
                            <?= icone('cog', 'h-4 w-4') ?> Gerenciar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Paginação -->
    <?php 
        $paginaAtual = $paginaAtual ?? (int)($filtros['pagina'] ?? 1);
        $totalPaginas = $totalPaginas ?? 1;
        $total = $total ?? count($usuarios ?? []);
    ?>
    <?php if ($totalPaginas > 1): ?>
        <div class="flex justify-between items-center px-4 py-4 mt-4 bg-surface dark:bg-surface rounded-xl border border-cinzaMarrom/30 text-xs">
            <span class="text-text-muted">
                Página <?= $paginaAtual ?> de <?= $totalPaginas ?> (Total: <?= $total ?>)
            </span>
            <div class="flex gap-1">
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <a href="?pagina=<?= $p ?>&busca=<?= urlencode($filtros['busca'] ?? '') ?>&status=<?= urlencode($filtros['status'] ?? '') ?>&perfil=<?= urlencode($filtros['perfil'] ?? '') ?>" 
                       class="px-3 py-1 rounded-lg font-bold <?= $p === $paginaAtual ? 'bg-primary text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white border border-cinzaMarrom/30' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL GERENCIAR USUÁRIO E PERFIS -->
<div id="modal-gerenciar" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="card-padrao bg-surface dark:bg-surface rounded-3xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto border border-rosa-3">
        <button type="button" onclick="fecharModalGerenciar()" class="absolute top-4 right-4 text-text-muted hover:text-text-dark dark:hover:text-white text-2xl font-bold">&times;</button>

        <div id="modal-conteudo">
            <div class="text-center py-6 text-text-muted text-sm">Carregando dados do usuário...</div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAÇÃO DE AÇÃO -->
<div id="modal-confirmacao" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="card-padrao bg-surface dark:bg-surface rounded-2xl max-w-sm w-full p-6 shadow-2xl text-center border border-rosa-3">
        <div id="confirma-icone" class="flex justify-center mb-2 text-amber-500"><?= icone('warning', 'h-10 w-10') ?></div>
        <h3 id="confirma-titulo" class="font-bold text-lg text-text-dark dark:text-white mb-2">Confirmação</h3>
        <p id="confirma-texto" class="text-xs text-text-muted mb-6">Você tem certeza desta ação?</p>

        <div class="flex gap-2">
            <button type="button" onclick="fecharModalConfirmacao()" class="btn-secundario flex-1 text-xs py-2.5">Cancelar</button>
            <button type="button" id="btn-executar-acao" class="btn-primario flex-1 text-xs py-2.5">Confirmar</button>
        </div>
    </div>
</div>

<script>
    const ICONE_AVISO = <?= json_encode(icone('warning', 'h-10 w-10')) ?>;
    const ICONE_BAN = <?= json_encode(icone('ban', 'h-10 w-10')) ?>;
    const ICONE_CHECK = <?= json_encode(icone('check-circle', 'h-10 w-10')) ?>;
    const ICONE_REFRESH = <?= json_encode(icone('refresh', 'h-10 w-10')) ?>;

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
                alert(res.mensagem);
                fecharModalGerenciar();
                return;
            }

            renderizarDetalhesModal(res.dados);
        } catch (e) {
            alert('Falha ao buscar dados do usuário.');
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
            <div class="p-3 bg-branco dark:bg-preto1 rounded-xl flex items-center justify-between border border-cinzaMarrom/30">
                <div>
                    <div class="font-bold text-xs text-text-dark dark:text-white">${p.nome}</div>
                    <div class="text-[10px] text-text-muted">${p.info}</div>
                    <span class="inline-block mt-1 text-[10px] font-bold ${p.ativo ? 'text-sucesso' : 'text-rosaAlerta'}">
                        ● ${p.ativo ? 'ATIVO' : 'DESATIVADO'}
                    </span>
                </div>
                <div>
                    <button type="button" onclick="confirmarAlterarPerfil(${u.usuario_id}, '${p.tipo}', '${p.nome}', '${p.ativo ? 'desativar' : 'ativar'})" class="text-[11px] font-bold px-3 py-1.5 rounded-xl transition ${p.ativo ? 'bg-rosaAlerta/10 text-rosaAlerta hover:bg-rosaAlerta hover:text-white' : 'bg-sucesso/10 text-sucesso hover:bg-sucesso hover:text-white'}">
                        ${p.ativo ? 'Desativar' : 'Reativar'}
                    </button>
                </div>
            </div>
        `;
        });

        container.innerHTML = `
        <h2 class="text-xl font-bold text-text-dark dark:text-white mb-1">${u.nome || 'Sem Nome'}</h2>
        <p class="text-xs text-text-muted mb-4">${u.email} &bull; Cadastrado em ${u.criado_em}</p>

        <!-- Status Global -->
        <div class="p-4 bg-rosa-1/60 dark:bg-preto1 rounded-2xl mb-4 border border-rosa-3 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-text-dark dark:text-white block">Status Global da Conta</span>
                <span class="text-xs font-extrabold ${isAtivo ? 'text-sucesso' : 'text-rosaAlerta'}">
                    ● ${isAtivo ? 'USUÁRIO ATIVO' : 'USUÁRIO DESATIVADO'}
                </span>
            </div>
            <button type="button" onclick="confirmarAlterarUsuario(${u.usuario_id}, '${isAtivo ? 'desativar' : 'ativar'})" class="text-xs font-bold px-3 py-2 rounded-xl transition ${isAtivo ? 'bg-rosaAlerta text-white hover:opacity-90' : 'bg-sucesso text-white hover:opacity-90'}">
                ${isAtivo ? 'Desativar Conta' : 'Reativar Conta'}
            </button>
        </div>

        <!-- Lista de Perfis -->
        <h3 class="font-bold text-xs text-text-muted uppercase tracking-wide mb-2">Perfis Vinculados</h3>
        <div class="space-y-2 mb-4">
            ${perfisHtml || '<p class="text-xs text-text-muted italic">Nenhum perfil específico vinculado.</p>'}
        </div>
    `;
    }

    function fecharModalGerenciar() {
        document.getElementById('modal-gerenciar').classList.add('hidden');
    }

    function confirmarAlterarUsuario(usuarioId, acao) {
        const isDesativar = (acao === 'desativar');
        const iconeEl = document.getElementById('confirma-icone');
        iconeEl.innerHTML = isDesativar ? ICONE_BAN : ICONE_CHECK;
        iconeEl.className = isDesativar ? 'flex justify-center mb-2 text-rosaAlerta' : 'flex justify-center mb-2 text-sucesso';
        document.getElementById('confirma-titulo').innerText = isDesativar ? 'Desativar Usuário?' : 'Reativar Usuário?';
        document.getElementById('confirma-texto').innerText = isDesativar ?
            'Ao desativar este usuário, ele não poderá mais realizar login nem utilizar qualquer funcionalidade na plataforma.' :
            'O usuário poderá voltar a acessar a plataforma com seus perfis ativos normalmente.';

        acaoPendente = async () => {
            const formData = new FormData();
            formData.append('usuario_id', usuarioId);
            formData.append('acao', acao);

            const resp = await fetch('<?= URL_BASE ?>/admin/usuarios/alterar-status', {
                method: 'POST',
                body: formData
            });
            const res = await resp.json();

            fecharModalConfirmacao();
            if (res.status === 'sucesso') {
                window.location.reload();
            } else {
                alert(res.mensagem);
            }
        };

        document.getElementById('modal-confirmacao').classList.remove('hidden');
    }

    function confirmarAlterarPerfil(usuarioId, tipoPerfil, nomePerfil, acao) {
        const isDesativar = (acao === 'desativar');
        const iconeEl = document.getElementById('confirma-icone');
        iconeEl.innerHTML = isDesativar ? ICONE_AVISO : ICONE_REFRESH;
        iconeEl.className = isDesativar ? 'flex justify-center mb-2 text-amber-500' : 'flex justify-center mb-2 text-primary';
        document.getElementById('confirma-titulo').innerText = `${isDesativar ? 'Desativar' : 'Reativar'} Perfil ${nomePerfil}?`;
        document.getElementById('confirma-texto').innerText = isDesativar ?
            `O usuário não poderá mais utilizar os recursos deste perfil, mas continuará acessando a plataforma caso possua outros perfis ativos.` :
            `O perfil ${nomePerfil} voltará a ficar disponível para o usuário.`;

        acaoPendente = async () => {
            const formData = new FormData();
            formData.append('usuario_id', usuarioId);
            formData.append('tipo_perfil', tipoPerfil);
            formData.append('acao', acao);

            const resp = await fetch('<?= URL_BASE ?>/admin/usuarios/alterar-status-perfil', {
                method: 'POST',
                body: formData
            });
            const res = await resp.json();

            fecharModalConfirmacao();
            if (res.status === 'sucesso') {
                abrirModalGerenciar(usuarioId);
            } else {
                alert(res.mensagem);
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