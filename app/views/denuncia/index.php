<?php
require_once __DIR__.'/../templates/header.php';
echo renderVoltar('/feed', 'Voltar ao feed');
?>
<section class="mx-auto max-w-4xl pb-8 text-text-dark" aria-labelledby="titulo-denuncia">
    <h1 id="titulo-denuncia" class="font-shantell text-2xl sm:text-3xl font-bold text-center mb-5">Como denunciar?</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-8 items-start">
        <div class="flex justify-center">
            <img src="<?= e(asset('assets/img/denuncia-policial.png')) ?>" alt="Cachorrinho policial com uma lupa" class="w-full max-w-[303px] h-auto object-contain">
        </div>
        <div class="min-w-0 rounded-3xl border border-rosa-3 bg-surface p-5 sm:p-7 shadow-sm">
            <h2 class="font-shantell text-xl font-bold">Ajude a cuidar da nossa comunidade</h2>
            <p class="mt-3 text-sm leading-relaxed">Encontrou um anúncio suspeito, informações falsas ou uma conduta inadequada? Envie seu relato para a equipe CãoNectados por e-mail.</p>
            <ol class="mt-5 space-y-3 list-decimal pl-5 text-sm leading-relaxed">
                <li>Informe o link do animal ou do perfil envolvido.</li>
                <li>Descreva o que aconteceu e quando, com os detalhes que você conhece.</li>
                <li>Anexe capturas de tela ou outros comprovantes e envie a mensagem.</li>
            </ol>
            <?php if ($linkAnimal): ?>
            <div class="mt-5 rounded-xl bg-primary/10 p-3 text-sm">
                <p class="font-semibold">Denúncia referente a <?= e($post['nome']) ?> · Pet #<?= (int)$post['animal_id'] ?></p>
                <p class="mt-1">Responsável: <?= e($post['nome_fantasia'] ?: 'Protetor independente') ?></p>
                <a href="<?= e($linkAnimal) ?>" class="mt-2 inline-flex min-h-11 items-center font-semibold underline">Ver anúncio selecionado</a>
            </div>
            <?php elseif ($postIndisponivel): ?>
            <p class="mt-5 text-sm" role="status">Este anúncio não está disponível. Você ainda pode enviar seu relato informando o link e anexando os comprovantes que possui.</p>
            <?php endif; ?>
            <details class="mt-5 text-sm">
                <summary class="cursor-pointer font-semibold min-h-11 flex items-center underline">Ver mensagem preparada</summary>
                <p class="mt-2 break-words"><strong>Assunto:</strong> <?= e($assuntoDenuncia) ?></p>
                <label for="mensagem-denuncia" class="block mt-3 mb-2">Texto para completar no e-mail (você também pode copiar):</label>
                <textarea id="mensagem-denuncia" readonly rows="12" class="w-full rounded-xl border border-primary/30 bg-background text-text-dark p-3"><?= e($corpoDenuncia) ?></textarea>
            </details>
            <a href="<?= e($linkEmail) ?>" class="btn-primario mt-6 flex min-h-11 items-center justify-center gap-2 text-center">Denunciar por e-mail</a>
            <p class="mt-3 text-xs leading-relaxed text-text-muted">O botão abre seu aplicativo de e-mail com uma mensagem para você completar. A denúncia só será enviada quando você confirmar o envio nele.</p>
            <p class="mt-5 text-sm">Se o aplicativo não abrir, escreva diretamente para:</p>
            <a href="<?= e($linkEmail) ?>" class="mt-2 block break-all font-semibold text-text-dark underline"><?= e($emailDenuncia) ?></a>
        </div>
    </div>
</section>
<?php require_once __DIR__.'/../templates/footer.php'; ?>
