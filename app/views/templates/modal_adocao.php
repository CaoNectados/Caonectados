<?php
// Incluído apenas no feed e em Minhas Solicitações.
$matchesAdocao = $matchesAdocao ?? [];
$modalPetisco = $modalPetisco ?? false;
$fotoAdocao = static function (?string $caminho): ?string {
    if (!$caminho) return null;
    $caminho = ltrim(trim($caminho), '/');
    if (!str_starts_with($caminho, 'assets/')) {
        $caminho = 'assets/uploads/'.preg_replace('#^(uploads/)+#', '', $caminho);
    }
    return rtrim(URL_BASE, '/').'/'.$caminho;
};
?>
<?php if ($modalPetisco): ?>
<dialog id="modal-petisco" class="adocao-modal box-border m-auto w-[calc(100%_-_1.5rem)] max-w-[390px] max-h-[calc(100dvh_-_2rem)] overflow-y-auto rounded-3xl border border-rosa-2 bg-surface px-5 pb-7 pt-12 text-center font-poppins text-text-dark shadow-xl backdrop:bg-preto/80 dark:border-preto3" aria-labelledby="petisco-titulo" aria-describedby="petisco-descricao" data-modal-adocao>
    <button type="button" class="adocao-fechar absolute right-2 top-2 inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent text-3xl leading-none text-primary transition hover:bg-rosa-1/40 dark:text-rosa-2" data-fechar-adocao aria-label="Fechar confirmação" autofocus>&times;</button>
    <h2 id="petisco-titulo" class="adocao-titulo mb-5 mt-3 font-shantell text-2xl font-bold text-primary sm:text-3xl dark:text-rosa-2">Petisco enviado!</h2>
    <img class="adocao-gif mx-auto mb-4 block h-auto max-h-[55dvh] w-full rounded-2xl bg-preto object-contain" data-gif-adocao src="<?= e(asset('assets/img/Caonectou.gif')) ?>" alt="Animação CãoNectou comemorando o envio do petisco" width="848" height="636">
    <p id="petisco-descricao" class="adocao-descricao mb-6 mt-4 text-sm leading-relaxed"></p>
    <a class="adocao-whatsapp btn-primario min-h-11 w-full whitespace-normal sm:w-auto" href="<?= e(URL_BASE.'/minhas-solicitacoes') ?>">Acompanhar solicitação</a>
</dialog>
<?php endif; ?>

<?php foreach ($matchesAdocao as $match):
    $matchId = (int)$match['solicitacao_id'];
    $whatsapp = \app\helpers\WhatsAppHelper::linkAdocao($match['protetor_telefone'] ?? null, $match['adotante_nome'] ?? '', $match['animal_nome']);
    $animalFoto = $fotoAdocao($match['animal_foto'] ?? null);
    $adotanteFoto = $fotoAdocao($match['adotante_foto'] ?? null);
    $protetorFoto = $fotoAdocao($match['protetor_foto'] ?? null);
    $autoKey = 'caonectou:'.(int)$_SESSION['usuario_id'].':'.$matchId.':'.($match['data_finalizacao'] ?? '');
