<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$protetor = $protetor ?? [];
$redes = $redes ?? [];
$disponiveis = $disponiveis ?? [];
$adotados = $adotados ?? [];

$montarUrlFoto = function (?string $caminho) use ($urlBase): string {
    if (empty($caminho)) {
        return '';
    }
    return strpos($caminho, 'http') === 0 ? $caminho : $urlBase . '/' . ltrim($caminho, '/');
};

$fotoPerfilUrl = $montarUrlFoto($protetor['foto_perfil'] ?? null) ?: $urlBase . '/assets/img/perfil-placeholder.png';
$fotoFundoUrl = $montarUrlFoto($protetor['foto_fundo'] ?? null);

$linksRede = [];
foreach ($redes as $rede) {
    $linksRede[$rede['tipo_rede']] = $rede['link_rede'];
}
$iconesRede = ['instagram' => '📷', 'facebook' => '📘', 'whatsapp' => '💬', 'outro' => '🔗'];
?>

<div class="max-w-md mx-auto pb-20">
    <!-- Capa + avatar sobreposto -->
    <div class="relative h-40 sm:h-48 <?= $fotoFundoUrl ? '' : 'bg-gradient-to-r from-roxoApagado to-rosa-2 dark:from-preto2 dark:to-preto3' ?>">
        <?php if ($fotoFundoUrl): ?>
            <img src="<?= htmlspecialchars($fotoFundoUrl) ?>" alt="" class="w-full h-full object-cover">
        <?php endif; ?>

        <a href="<?= $urlBase ?>/" onclick="if(document.referrer){history.back();return false;}"
           class="absolute top-4 left-4 w-10 h-10 rounded-full bg-white/90 flex items-center justify-center text-text-dark shadow" aria-label="Voltar">
            &larr;
        </a>

        <button type="button" onclick="if(typeof mostrarModalFeedback === 'function') { mostrarModalFeedback('informativo', 'Denúncia de perfil ainda está sendo implementada. Em breve você poderá reportar ONGs/Protetores por aqui.'); } else { alert('Em breve!'); }"
                class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/90 flex items-center justify-center text-erro shadow" title="Denunciar">
            🚩
        </button>
    </div>

    <div class="flex flex-col items-center -mt-14 px-6">
        <img src="<?= htmlspecialchars($fotoPerfilUrl) ?>" alt="Foto de <?= htmlspecialchars($protetor['nome_fantasia'] ?? '') ?>"
             class="w-28 h-28 rounded-full border-4 border-branco dark:border-preto1 object-cover bg-surface dark:bg-preto2 shadow-md"
             onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">

        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white mt-3 text-center"><?= htmlspecialchars($protetor['nome_fantasia'] ?? 'ONG/Protetor') ?></h1>

        <!-- Redes sociais / contato -->
        <div class="flex items-center gap-4 mt-3 text-xl">
            <?php foreach (['instagram', 'facebook', 'whatsapp', 'outro'] as $tipo): ?>
                <?php if (!empty($linksRede[$tipo])): ?>
                    <a href="<?= htmlspecialchars($linksRede[$tipo]) ?>" target="_blank" rel="noopener" title="<?= ucfirst($tipo) ?>" class="hover:opacity-70 transition"><?= $iconesRede[$tipo] ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!empty($protetor['nome_regiao'])): ?>
                <span title="<?= htmlspecialchars($protetor['nome_regiao']) ?>">📍</span>
            <?php endif; ?>
            <?php if (!empty($protetor['chave_pix'])): ?>
                <button type="button" onclick="copiarChavePix(this)" data-chave="<?= htmlspecialchars($protetor['chave_pix']) ?>" title="Copiar chave PIX" class="hover:opacity-70 transition cursor-pointer">💠</button>
            <?php endif; ?>
        </div>

        <?php if (!empty($protetor['pagina_descricao'])): ?>
            <p class="text-sm text-text-muted text-center mt-4"><?= nl2br(htmlspecialchars($protetor['pagina_descricao'])) ?></p>
        <?php else: ?>
            <p class="text-sm text-text-muted italic text-center mt-4">Esta página ainda não tem uma descrição.</p>
        <?php endif; ?>

        <div class="flex items-center gap-10 mt-4">
            <div class="text-center">
                <p class="font-bold text-lg text-text-dark dark:text-white"><?= count($disponiveis) ?></p>
                <p class="text-xs text-text-muted">Animais em Adoção</p>
            </div>
            <div class="text-center">
                <p class="font-bold text-lg text-text-dark dark:text-white"><?= count($adotados) ?></p>
                <p class="text-xs text-text-muted">Animais Adotados</p>
            </div>
        </div>
    </div>

    <!-- Abas: disponíveis / adotados -->
    <div class="flex mt-6 bg-roxo2 dark:bg-primary">
        <button type="button" onclick="mostrarAbaCatalogo('disponiveis')" id="aba-btn-disponiveis" class="flex-1 py-3 text-white font-bold text-sm border-b-2 border-white">
            🐾 Disponíveis
        </button>
        <button type="button" onclick="mostrarAbaCatalogo('adotados')" id="aba-btn-adotados" class="flex-1 py-3 text-white/70 font-bold text-sm border-b-2 border-transparent">
            🏠 Adotados
        </button>
    </div>

    <!-- RN 18.5: catálogo exclusivo, só os disponíveis desta ONG/Protetor (e, como bônus, os já adotados) -->
    <div id="aba-disponiveis" class="grid grid-cols-3 gap-0.5 mt-0.5">
        <?php if (empty($disponiveis)): ?>
            <p class="col-span-3 text-center text-sm text-text-muted italic py-10">Nenhum animal disponível no momento.</p>
        <?php else: ?>
            <?php foreach ($disponiveis as $animal): ?>
                <?php
                    $foto = $montarUrlFoto($animal->getFotoPrincipal()) ?: $urlBase . '/assets/img/perfil-placeholder.png';
                ?>
                <a href="<?= $urlBase ?>/animal/mostrar?id=<?= $animal->getAnimalId() ?>" class="relative aspect-square block overflow-hidden group">
                    <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($animal->getNome()) ?>" class="w-full h-full object-cover group-hover:scale-105 transition" onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                    <span class="absolute bottom-1.5 left-1.5 w-6 h-6 rounded-full bg-sucesso flex items-center justify-center text-xs shadow">🐾</span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="aba-adotados" class="hidden grid grid-cols-3 gap-0.5 mt-0.5">
        <?php if (empty($adotados)): ?>
            <p class="col-span-3 text-center text-sm text-text-muted italic py-10">Nenhum animal adotado ainda.</p>
        <?php else: ?>
            <?php foreach ($adotados as $animal): ?>
                <?php
                    $foto = $montarUrlFoto($animal->getFotoPrincipal()) ?: $urlBase . '/assets/img/perfil-placeholder.png';
                ?>
                <a href="<?= $urlBase ?>/animal/mostrar?id=<?= $animal->getAnimalId() ?>" class="relative aspect-square block overflow-hidden group">
                    <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($animal->getNome()) ?>" class="w-full h-full object-cover grayscale-[30%] group-hover:scale-105 transition" onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                    <span class="absolute bottom-1.5 left-1.5 flex items-center gap-1 bg-rosaAlerta/90 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow">❤️ Adotado</span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function mostrarAbaCatalogo(aba) {
        const disponiveis = aba === 'disponiveis';
        document.getElementById('aba-disponiveis').classList.toggle('hidden', !disponiveis);
        document.getElementById('aba-adotados').classList.toggle('hidden', disponiveis);

        document.getElementById('aba-btn-disponiveis').classList.toggle('text-white', disponiveis);
        document.getElementById('aba-btn-disponiveis').classList.toggle('text-white/70', !disponiveis);
        document.getElementById('aba-btn-disponiveis').classList.toggle('border-white', disponiveis);
        document.getElementById('aba-btn-disponiveis').classList.toggle('border-transparent', !disponiveis);

        document.getElementById('aba-btn-adotados').classList.toggle('text-white', !disponiveis);
        document.getElementById('aba-btn-adotados').classList.toggle('text-white/70', disponiveis);
        document.getElementById('aba-btn-adotados').classList.toggle('border-white', !disponiveis);
        document.getElementById('aba-btn-adotados').classList.toggle('border-transparent', disponiveis);
    }

    function copiarChavePix(botao) {
        const chave = botao.dataset.chave;
        navigator.clipboard?.writeText(chave).then(function () {
            if (typeof mostrarModalFeedback === 'function') {
                mostrarModalFeedback('sucesso', 'Chave PIX copiada!');
            } else {
                alert('Chave PIX copiada: ' + chave);
            }
        }).catch(function () {
            alert('Chave PIX: ' + chave);
        });
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
