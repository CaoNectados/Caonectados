<?php
$conversas = $conversas ?? [];
$papelAtual = $papelAtual ?? null;
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

function montarUrlFotoChat(?string $caminho, string $urlBase): ?string
{
    if (empty($caminho)) {
        return null;
    }
    return str_starts_with($caminho, 'http') ? $caminho : $urlBase . '/' . ltrim($caminho, '/');
}
?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6">
        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Conversas</h1>
        <p class="text-sm text-text-muted mt-1">Fale com quem CãoNectou com você</p>
    </div>

    <?php if (empty($conversas)): ?>
        <div class="text-center py-16">
            <span class="text-5xl block mb-3">💬</span>
            <p class="text-text-muted text-sm">Nenhuma conversa ainda. Elas aparecem aqui assim que uma adoção é aprovada.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($conversas as $chat): ?>
                <?php
                    $souAdotante = $papelAtual === 'adotante';
                    $nomeContato = $souAdotante ? $chat['nome_fantasia'] : $chat['adotante_nome'];
                    $fotoContato = montarUrlFotoChat($souAdotante ? ($chat['protetor_foto'] ?? null) : ($chat['adotante_foto'] ?? null), $urlBase);
                    $naoLidas = (int) ($chat['nao_lidas'] ?? 0);
                ?>
                <a href="<?= $urlBase ?>/chats/conversa?id=<?= (int) $chat['chat_id'] ?>"
                   class="flex items-center gap-3 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl p-3 shadow-sm hover:shadow-md transition">
                    <?php if ($fotoContato): ?>
                        <img src="<?= htmlspecialchars($fotoContato) ?>" alt="" class="w-12 h-12 rounded-full object-cover shrink-0">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full bg-rosa-1 dark:bg-preto2 flex items-center justify-center text-lg shrink-0">🐾</div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-bold text-text-dark dark:text-white truncate"><?= htmlspecialchars($nomeContato) ?></p>
                            <?php if ($naoLidas > 0): ?>
                                <span class="shrink-0 min-w-[1.25rem] h-5 px-1.5 rounded-full bg-rosaAlerta text-white text-[11px] font-bold flex items-center justify-center"><?= $naoLidas > 9 ? '9+' : $naoLidas ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-text-muted truncate">
                            <?= htmlspecialchars($chat['ultima_mensagem'] ?? ('Sobre ' . $chat['animal_nome'] . ' — diga oi!')) ?>
                        </p>
                        <?php if ($chat['status'] !== 'ativo'): ?>
                            <span class="inline-block mt-1 text-[10px] font-bold text-text-muted">Conversa encerrada</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
