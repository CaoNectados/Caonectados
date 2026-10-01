<?php require __DIR__.'/../templates/header.php'; ?>
<main class="max-w-xl mx-auto p-6 card-padrao">
<h1 class="font-shantell text-xl">Classificação de <?= htmlspecialchars($protetor['nome_fantasia']) ?></h1>
<p>Classificação atual: <?= !empty($protetor['inadimplente']) ? 'Inadimplente' : 'Regular' ?></p>
<p>Registre a decisão administrativa e sua justificativa. O bloqueio global permanece uma ação separada.</p>
<form method="POST" action="<?= URL_BASE ?>/admin/protetores/classificacao" class="space-y-4 mt-4">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<input type="hidden" name="usuario_id" value="<?= (int)$usuarioId ?>">
<label for="classificacao">Classificação</label>
<select name="inadimplente" id="classificacao" class="input-padrao"><option value="0">Regular</option><option value="1" <?= !empty($protetor['inadimplente']) ? 'selected' : '' ?>>Inadimplente</option></select>
<label for="motivo">Justificativa</label>
<textarea name="motivo" id="motivo" required maxlength="2000" class="input-padrao"><?= htmlspecialchars($protetor['motivo_inadimplencia'] ?? '') ?></textarea>
<button class="btn-primario" type="submit">Registrar decisão</button>
<a href="<?= URL_BASE ?>/admin/gerenciar-usuarios">Voltar</a>
</form></main>
<?php require __DIR__.'/../templates/footer.php'; ?>
