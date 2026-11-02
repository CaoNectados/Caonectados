<?php
$solicitacoes = $solicitacoes ?? [];
require_once __DIR__ . '/../templates/header.php';
echo renderVoltar('/perfil');

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$statusLabels = [
    'pendente'   => ['texto' => 'Enviada', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'em_analise' => ['texto' => 'Em análise', 'classe' => 'bg-laranja-1/20 text-laranja-1'],
    'aprovada'   => ['texto' => 'Aprovada', 'classe' => 'bg-sucesso/15 text-sucesso'],
    'reprovada'  => ['texto' => 'Recusada', 'classe' => 'bg-erro/10 text-erro'],
    'cancelada'  => ['texto' => 'Cancelada', 'classe' => 'bg-cinzaMarrom/20 dark:bg-preto2 text-text-muted'],
];

$adocaoConcluida = static fn(array $solicitacao): bool =>
    ($solicitacao['status_solicitacao'] ?? '') === 'aprovada'
    && ($solicitacao['animal_status'] ?? '') === 'adotado';

$solicitacaoCaoNectou = null;
foreach ($solicitacoes as $item) {
    if ($adocaoConcluida($item)) {
        $solicitacaoCaoNectou = $item;
        break;
    }
}

$montarFoto = static function (?string $caminho) use ($urlBase): string {
    if (!$caminho) return asset('assets/img/perfil-placeholder.png');
    if (preg_match('#^https?://#i', $caminho)) return $caminho;
    return $urlBase . '/' . ltrim($caminho, '/');
};

$dadosCaoNectou = [
    'animal' => $solicitacaoCaoNectou['animal_nome'] ?? 'Seu novo melhor amigo',
    'adotante' => $solicitacaoCaoNectou['adotante_nome'] ?? ($_SESSION['usuario_nome'] ?? 'Adotante'),
    'responsavel' => $solicitacaoCaoNectou['nome_fantasia'] ?? 'ONG CãoNectados',
    'fotoAnimal' => $montarFoto($solicitacaoCaoNectou['animal_foto'] ?? null),
    'fotoAdotante' => $montarFoto($solicitacaoCaoNectou['adotante_foto'] ?? null),
    'fotoResponsavel' => $montarFoto($solicitacaoCaoNectou['protetor_foto'] ?? null),
    'whatsapp' => preg_replace('/\D+/', '', (string)($solicitacaoCaoNectou['protetor_telefone'] ?? '')),
];
if (strlen($dadosCaoNectou['whatsapp']) >= 10 && !str_starts_with($dadosCaoNectou['whatsapp'], '55')) {
    $dadosCaoNectou['whatsapp'] = '55' . $dadosCaoNectou['whatsapp'];
}
?>

<div class="max-w-md lg:max-w-2xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6 pt-6">
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white">Minhas Solicitações</h1>
            <p class="text-sm text-text-muted mt-1">Acompanhe seus petiscos enviados</p>
        </div>
    </div>

    <?php if (empty($solicitacoes)): ?>
        <div class="text-center py-16">
            <div class="flex justify-center mb-3"><?= renderIconeMenu("patinha.svg", "", "h-12 w-12 text-primary dark:text-white") ?></div>
            <p class="text-text-muted text-sm">Você ainda não demonstrou interesse em nenhum animal.</p>
            <a href="<?= e($urlBase) ?>/feed" class="inline-block mt-4 btn-primario text-sm">Ver animais no Feed</a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($solicitacoes as $solic): ?>
                <?php
                $status = $statusLabels[$solic['status_solicitacao']] ?? ['texto' => $solic['status_solicitacao'], 'classe' => $statusLabels['cancelada']['classe']];
                $foto = !empty($solic['animal_foto']) ? $montarFoto($solic['animal_foto']) : null;
                $podeCancel = in_array($solic['status_solicitacao'], ['pendente', 'em_analise'], true);
                $podeExibirCaoNectou = $adocaoConcluida($solic);
                ?>
                <div class="flex items-center gap-3 bg-white dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-2xl p-3 shadow-sm">
                    <?php if ($foto): ?>
                        <img src="<?= e($foto) ?>" alt="Foto de <?= e($solic['animal_nome']) ?>" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    <?php else: ?>
                        <div class="w-14 h-14 rounded-xl bg-rosa-1 dark:bg-preto2 flex items-center justify-center shrink-0">
                            <img src="<?= e(asset('assets/img/icone-adotado-patinha-coracao.png')) ?>" alt="" class="w-8 h-8 object-contain">
                        </div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-text-dark dark:text-white truncate"><?= e($solic['animal_nome']) ?></p>
                        <p class="text-xs text-text-muted truncate"><?= e($solic['nome_fantasia'] ?? 'Protetor independente') ?></p>
                        <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full <?= e($status['classe']) ?>"><?= e($status['texto']) ?></span>
                        <?php if ($solic['status_solicitacao'] === 'reprovada' && !empty($solic['justificativa_recusa'])): ?>
                            <p class="text-[11px] text-text-muted mt-1">Motivo: <?= e($solic['justificativa_recusa']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <?php if ($podeExibirCaoNectou): ?>
                            <button type="button" class="btn-ver-caonectou rounded-full bg-rosa-1 px-3 py-1.5 text-xs font-bold text-text-dark transition hover:scale-105 dark:bg-primary dark:text-white">CãoNectou!</button>
                        <?php endif; ?>
                        <?php if ($podeCancel): ?>
                            <form id="form-cancelar-<?= (int) $solic['solicitacao_id'] ?>" method="POST" action="<?= e($urlBase) ?>/minhas-solicitacoes/cancelar">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int) $solic['solicitacao_id'] ?>">
                                <button type="button"
                                    onclick="const form = this.closest('form'); abrirModalConfirmacao('Cancelar solicitação', 'Tem certeza que deseja cancelar esta solicitação?', () => form.requestSubmit(), 'Cancelar solicitação', 'Voltar')"
                                    class="text-xs font-bold text-erro underline hover:opacity-80">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($solicitacaoCaoNectou): ?>
<section id="modal-caonectou" class="hidden fixed inset-0 z-[100] items-center justify-center overflow-y-auto bg-[#100b24]/90 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="titulo-caonectou" aria-hidden="true">
    <div id="etapa-petisco-caonectou" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <img id="gif-caonectou" src="<?= e(asset('assets/img/caonectou-animacao.gif')) ?>" data-src="<?= e(asset('assets/img/caonectou-animacao.gif')) ?>" alt="" class="gif-caonectou absolute inset-0 h-full w-full object-cover">
        <p class="absolute inset-x-4 top-[8vh] z-10 text-center font-shantell text-xl font-bold text-white drop-shadow-[0_2px_8px_rgba(0,0,0,.8)] sm:text-2xl">Uma nova amizade está começando...</p>
    </div>

    <article id="cartao-caonectou" class="relative z-10 my-auto hidden w-full max-w-lg scale-95 rounded-[2rem] border border-rosa-3 bg-white p-5 text-center text-[#30243c] opacity-0 shadow-[0_25px_90px_rgba(0,0,0,.55)] transition duration-500 dark:bg-[#21173b] dark:text-white sm:p-8">
        <button type="button" id="fechar-caonectou" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-2xl text-primary transition hover:bg-primary hover:text-white dark:bg-white/10 dark:text-white" aria-label="Fechar">&times;</button>
        <h2 id="titulo-caonectou" class="font-shantell text-3xl font-bold text-primary dark:text-white sm:text-4xl">CãoNectou!</h2>
        <p class="mt-2 text-sm text-[#6f6675] dark:text-white/75 sm:text-base">Uma nova história começa com carinho.</p>

        <div class="relative mx-auto mt-6 h-52 w-full max-w-[360px] sm:mt-7 sm:h-56">
            <div class="foto-encontro absolute left-6 top-0 z-10 flex w-36 flex-col items-center">
                <img id="foto-animal-caonectou" src="<?= e($dadosCaoNectou['fotoAnimal']) ?>" alt="Animal adotado" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg dark:border-white/90">
                <span id="nome-animal-caonectou" class="mt-1 max-w-full truncate text-xs font-bold sm:text-sm"><?= e($dadosCaoNectou['animal']) ?></span>
            </div>
            <div class="foto-encontro foto-encontro-adotante absolute right-6 top-0 z-10 flex w-36 flex-col items-center">
                <img id="foto-adotante-caonectou" src="<?= e($dadosCaoNectou['fotoAdotante']) ?>" alt="Adotante" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg dark:border-white/90">
                <span id="nome-adotante-caonectou" class="mt-1 max-w-full truncate text-xs font-bold sm:text-sm"><?= e($dadosCaoNectou['adotante']) ?></span>
            </div>
            <img src="<?= e(asset('assets/img/icone-adotado-patinha-coracao.png')) ?>" alt="Coração com uma patinha" class="absolute left-1/2 top-6 z-20 h-20 w-20 -translate-x-1/2 object-contain drop-shadow-[0_5px_9px_rgba(0,0,0,.3)] sm:h-24 sm:w-24">
            <div class="absolute bottom-0 left-1/2 z-20 flex -translate-x-1/2 flex-col items-center">
                <img id="foto-responsavel-caonectou" src="<?= e($dadosCaoNectou['fotoResponsavel']) ?>" alt="ONG ou protetor" class="h-12 w-12 rounded-full border-2 border-white bg-white object-cover shadow-md dark:border-white/80">
                <span id="nome-responsavel-caonectou" class="mt-1 max-w-40 truncate text-[11px] font-semibold text-[#51465a] dark:text-white/85 sm:text-xs"><?= e($dadosCaoNectou['responsavel']) ?></span>
            </div>
        </div>

        <p id="mensagem-caonectou" class="mt-3 text-sm leading-relaxed text-[#51465a] dark:text-white/85 sm:text-base"></p>
        <a id="whatsapp-caonectou" href="#" target="_blank" rel="noopener noreferrer" class="mt-6 hidden min-h-12 w-full items-center justify-center rounded-full bg-[#4a6645] px-5 py-3 font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#3d5939]">Conversar com a ONG pelo WhatsApp</a>
        <p id="whatsapp-indisponivel-caonectou" class="mt-5 hidden text-xs text-[#786f7e] dark:text-white/70">O responsável ainda não cadastrou um telefone para contato pelo WhatsApp.</p>
        <button type="button" id="continuar-caonectou" class="mt-4 w-full rounded-full border border-primary/35 px-5 py-3 font-bold text-primary transition hover:bg-primary/5 dark:border-white/50 dark:text-white dark:hover:bg-white/10">Continuar</button>
    </article>
</section>

<style>
    #modal-caonectou.caonectou-aberto { display: flex; }
    #modal-caonectou.caonectou-final #etapa-petisco-caonectou { display: none; }
    #modal-caonectou.caonectou-final #cartao-caonectou { display: block; }
    #modal-caonectou.caonectou-final #cartao-caonectou.cartao-visivel { opacity: 1; transform: scale(1); }
    .gif-caonectou { animation: gif-sobe-do-fundo .8s cubic-bezier(.2,.75,.25,1) both; }
    .foto-encontro { animation: fotos-encontro .65s cubic-bezier(.2,.8,.2,1) both; }
    .foto-encontro-adotante { animation-delay:.12s; }
    @keyframes gif-sobe-do-fundo { from { transform:translateY(100%); } to { transform:translateY(0); } }
    @keyframes fotos-encontro { from { opacity:0; transform:translateY(-55vh) scale(.65); } to { opacity:1; transform:translateY(0) scale(1); } }
    @media (min-width: 1024px) {
        #gif-caonectou { inset:15vh auto auto 15vw; width:70vw; height:70vh; object-fit:contain; }
    }
    @media (prefers-reduced-motion: reduce) {
        .gif-caonectou,.foto-encontro { animation-duration:.01ms !important; animation-iteration-count:1 !important; }
    }
</style>

<script>
(() => {
    const modal = document.getElementById('modal-caonectou');
    const cartao = document.getElementById('cartao-caonectou');
    if (!modal || !cartao) return;

    const dados = <?= json_encode($dadosCaoNectou, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const fotoPadrao = <?= json_encode(asset('assets/img/perfil-placeholder.png'), JSON_UNESCAPED_SLASHES) ?>;
    const gifCaonectou = document.getElementById('gif-caonectou');
    let temporizador;
    const abrir = () => {
        window.clearTimeout(temporizador);
        document.getElementById('nome-animal-caonectou').textContent = dados.animal || 'Seu novo melhor amigo';
        document.getElementById('nome-adotante-caonectou').textContent = dados.adotante || 'Adotante';
        document.getElementById('nome-responsavel-caonectou').textContent = dados.responsavel || 'ONG CãoNectados';
        document.getElementById('mensagem-caonectou').textContent = `Você e ${dados.animal || 'seu novo melhor amigo'} têm tudo para serem melhores amigos. Converse com ${dados.responsavel || 'a ONG ou o protetor'} para combinar os próximos passos da adoção.`;
        [['foto-animal-caonectou', dados.fotoAnimal], ['foto-adotante-caonectou', dados.fotoAdotante], ['foto-responsavel-caonectou', dados.fotoResponsavel]].forEach(([id, caminho]) => {
            const imagem = document.getElementById(id);
            imagem.onerror = () => { imagem.onerror = null; imagem.src = fotoPadrao; };
            imagem.src = caminho || fotoPadrao;
        });

        const whatsapp = document.getElementById('whatsapp-caonectou');
        const indisponivel = document.getElementById('whatsapp-indisponivel-caonectou');
        if (dados.whatsapp) {
            const mensagem = `Olá! Vi a aprovação da adoção de ${dados.animal || 'um animal'} no CãoNectados e gostaria de combinar os próximos passos.`;
            whatsapp.href = `https://wa.me/${dados.whatsapp}?text=${encodeURIComponent(mensagem)}`;
        }
        whatsapp.classList.toggle('hidden', !dados.whatsapp);
        whatsapp.classList.toggle('inline-flex', Boolean(dados.whatsapp));
        indisponivel.classList.toggle('hidden', Boolean(dados.whatsapp));
        gifCaonectou.src = `${gifCaonectou.dataset.src}?replay=${Date.now()}`;
        modal.classList.remove('hidden', 'caonectou-final');
        modal.classList.add('caonectou-aberto');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        cartao.classList.remove('cartao-visivel');
        temporizador = window.setTimeout(() => {
            modal.classList.add('caonectou-final');
            requestAnimationFrame(() => cartao.classList.add('cartao-visivel'));
            document.getElementById('fechar-caonectou').focus();
        }, 1750);
    };
    const fechar = () => {
        window.clearTimeout(temporizador);
        modal.classList.add('hidden');
        modal.classList.remove('caonectou-aberto', 'caonectou-final');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('.btn-ver-caonectou').forEach((botao) => botao.addEventListener('click', abrir));
    document.getElementById('fechar-caonectou')?.addEventListener('click', fechar);
    document.getElementById('continuar-caonectou')?.addEventListener('click', fechar);
    modal.addEventListener('click', (evento) => { if (evento.target === modal) fechar(); });
    document.addEventListener('keydown', (evento) => { if (evento.key === 'Escape' && modal.classList.contains('caonectou-aberto')) fechar(); });

    const sessaoKey = 'caonectou-visto-<?= (int)$solicitacaoCaoNectou['solicitacao_id'] ?>';
    if (!sessionStorage.getItem(sessaoKey)) {
        sessionStorage.setItem(sessaoKey, '1');
        window.setTimeout(abrir, 500);
    }
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
