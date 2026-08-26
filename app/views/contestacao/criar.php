<?php
$advertencias = $advertencias ?? [];
$advertenciaIdPreSelecionada = $advertenciaIdPreSelecionada ?? 0;
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$pesoRotulo = ['leve' => 'Leve', 'media' => 'Média', 'grave' => 'Grave'];
?>

<div class="max-w-md lg:max-w-lg mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6">
        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Conteste a sua advertência</h1>
        <p class="text-sm text-text-muted mt-1">Explique o que aconteceu — nossa equipe vai revisar</p>
    </div>

    <?php if (empty($advertencias)): ?>
        <div class="text-center py-16">
            <span class="text-5xl block mb-3">✅</span>
            <p class="text-text-muted text-sm">Você não tem nenhuma advertência ativa para contestar no momento.</p>
        </div>
    <?php else: ?>
        <form method="POST" action="<?= $urlBase ?>/contestar/enviar" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label for="advertencia_id" class="block text-sm font-bold text-text-dark dark:text-white mb-2">Qual advertência você está contestando?</label>
                <select name="advertencia_id" id="advertencia_id" required
                        class="w-full p-3 border border-rosa-2 dark:border-preto3 rounded-xl bg-white dark:bg-preto1 text-text-dark dark:text-white outline-none focus:border-primary transition">
                    <?php foreach ($advertencias as $adv): ?>
                        <option value="<?= (int) $adv['advertencia_id'] ?>" <?= (int) $adv['advertencia_id'] === $advertenciaIdPreSelecionada ? 'selected' : '' ?>>
                            Advertência <?= htmlspecialchars($pesoRotulo[$adv['peso_status']] ?? $adv['peso_status']) ?> — <?= date('d/m/Y', strtotime($adv['criado_em'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="justificativa" class="block text-sm font-bold text-text-dark dark:text-white mb-2">Descreva a sua contestação</label>
                <textarea id="justificativa" name="justificativa" rows="5" required
                          placeholder="Escreva sua justificativa..."
                          class="w-full p-3 border border-rosa-2 dark:border-preto3 rounded-xl bg-white dark:bg-preto1 text-text-dark dark:text-white outline-none focus:border-primary transition"></textarea>
                <p class="text-xs text-text-muted mt-1">Submeta sua justificativa com o máximo de detalhes possível.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-text-dark dark:text-white mb-2">Anexo de prova (opcional)</label>
                <p class="text-xs text-text-muted mb-2">Ajude a equipe a verificar o que você está contestando — aceita PDF, JPG, PNG ou WEBP, até 5MB.</p>
                <input type="file" name="anexo" accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="w-full text-sm text-text-dark dark:text-white file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-rosa-1 file:text-text-dark file:font-bold file:text-xs hover:file:bg-rosa-2">
            </div>

            <button type="submit" class="w-full py-3 bg-laranja-1 hover:opacity-90 text-white rounded-full text-sm font-bold shadow transition">
                Enviar contestação
            </button>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
