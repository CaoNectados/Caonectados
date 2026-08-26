<?php
$notificacoes = $notificacoes ?? [];
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$iconesPorTipo = [
    'solicitacao'  => '🐾',
    'mensagem'     => '💬',
    'denuncia'     => '🚩',
    'contestacao'  => '⚖️',
    'advertencia'  => '⚠️',
    'sistema'      => 'ℹ️',
];

// Hoje / Ontem / Mais antigas — a lista já vem ordenada por criado_em DESC, então dá pra
// agrupar com uma simples troca de rótulo conforme percorre (sem reordenar nada).
function grupoDataNotificacao(string $criadoEm): string
{
    $data = new DateTime($criadoEm);
    $hoje = new DateTime('today');
    $ontem = (clone $hoje)->modify('-1 day');

    if ($data->format('Y-m-d') === $hoje->format('Y-m-d')) {
        return 'Hoje';
    }
    if ($data->format('Y-m-d') === $ontem->format('Y-m-d')) {
        return 'Ontem';
    }
    return 'Alguns dias atrás';
}
?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6">
        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Notificações</h1>
    </div>

    <div id="lista-notificacoes" class="space-y-5">
        <?php if (empty($notificacoes)): ?>
            <div class="text-center py-16">
                <span class="text-5xl block mb-3">🔔</span>
                <p class="text-text-muted text-sm">Você ainda não tem notificações.</p>
            </div>
        <?php else: ?>
            <?php
                // Pré-agrupa em vez de abrir/fechar <div> condicionalmente no meio do loop — a
                // lista já vem ordenada por criado_em DESC, então as chaves deste array saem
                // naturalmente na ordem certa (Hoje, Ontem, Alguns dias atrás).
                $grupos = [];
                foreach ($notificacoes as $notif) {
                    $grupos[grupoDataNotificacao($notif['criado_em'])][] = $notif;
                }
            ?>
            <?php foreach ($grupos as $rotuloGrupo => $itensGrupo): ?>
                <div>
                    <h2 class="font-shantell text-sm font-bold text-text-muted mb-2"><?= htmlspecialchars($rotuloGrupo) ?></h2>
                    <div class="space-y-3">
                        <?php foreach ($itensGrupo as $notif): ?>
                            <?php
                                $lida = (bool) $notif['lida'];
                                $icone = $iconesPorTipo[$notif['tipo_notificacao']] ?? '🔔';
                            ?>
                            <a href="<?= $urlBase ?>/notificacoes/abrir?id=<?= (int) $notif['notificacao_id'] ?>"
                               class="block rounded-2xl px-4 py-3 border-l-4 transition hover:opacity-90 <?= $lida
                                    ? 'bg-gray-100 dark:bg-preto2/60 border-transparent'
                                    : 'bg-white dark:bg-preto1 border-primary dark:border-roxinhoFofo shadow-sm' ?>">
                                <div class="flex items-start gap-3">
                                    <span class="text-xl shrink-0 leading-none mt-0.5"><?= $icone ?></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm <?= $lida ? 'text-text-muted' : 'text-text-dark dark:text-white font-bold' ?>">
                                            <?= htmlspecialchars($notif['txt_notificacao']) ?>
                                        </p>
                                        <p class="text-[11px] text-text-muted mt-1"><?= date('H:i', strtotime($notif['criado_em'])) ?></p>
                                    </div>
                                    <?php if (!$lida): ?>
                                        <span class="w-2 h-2 rounded-full bg-primary dark:bg-roxinhoFofo shrink-0 mt-1.5"></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($temMais)): ?>
        <div class="text-center mt-6">
            <button type="button" id="btn-carregar-mais" data-offset="<?= (int) $proximoOffset ?>"
                    class="text-sm font-bold text-primary dark:text-roxinhoFofo underline hover:opacity-80">
                Ver mais...
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    'use strict';

    const urlBase = '<?= $urlBase ?>';
    const btnCarregarMais = document.getElementById('btn-carregar-mais');
    const lista = document.getElementById('lista-notificacoes');

    const icones = {
        solicitacao: '🐾', mensagem: '💬', denuncia: '🚩',
        contestacao: '⚖️', advertencia: '⚠️', sistema: 'ℹ️'
    };

    function formatarHora(dataMysql) {
        const data = new Date(dataMysql.replace(' ', 'T'));
        return isNaN(data.getTime()) ? '' : data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    function criarCard(notif) {
        const lida = !!parseInt(notif.lida, 10);
        const icone = icones[notif.tipo_notificacao] || '🔔';

        const link = document.createElement('a');
        link.href = `${urlBase}/notificacoes/abrir?id=${notif.notificacao_id}`;
        link.className = `block rounded-2xl px-4 py-3 border-l-4 transition hover:opacity-90 ${lida ? 'bg-gray-100 dark:bg-preto2/60 border-transparent' : 'bg-white dark:bg-preto1 border-primary dark:border-roxinhoFofo shadow-sm'}`;

        const textoClasse = lida ? 'text-text-muted' : 'text-text-dark dark:text-white font-bold';
        const bolinha = lida ? '' : '<span class="w-2 h-2 rounded-full bg-primary dark:bg-roxinhoFofo shrink-0 mt-1.5"></span>';

        link.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="text-xl shrink-0 leading-none mt-0.5">${icone}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm ${textoClasse}"><span class="texto-notif"></span></p>
                    <p class="text-[11px] text-text-muted mt-1">${formatarHora(notif.criado_em)}</p>
                </div>
                ${bolinha}
            </div>
        `;
        link.querySelector('.texto-notif').textContent = notif.txt_notificacao;

        return link;
    }

    if (btnCarregarMais) {
        btnCarregarMais.addEventListener('click', async function () {
            const offset = parseInt(btnCarregarMais.dataset.offset, 10) || 0;
            btnCarregarMais.disabled = true;
            btnCarregarMais.textContent = 'Carregando...';

            try {
                const resposta = await fetch(`${urlBase}/notificacoes/carregar-mais?offset=${offset}`, {
                    headers: { Accept: 'application/json' }
                });
                const resultado = await resposta.json();

                if (resultado.status === 'sucesso') {
                    let grupoContainer = document.createElement('div');
                    grupoContainer.className = 'space-y-3';
                    if (offset === <?= (int) ($proximoOffset ?? 0) ?>) {
                        const titulo = document.createElement('h2');
                        titulo.className = 'font-shantell text-sm font-bold text-text-muted mb-2 mt-5';
                        titulo.textContent = 'Mais antigas';
                        lista.appendChild(titulo);
                    }
                    resultado.notificacoes.forEach(function (notif) {
                        grupoContainer.appendChild(criarCard(notif));
                    });
                    lista.appendChild(grupoContainer);

                    if (resultado.temMais) {
                        btnCarregarMais.dataset.offset = resultado.proximoOffset;
                        btnCarregarMais.disabled = false;
                        btnCarregarMais.textContent = 'Ver mais...';
                    } else {
                        btnCarregarMais.remove();
                    }
                }
            } catch (erro) {
                btnCarregarMais.disabled = false;
                btnCarregarMais.textContent = 'Ver mais...';
                if (typeof mostrarModalFeedback === 'function') {
                    mostrarModalFeedback('erro', 'Não foi possível carregar mais notificações.');
                }
            }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
