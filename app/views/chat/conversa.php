<?php
$chat = $chat ?? [];
$mensagens = $mensagens ?? [];
$papelAtual = $papelAtual ?? ($chat['meu_papel'] ?? null);
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

function montarUrlFotoConversa(?string $caminho, string $urlBase): ?string
{
    if (empty($caminho)) {
        return null;
    }
    return str_starts_with($caminho, 'http') ? $caminho : $urlBase . '/' . ltrim($caminho, '/');
}

$souAdotante = $papelAtual === 'adotante';
$nomeContato = $souAdotante ? $chat['nome_fantasia'] : $chat['adotante_nome'];
$fotoContato = montarUrlFotoConversa($souAdotante ? ($chat['protetor_foto'] ?? null) : ($chat['adotante_foto'] ?? null), $urlBase);
$fotoAnimal = montarUrlFotoConversa($chat['animal_foto'] ?? null, $urlBase);
$fotoEuMesmo = montarUrlFotoConversa($souAdotante ? ($chat['adotante_foto'] ?? null) : ($chat['protetor_foto'] ?? null), $urlBase);
$dataVinculo = !empty($chat['data_vinculo']) ? date('d/m/Y', strtotime($chat['data_vinculo'])) : date('d/m/Y', strtotime($chat['criado_em']));
$chatAtivo = $chat['status'] === 'ativo';
$primeiraConversa = empty($mensagens);
?>

<div class="max-w-md lg:max-w-2xl mx-auto flex flex-col h-[calc(100dvh-4rem)] lg:h-[calc(100dvh-4rem)]">

    <!-- Cabeçalho da conversa -->
    <div class="flex items-center gap-3 px-4 sm:px-6 py-3 border-b border-rosa-2 dark:border-preto3 shrink-0">
        <a href="<?= $urlBase ?>/chats" class="text-text-dark dark:text-white text-xl leading-none">&larr;</a>
        <?php if ($fotoContato): ?>
            <img src="<?= htmlspecialchars($fotoContato) ?>" alt="" class="w-9 h-9 rounded-full object-cover">
        <?php else: ?>
            <div class="w-9 h-9 rounded-full bg-rosa-1 dark:bg-preto2 flex items-center justify-center text-sm">🐾</div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-text-dark dark:text-white truncate"><?= htmlspecialchars($nomeContato) ?></p>
            <p class="text-[11px] text-text-muted truncate">Você CãoNectou com <?= htmlspecialchars($nomeContato) ?> em <?= $dataVinculo ?></p>
        </div>
        <?php $contatoUsuarioId = $souAdotante ? (int) $chat['protetor_usuario_id'] : (int) $chat['adotante_usuario_id']; ?>
        <a href="<?= $urlBase ?>/denunciar?usuario_id=<?= $contatoUsuarioId ?>&chat_id=<?= (int) $chat['chat_id'] ?>"
           class="text-erro shrink-0" title="Denunciar">🚩</a>
        <?php if ($chatAtivo): ?>
            <form id="form-encerrar-chat" method="POST" action="<?= $urlBase ?>/chats/encerrar">
                <input type="hidden" name="id" value="<?= (int) $chat['chat_id'] ?>">
                <button type="button"
                        onclick="abrirModalConfirmacao('Encerrar conversa', 'Tem certeza que deseja encerrar esta conversa?', function () { document.getElementById('form-encerrar-chat').submit(); }, 'Encerrar', 'Cancelar')"
                        class="text-xs font-bold text-erro underline hover:opacity-80 shrink-0">Encerrar</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Área de mensagens -->
    <div id="area-mensagens" class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 space-y-3">
        <?php foreach ($mensagens as $msg): ?>
            <?php $ehMinha = $msg['remetente_perfil'] === $papelAtual; ?>
            <div class="flex <?= $ehMinha ? 'justify-end' : 'justify-start' ?>" data-mensagem-id="<?= (int) $msg['mensagem_id'] ?>">
                <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm <?= $ehMinha ? 'bg-primary text-white rounded-br-sm' : 'bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white rounded-bl-sm' ?>">
                    <?= nl2br(htmlspecialchars($msg['texto'])) ?>
                    <div class="text-[10px] mt-1 opacity-70"><?= date('H:i', strtotime($msg['data_hora'])) ?><?= $ehMinha ? ($msg['lida'] ? ' · lida' : ' · enviada') : '' ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Campo de envio -->
    <div class="px-4 sm:px-6 py-3 border-t border-rosa-2 dark:border-preto3 shrink-0">
        <?php if ($chatAtivo): ?>
            <form id="form-enviar-mensagem" class="flex items-center gap-2">
                <input type="text" id="input-mensagem" autocomplete="off" placeholder="Escreva uma mensagem..."
                       class="flex-1 bg-rosa-1/40 dark:bg-preto2 rounded-full px-4 py-2.5 text-sm outline-none text-text-dark dark:text-white placeholder-text-muted">
                <button type="submit" class="text-primary dark:text-roxinhoFofo font-bold text-sm px-3 shrink-0">Enviar</button>
            </form>
        <?php else: ?>
            <p class="text-center text-xs text-text-muted">Esta conversa foi encerrada.</p>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL "CãoNectou!" — só na primeira vez que a conversa (ainda sem mensagens) é aberta -->