?>
<dialog id="modal-caonectou-<?= $matchId ?>" class="adocao-modal box-border m-auto w-[calc(100%_-_1.5rem)] max-w-[390px] max-h-[calc(100dvh_-_2rem)] overflow-y-auto rounded-3xl border border-rosa-2 bg-surface px-5 pb-7 pt-12 text-center font-poppins text-text-dark shadow-xl backdrop:bg-preto/80 dark:border-preto3" aria-labelledby="caonectou-titulo-<?= $matchId ?>" aria-describedby="caonectou-descricao-<?= $matchId ?>" data-modal-adocao data-auto-key="<?= e($autoKey) ?>">
    <button type="button" class="adocao-fechar absolute right-2 top-2 inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent text-3xl leading-none text-primary transition hover:bg-rosa-1/40 dark:text-rosa-2" data-fechar-adocao aria-label="Fechar CãoNectou" autofocus>&times;</button>
    <h2 id="caonectou-titulo-<?= $matchId ?>" class="adocao-titulo mb-5 mt-3 font-shantell text-2xl font-bold text-primary sm:text-3xl dark:text-rosa-2">CãoNectou!</h2>
    <div class="adocao-fotos relative mb-3 flex justify-center">
        <?php foreach ([[$match['animal_nome'], $animalFoto], [$match['adotante_nome'], $adotanteFoto]] as [$nomeRetrato, $fotoRetrato]): ?>
        <div class="adocao-retrato relative grid aspect-square w-1/2 place-items-center overflow-hidden rounded-full border-[3px] border-rosa-2 bg-primary text-5xl text-white dark:border-rosa-3">
            <span aria-hidden="true"><?= e(mb_substr($nomeRetrato ?? '', 0, 1)) ?></span>
            <?php if ($fotoRetrato): ?><img class="absolute inset-0 h-full w-full object-cover" src="<?= e($fotoRetrato) ?>" alt="<?= e($nomeRetrato) ?>" data-foto-adocao><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <img src="<?= e(asset('assets/icons/geral/patinha-coracao.svg')) ?>" class="adocao-coracao absolute left-1/2 top-1/2 h-[90px] w-[90px] -translate-x-1/2 -translate-y-1/2" alt="">
    </div>
    <p class="adocao-nomes text-sm [overflow-wrap:anywhere]"><?= e($match['animal_nome']) ?> &amp; <?= e($match['adotante_nome']) ?></p>
    <div class="adocao-responsavel relative mt-3 mb-5 flex flex-col items-center gap-2 text-sm text-text-muted">
        <?php if ($protetorFoto): ?><img class="h-16 w-16 rounded-full border-[3px] border-rosa-2 bg-surface object-cover dark:border-rosa-3" src="<?= e($protetorFoto) ?>" alt="" data-foto-adocao><?php endif; ?>
        <span><?= e($match['nome_fantasia'] ?: 'Protetor independente') ?></span>
    </div>
    <p id="caonectou-descricao-<?= $matchId ?>" class="adocao-descricao mb-6 mt-4 text-sm leading-relaxed">Sua solicitação foi aceita! Converse com o responsável sobre a adoção de <?= e($match['animal_nome']) ?>.</p>
    <?php if ($whatsapp): ?>
        <a class="adocao-whatsapp btn-primario min-h-11 w-full whitespace-normal sm:w-auto" href="<?= e($whatsapp) ?>" target="_blank" rel="noopener noreferrer">ABRIR WHATSAPP</a>
    <?php else: ?>
        <p class="adocao-descricao mb-6 mt-4 text-sm leading-relaxed">O responsável ainda não possui um telefone válido cadastrado.</p>
        <a class="adocao-whatsapp btn-primario min-h-11 w-full whitespace-normal sm:w-auto" href="<?= e(URL_BASE.'/pagina?id='.(int)$match['protetor_id']) ?>">Ver página do responsável</a>
    <?php endif; ?>
</dialog>
<?php endforeach; ?>

<script>
(function () {
    'use strict';
    const modais = Array.from(document.querySelectorAll('[data-modal-adocao]'));
    const fechados = new Set();
    function foiFechado(modal) {
        if (fechados.has(modal.dataset.autoKey)) return true;
        try { return sessionStorage.getItem(modal.dataset.autoKey) === '1'; }
        catch (_) { return false; }
    }
    function abrir(modal) {
        if (!modal || modal.open) return;
        modal.showModal();
        const gif = modal.querySelector('[data-gif-adocao]');
        if (gif) gif.src = gif.getAttribute('src');
    }
    function proximo() {
        if (document.querySelector('dialog[open]')) return;
        abrir(modais.find(modal => modal.dataset.autoKey && !foiFechado(modal)));
    }
    modais.forEach(modal => {
        modal.querySelector('[data-fechar-adocao]').addEventListener('click', () => modal.close());
        modal.addEventListener('click', event => {
            const r = modal.getBoundingClientRect();
            if (event.target === modal && (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom)) modal.close();
        });
        modal.addEventListener('close', () => {
            if (!modal.dataset.autoKey) return;
            fechados.add(modal.dataset.autoKey);
            try { sessionStorage.setItem(modal.dataset.autoKey, '1'); } catch (_) {}
            proximo();
        });
        modal.querySelectorAll('[data-foto-adocao]').forEach(img => {
            img.addEventListener('error', () => { img.hidden = true; });
        });
    });
    document.querySelectorAll('[data-abrir-adocao]').forEach(botao => {
        botao.addEventListener('click', () => abrir(document.getElementById(botao.dataset.abrirAdocao)));
    });
    window.mostrarModalPetisco = function (mensagem) {
        const modal = document.getElementById('modal-petisco');
        if (!modal) return;
        modal.querySelector('#petisco-descricao').textContent = mensagem;
        abrir(modal);
    };
    document.addEventListener('DOMContentLoaded', proximo);
})();
</script>