<div id="modal-caonectou" class="<?= $primeiraConversa ? '' : 'hidden' ?> fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
    <div class="bg-branco dark:bg-preto1 rounded-3xl max-w-sm w-full p-6 text-center relative border border-rosa-3">
        <button type="button" onclick="document.getElementById('modal-caonectou').classList.add('hidden')" class="absolute top-4 right-4 text-text-muted hover:text-erro text-2xl leading-none">&times;</button>

        <h2 class="font-shantell text-2xl font-bold text-primary dark:text-roxinhoFofo mb-4">CãoNectou! 🐾</h2>

        <div class="flex items-center justify-center -space-x-3 mb-4">
            <?php if ($fotoEuMesmo): ?>
                <img src="<?= htmlspecialchars($fotoEuMesmo) ?>" class="w-16 h-16 rounded-full object-cover border-4 border-branco dark:border-preto1 shadow">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-rosa-1 dark:bg-preto2 border-4 border-branco dark:border-preto1 shadow flex items-center justify-center text-2xl">🙂</div>
            <?php endif; ?>

            <?php if ($fotoAnimal): ?>
                <img src="<?= htmlspecialchars($fotoAnimal) ?>" class="w-16 h-16 rounded-full object-cover border-4 border-branco dark:border-preto1 shadow z-10">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-rosa-1 dark:bg-preto2 border-4 border-branco dark:border-preto1 shadow flex items-center justify-center text-2xl z-10">🐾</div>
            <?php endif; ?>

            <?php if ($fotoContato): ?>
                <img src="<?= htmlspecialchars($fotoContato) ?>" class="w-16 h-16 rounded-full object-cover border-4 border-branco dark:border-preto1 shadow">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-rosa-1 dark:bg-preto2 border-4 border-branco dark:border-preto1 shadow flex items-center justify-center text-2xl">🐾</div>
            <?php endif; ?>
        </div>

        <p class="text-sm text-text-dark dark:text-white font-bold"><?= htmlspecialchars($chat['animal_nome']) ?></p>
        <p class="text-xs text-text-muted mb-6">Você e <?= htmlspecialchars($nomeContato) ?> têm tudo pra dar certo. O chat sobre <?= htmlspecialchars($chat['animal_nome']) ?> já está liberado!</p>

        <button type="button" onclick="document.getElementById('modal-caonectou').classList.add('hidden'); document.getElementById('input-mensagem')?.focus();"
                class="w-full py-3 bg-primary hover:opacity-90 text-white font-bold text-sm rounded-full shadow transition active:scale-95">
            ABRIR CHAT
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';

    const urlBase = '<?= $urlBase ?>';
    const chatId = <?= (int) $chat['chat_id'] ?>;
    const papelAtual = <?= json_encode($papelAtual) ?>;
    const area = document.getElementById('area-mensagens');
    const form = document.getElementById('form-enviar-mensagem');
    const input = document.getElementById('input-mensagem');

    let ultimoIdVisto = 0;
    document.querySelectorAll('[data-mensagem-id]').forEach(function (el) {
        ultimoIdVisto = Math.max(ultimoIdVisto, parseInt(el.dataset.mensagemId, 10) || 0);
    });

    function rolarParaFinal() {
        area.scrollTop = area.scrollHeight;
    }
    rolarParaFinal();

    function criarBalao(msg) {
        const ehMinha = msg.remetente_perfil === papelAtual;
        const div = document.createElement('div');
        div.className = `flex ${ehMinha ? 'justify-end' : 'justify-start'}`;
        div.dataset.mensagemId = msg.mensagem_id;

        const hora = new Date(msg.data_hora.replace(' ', 'T')).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        const texto = document.createElement('div');
        texto.textContent = msg.texto;

        div.innerHTML = `
            <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm ${ehMinha ? 'bg-primary text-white rounded-br-sm' : 'bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white rounded-bl-sm'}">
                <span class="texto-mensagem"></span>
                <div class="text-[10px] mt-1 opacity-70">${hora}${ehMinha ? (msg.lida == 1 ? ' · lida' : ' · enviada') : ''}</div>
            </div>
        `;
        div.querySelector('.texto-mensagem').textContent = msg.texto;

        area.appendChild(div);
        ultimoIdVisto = Math.max(ultimoIdVisto, parseInt(msg.mensagem_id, 10) || 0);
    }

    if (form) {
        form.addEventListener('submit', async function (evento) {
            evento.preventDefault();
            const texto = input.value.trim();
            if (!texto) return;

            input.disabled = true;

            try {
                const corpo = new URLSearchParams({ chat_id: chatId, texto });
                const resposta = await fetch(`${urlBase}/chats/enviar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' },
                    body: corpo
                });
                const resultado = await resposta.json();

                if (resultado.status === 'sucesso') {
                    criarBalao(resultado.mensagem);
                    rolarParaFinal();
                    input.value = '';
                } else if (typeof mostrarModalFeedback === 'function') {
                    mostrarModalFeedback('erro', resultado.mensagem || 'Não foi possível enviar a mensagem.');
                }
            } catch (erro) {
                if (typeof mostrarModalFeedback === 'function') {
                    mostrarModalFeedback('erro', 'Erro de conexão ao enviar a mensagem.');
                }
            } finally {
                input.disabled = false;
                input.focus();
            }
        });
    }

    // Polling leve: só busca o que chegou depois da última mensagem já vista nesta tela.
    async function verificarNovasMensagens() {
        try {
            const resposta = await fetch(`${urlBase}/chats/novas-mensagens?id=${chatId}&depois_de=${ultimoIdVisto}`, {
                headers: { Accept: 'application/json' }
            });
            const resultado = await resposta.json();

            if (resultado.status === 'sucesso' && resultado.mensagens.length) {
                resultado.mensagens.forEach(criarBalao);
                rolarParaFinal();
            }
        } catch (erro) {
            // Silencioso — o polling só tenta de novo no próximo intervalo.
        }
    }

    <?php if ($chatAtivo): ?>
    setInterval(verificarNovasMensagens, 4000);
    <?php endif; ?>
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
