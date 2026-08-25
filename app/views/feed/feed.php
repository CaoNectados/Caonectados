<?php
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
$animais = $animais ?? [];
$filtrosAtuais = $filtrosAtuais ?? [];
$especies = $especies ?? [];
$regioes = $regioes ?? [];
$protetores = $protetores ?? [];

$porteLabels = ['pequeno' => 'Pequeno Porte', 'medio' => 'Médio Porte', 'grande' => 'Grande Porte'];
$sexoLabels  = ['macho' => 'Macho', 'femea' => 'Fêmea', 'indefinido' => 'Indefinido'];
$comportamentoLabels = ['calmo' => 'Calmo', 'ativo' => 'Ativo', 'docil' => 'Dócil', 'arisco' => 'Arisco'];

// Usada aqui e replicada em JS (formatarIdade) pros cards carregados via AJAX no scroll infinito
function feedFormatarIdade(?string $dtNasc): string
{
    if (empty($dtNasc)) {
        return 'Idade não informada';
    }
    $nascimento = new DateTime($dtNasc);
    $hoje = new DateTime('today');
    $meses = ($hoje->format('Y') - $nascimento->format('Y')) * 12 + ($hoje->format('n') - $nascimento->format('n'));

    if ($meses < 12) {
        return $meses <= 1 ? '1 mês' : "{$meses} meses";
    }
    $anos = intdiv($meses, 12);
    return $anos === 1 ? '1 ano' : "{$anos} anos";
}

function feedMontarUrlFoto(?string $caminho, string $urlBase): ?string
{
    if (empty($caminho)) {
        return null;
    }
    return $urlBase . '/' . ltrim($caminho, '/');
}
?>

<style>
    /* Trilha do feed: por padrão (mobile) rola na VERTICAL; a partir de lg vira HORIZONTAL.
       É a mesma ideia dos protótipos — mobile pra baixo, desktop pros lados. */
    #feed-track {
        scroll-snap-type: y mandatory;
        overscroll-behavior: contain;
    }
    #feed-track .feed-card {
        scroll-snap-align: start;
    }
    @media (min-width: 1024px) {
        #feed-track {
            scroll-snap-type: x mandatory;
            flex-direction: row !important;
        }
        #feed-track .feed-card {
            scroll-snap-align: center;
        }
    }
    #feed-track::-webkit-scrollbar { display: none; }
    #feed-track { scrollbar-width: none; -ms-overflow-style: none; }

    /* O efeito de "empilhado/esmaecido" nos vizinhos só faz sentido no desktop (cards lado a
       lado, na horizontal). No mobile é um card por vez ocupando a tela — aplicar esse mesmo
       scale/opacity ali brigava com o snap-scroll nativo durante o gesto de rolar e dava a
       sensação de carrossel "estranho"/tremendo que foi reportada. */
    @media (min-width: 1024px) {
        .feed-card {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .feed-card:not(.is-active) {
            opacity: 0.45;
            transform: scale(0.92);
        }
    }
</style>

<div class="max-w-md lg:max-w-none mx-auto pb-24 lg:pb-10 relative">

    <!-- Cabeçalho da tela + botão de filtros -->
    <div class="flex items-center justify-between px-4 pt-4 lg:px-8">
        <div>
            <h1 class="font-shantell text-xl lg:text-2xl font-bold text-text-dark dark:text-white">Feed de Adoção</h1>
            <p class="text-xs text-text-muted hidden sm:block">Deslize para conhecer os animais disponíveis</p>
        </div>
        <button type="button" onclick="document.getElementById('modal-filtros-feed').classList.remove('hidden')"
                class="flex items-center gap-2 bg-surface dark:bg-preto1 border border-rosa-2 dark:border-preto3 rounded-full px-4 py-2 text-xs font-bold text-text-dark dark:text-white shadow-sm hover:shadow-md transition shrink-0">
            🔎 Filtros
        </button>
    </div>

    <?php if (empty($animais)): ?>
        <div class="mx-4 mt-6 bg-surface dark:bg-preto1 rounded-3xl border border-rosa-2 dark:border-preto3 p-10 text-center shadow-sm">
            <span class="text-5xl block mb-4">🐾</span>
            <p class="font-poppins text-text-dark dark:text-white font-bold mb-1">Nenhum animal encontrado.</p>
            <p class="font-poppins text-text-muted text-sm">Tente ajustar os filtros ou volte mais tarde — novos animais aparecem por aqui o tempo todo.</p>
        </div>
    <?php else: ?>

        <!-- ÁREA DO FEED: setas grandes (desktop) + trilha de cards -->
        <div class="relative flex items-center justify-center mt-4 lg:mt-8 px-2 lg:px-4">

            <button type="button" id="btn-anterior-animal" aria-label="Animal anterior"
                    class="hidden lg:flex items-center justify-center w-12 h-12 rounded-full bg-roxo2 dark:bg-primary text-white shadow-lg hover:opacity-90 transition shrink-0 mr-4 disabled:opacity-30 disabled:cursor-not-allowed">
                &lsaquo;
            </button>

            <div id="feed-track" class="flex flex-col lg:flex-row gap-6 overflow-y-auto lg:overflow-y-hidden lg:overflow-x-auto w-full lg:w-auto h-[70vh] items-stretch lg:items-center">
                <?php foreach ($animais as $indice => $animal): ?>
                    <?php
                        $fotos = array_values(array_filter(array_map(
                            fn(array $f) => feedMontarUrlFoto($f['caminho_foto'], $urlBase),
                            $animal['fotos'] ?? []
                        )));
                        if (empty($fotos)) {
                            $fotos = [$urlBase . '/assets/img/perfil-placeholder.png'];
                        }
                        $porte = $porteLabels[$animal['porte']] ?? ucfirst((string) $animal['porte']);
                        $comportamento = $comportamentoLabels[$animal['comportamento']] ?? null;
                    ?>
                    <div class="feed-card <?= $indice === 0 ? 'is-active' : '' ?> shrink-0 w-full lg:w-[400px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 relative flex flex-col"
                         data-animal-id="<?= (int) $animal['animal_id'] ?>">

                        <!-- Barra segmentada + carrossel de FOTOS deste animal -->
                        <div class="relative flex-1 bg-black/5">
                            <div class="absolute top-3 left-3 right-3 z-20 flex gap-1.5">
                                <?php foreach ($fotos as $idxFoto => $foto): ?>
                                    <div class="h-1 flex-1 rounded-full bg-white/40 overflow-hidden">
                                        <div class="h-full bg-white foto-segmento <?= $idxFoto === 0 ? 'w-full' : 'w-0' ?>"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <a href="<?= $urlBase ?>/pagina?id=<?= (int) $animal['protetor_id'] ?>" onclick="event.stopPropagation()"
                               class="absolute top-10 left-3 z-20 flex items-center gap-2 bg-white/90 dark:bg-preto1/90 rounded-full pl-1 pr-3 py-1 shadow hover:bg-white transition">
                                <span class="w-7 h-7 rounded-full bg-rosa-1 flex items-center justify-center text-xs">🏠</span>
                                <span class="text-xs font-bold text-text-dark"><?= htmlspecialchars($animal['nome_fantasia'] ?? 'Protetor independente') ?></span>
                            </a>

                            <div class="foto-carrossel absolute inset-0">
                                <?php foreach ($fotos as $idxFoto => $foto): ?>
                                    <img src="<?= htmlspecialchars($foto) ?>"
                                         alt="Foto de <?= htmlspecialchars($animal['nome']) ?>"
                                         class="foto-item absolute inset-0 w-full h-full object-cover <?= $idxFoto === 0 ? '' : 'hidden' ?>"
                                         onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($fotos) > 1): ?>
                                <button type="button" class="btn-foto-anterior absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/70 hover:bg-white flex items-center justify-center text-text-dark shadow" aria-label="Foto anterior">&lsaquo;</button>
                                <button type="button" class="btn-foto-proxima absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/70 hover:bg-white flex items-center justify-center text-text-dark shadow" aria-label="Próxima foto">&rsaquo;</button>
                            <?php endif; ?>

                            <span class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-white/80 flex items-center justify-center text-erro" title="Denunciar (em breve)">🚩</span>

                            <button type="button" onclick="if(typeof mostrarModalFeedback === 'function') { mostrarModalFeedback('informativo', 'Em breve você poderá dar petiscos direto por aqui! Essa etapa ainda está sendo implementada.'); } else { alert('Em breve! Essa função ainda está sendo implementada.'); }"
                                    class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 bg-rosa-1 hover:bg-rosa-2 text-text-dark font-shantell font-bold text-lg px-8 py-3 rounded-full shadow-lg transition active:scale-95">
                                Dar Petisco!
                            </button>
                        </div>

                        <!-- Info do animal -->
                        <div class="p-4 bg-branco dark:bg-preto1">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <h2 class="font-shantell text-xl font-bold text-text-dark dark:text-white"><?= htmlspecialchars($animal['nome']) ?></h2>
                                <span class="text-primary dark:text-roxinhoFofo font-bold text-sm"><?= htmlspecialchars(feedFormatarIdade($animal['dt_nasc'])) ?></span>
                                <?php if (!empty($animal['nome_regiao'])): ?>
                                    <span class="ml-auto flex items-center gap-1 bg-sucesso/15 text-sucesso text-xs font-bold px-2.5 py-1 rounded-full">📍 <?= htmlspecialchars($animal['nome_regiao']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($porte) ?></span>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($animal['raca_nome'] ?? $animal['especie_nome']) ?></span>
                                <?php if ($comportamento): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($comportamento) ?></span>
                                <?php endif; ?>
                                <?php if ($animal['castrado']): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sucesso/15 text-sucesso">Castrado</span>
                                <?php endif; ?>
                                <?php if ($animal['vacinado']): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sucesso/15 text-sucesso">Vacinado</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= $urlBase ?>/animal/mostrar?id=<?= (int) $animal['animal_id'] ?>" class="inline-block mt-3 text-xs font-bold text-primary dark:text-roxinhoFofo underline">Ver perfil completo</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($temMais)): ?>
                    <div id="card-fim-feed" class="feed-card is-active shrink-0 w-full lg:w-[400px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 flex flex-col items-center justify-center text-center p-8 gap-4">
                        <span class="text-5xl">🐾</span>
                        <p class="font-poppins text-text-dark dark:text-white font-bold">Você já viu todos os animais disponíveis por aqui!</p>
                        <p class="font-poppins text-text-muted text-sm">Volte outra hora para conferir novidades.</p>
                        <button type="button" onclick="window.location.href='<?= $urlBase ?>/feed'" class="btn-primario">🔄 Atualizar Feed</button>
                    </div>
                <?php else: ?>
                    <!-- Sentinela do scroll infinito: quando entra na tela, carrega a próxima página -->
                    <div id="sentinela-fim-feed" class="shrink-0 w-2 h-2 lg:w-2 lg:h-full"></div>
                <?php endif; ?>
            </div>

            <button type="button" id="btn-proximo-animal" aria-label="Próximo animal"
                    class="hidden lg:flex items-center justify-center w-12 h-12 rounded-full bg-roxo2 dark:bg-primary text-white shadow-lg hover:opacity-90 transition shrink-0 ml-4 disabled:opacity-30 disabled:cursor-not-allowed">
                &rsaquo;
            </button>
        </div>

        <div id="loading-mais-animais" class="hidden text-center py-6 text-sm text-text-muted">Carregando mais animais...</div>
    <?php endif; ?>
</div>

<!-- BARRA INFERIOR (só mobile — desktop já usa a sidebar padrão do site) -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-primary flex items-center justify-around h-16 shadow-[0_-4px_12px_rgba(0,0,0,0.2)]">
    <a href="<?= $urlBase ?>/feed" class="flex flex-col items-center justify-center text-white">
        <img src="<?= $urlBase ?>/assets/icons/navbar/home.svg" alt="" class="w-6 h-6 brightness-0 invert">
    </a>
    <button type="button" onclick="document.getElementById('modal-filtros-feed').classList.remove('hidden')" class="flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/pesquisar.svg" alt="Buscar" class="w-6 h-6 brightness-0 invert opacity-70">
    </button>
    <a href="#" onclick="event.preventDefault(); if(typeof mostrarModalFeedback === 'function'){mostrarModalFeedback('informativo','Chat ainda não foi implementado.');}" class="flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/chat.svg" alt="Chat" class="w-6 h-6 brightness-0 invert opacity-70">
    </a>
    <a href="<?= $urlBase ?>/perfil" class="flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/perfil.svg" alt="Perfil" class="w-6 h-6 brightness-0 invert opacity-70">
    </a>
</nav>

<!-- MODAL DE FILTROS -->
<div id="modal-filtros-feed" class="fixed inset-0 bg-black/70 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-branco dark:bg-preto1 rounded-3xl max-w-md w-full p-6 max-h-[85vh] overflow-y-auto border border-rosa-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-shantell text-xl font-bold text-text-dark dark:text-white">Filtros</h3>
            <button type="button" onclick="document.getElementById('modal-filtros-feed').classList.add('hidden')" class="text-text-muted hover:text-erro text-3xl font-bold">&times;</button>
        </div>

        <form method="GET" action="<?= $urlBase ?>/feed" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label-padrao">Porte</label>
                    <select name="porte" class="input-padrao">
                        <option value="">Todos</option>
                        <option value="pequeno" <?= ($filtrosAtuais['porte'] ?? '') === 'pequeno' ? 'selected' : '' ?>>Pequeno</option>
                        <option value="medio" <?= ($filtrosAtuais['porte'] ?? '') === 'medio' ? 'selected' : '' ?>>Médio</option>
                        <option value="grande" <?= ($filtrosAtuais['porte'] ?? '') === 'grande' ? 'selected' : '' ?>>Grande</option>
                    </select>
                </div>
                <div>
                    <label class="label-padrao">Sexo</label>
                    <select name="sexo" class="input-padrao">
                        <option value="">Todos</option>
                        <option value="macho" <?= ($filtrosAtuais['sexo'] ?? '') === 'macho' ? 'selected' : '' ?>>Macho</option>
                        <option value="femea" <?= ($filtrosAtuais['sexo'] ?? '') === 'femea' ? 'selected' : '' ?>>Fêmea</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label-padrao">Espécie</label>
                    <select name="especie_id" id="filtro-especie" class="input-padrao">
                        <option value="">Todas</option>
                        <?php foreach ($especies as $especie): ?>
                            <option value="<?= $especie['especie_id'] ?>" <?= (string) ($filtrosAtuais['especie_id'] ?? '') === (string) $especie['especie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($especie['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label-padrao">Raça</label>
                    <select name="raca_id" id="filtro-raca" data-old-value="<?= htmlspecialchars((string) ($filtrosAtuais['raca_id'] ?? '')) ?>" class="input-padrao">
                        <option value="">Todas</option>
                    </select>
                </div>
            </div>

            <div class="relative">
                <label class="label-padrao" for="feed-input-busca-bairro">Bairro / Região</label>
                <?php
                    $regiaoNomeAtual = '';
                    foreach ($regioes as $regiao) {
                        if ((string) $regiao->getRegiaoId() === (string) ($filtrosAtuais['regiao_id'] ?? '')) {
                            $regiaoNomeAtual = $regiao->getNomeRegiao();
                            break;
                        }
                    }
                ?>
                <!-- Muitos bairros cadastrados — digitável (com autocomplete nativo via
                     datalist) em vez de <select>, pro adotante achar o dele mais rápido. -->
                <input type="text" id="feed-input-busca-bairro" list="feed-lista-regioes"
                       value="<?= htmlspecialchars($regiaoNomeAtual) ?>"
                       placeholder="Digite o nome do bairro..." autocomplete="off"
                       class="input-padrao input-com-seta">
                <datalist id="feed-lista-regioes">
                    <?php foreach ($regioes as $regiao): ?>
                        <option data-id="<?= $regiao->getRegiaoId() ?>" value="<?= htmlspecialchars($regiao->getNomeRegiao()) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <input type="hidden" name="regiao_id" id="feed-regiao-id-hidden" value="<?= htmlspecialchars((string) ($filtrosAtuais['regiao_id'] ?? '')) ?>">
            </div>

            <div>
                <label class="label-padrao">ONG / Protetor</label>
                <select name="protetor_id" class="input-padrao">
                    <option value="">Todos</option>
                    <?php foreach ($protetores as $p): ?>
                        <option value="<?= $p['protetor_id'] ?>" <?= (string) ($filtrosAtuais['protetor_id'] ?? '') === (string) $p['protetor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome_fantasia']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="castrado" value="1" <?= ($filtrosAtuais['castrado'] ?? '') === '1' ? 'checked' : '' ?> class="w-5 h-5 accent-primary">
                    <span class="text-sm font-bold text-text-dark dark:text-white">Castrado</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="vacinado" value="1" <?= ($filtrosAtuais['vacinado'] ?? '') === '1' ? 'checked' : '' ?> class="w-5 h-5 accent-primary">
                    <span class="text-sm font-bold text-text-dark dark:text-white">Vacinado</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="<?= $urlBase ?>/feed" class="flex-1 text-center bg-cinzaMarrom/20 text-text-dark dark:text-white py-3 rounded-full font-bold text-sm">Limpar</a>
                <button type="submit" class="flex-1 btn-primario justify-center">Aplicar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    const urlBase = '<?= $urlBase ?>';
    const filtrosAtuais = <?= json_encode($filtrosAtuais) ?>;
    let proximoOffset = <?= (int) ($proximoOffset ?? 0) ?>;
    let temMais = <?= !empty($temMais) ? 'true' : 'false' ?>;
    let carregando = false;

    // ---------- Carrossel de fotos por card ----------
    function iniciarCarrossel(card) {
        const fotos = card.querySelectorAll('.foto-item');
        const segmentos = card.querySelectorAll('.foto-segmento');
        if (fotos.length <= 1) return;

        let indice = 0;

        function mostrar(novoIndice) {
            fotos[indice].classList.add('hidden');
            segmentos[indice].classList.remove('w-full');
            segmentos[indice].classList.add('w-0');

            indice = (novoIndice + fotos.length) % fotos.length;

            fotos[indice].classList.remove('hidden');
            segmentos[indice].classList.remove('w-0');
            segmentos[indice].classList.add('w-full');
        }

        card.querySelector('.btn-foto-anterior')?.addEventListener('click', function (e) {
            e.stopPropagation();
            mostrar(indice - 1);
        });
        card.querySelector('.btn-foto-proxima')?.addEventListener('click', function (e) {
            e.stopPropagation();
            mostrar(indice + 1);
        });
    }

    document.querySelectorAll('.feed-card').forEach(iniciarCarrossel);

    // ---------- Card "ativo" (o que está em foco na trilha) via IntersectionObserver ----------
    const track = document.getElementById('feed-track');
    let cardAtivoAtual = null;

    if (track) {
        const observadorAtivo = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting && entrada.intersectionRatio > 0.6) {
                    document.querySelectorAll('.feed-card.is-active').forEach(c => c.classList.remove('is-active'));
                    entrada.target.classList.add('is-active');
                    cardAtivoAtual = entrada.target;
                }
            });
        }, { root: track, threshold: [0.6] });

        document.querySelectorAll('.feed-card').forEach(card => observadorAtivo.observe(card));

        // ---------- Navegação entre animais (setas grandes, só desktop) ----------
        function irParaVizinho(direcao) {
            const cards = Array.from(track.querySelectorAll('.feed-card'));
            const atualIndex = cardAtivoAtual ? cards.indexOf(cardAtivoAtual) : 0;
            const alvo = cards[atualIndex + direcao];
            if (alvo) {
                alvo.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }
        document.getElementById('btn-anterior-animal')?.addEventListener('click', () => irParaVizinho(-1));
        document.getElementById('btn-proximo-animal')?.addEventListener('click', () => irParaVizinho(1));

        // ---------- Scroll infinito ----------
        const sentinela = document.getElementById('sentinela-fim-feed');
        if (sentinela) {
            const observadorFim = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (entrada) {
                    if (entrada.isIntersecting) {
                        carregarMaisAnimais();
                    }
                });
            }, { root: track, threshold: 0.1 });

            observadorFim.observe(sentinela);

            // Re-observa a cada novo lote (o querySelector do card muda o DOM)
            window.registrarNovoCardParaObservador = function (card) {
                observadorAtivo.observe(card);
            };
        }
    }

    async function carregarMaisAnimais() {
        if (carregando || !temMais) return;
        carregando = true;
        document.getElementById('loading-mais-animais')?.classList.remove('hidden');

        try {
            const params = new URLSearchParams({ ...filtrosAtuais, offset: proximoOffset });
            // Remove chaves vazias pra não poluir a query string
            Array.from(params.keys()).forEach(k => { if (!params.get(k)) params.delete(k); });

            const response = await fetch(`${urlBase}/feed/carregar-mais?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const resultado = await response.json();

            if (resultado.status === 'sucesso') {
                resultado.animais.forEach(criarElementoCard);
                proximoOffset = resultado.proximoOffset;
                temMais = resultado.temMais;

                if (!temMais) {
                    criarCardFimDeFeed();
                }
            }
        } catch (erro) {
            console.error('Erro ao carregar mais animais do feed:', erro);
        } finally {
            carregando = false;
            document.getElementById('loading-mais-animais')?.classList.add('hidden');
        }
    }

    // Substitui a sentinela por um card de "fim de feed" dentro da própria trilha (no lugar
    // de onde entraria o próximo animal), com botão pra recarregar e ver se surgiu algo novo.
    function criarCardFimDeFeed() {
        const sentinela = document.getElementById('sentinela-fim-feed');
        if (!sentinela || document.getElementById('card-fim-feed')) return;

        const card = document.createElement('div');
        card.id = 'card-fim-feed';
        card.className = 'feed-card shrink-0 w-full lg:w-[400px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 flex flex-col items-center justify-center text-center p-8 gap-4';
        card.innerHTML = `
            <span class="text-5xl">🐾</span>
            <p class="font-poppins text-text-dark dark:text-white font-bold">Você já viu todos os animais disponíveis por aqui!</p>
            <p class="font-poppins text-text-muted text-sm">Volte outra hora para conferir novidades.</p>
            <button type="button" class="btn-primario">🔄 Atualizar Feed</button>
        `;
        card.querySelector('button').addEventListener('click', () => { window.location.href = `${urlBase}/feed`; });

        sentinela.replaceWith(card);
        if (typeof window.registrarNovoCardParaObservador === 'function') {
            window.registrarNovoCardParaObservador(card);
        }
    }

    function formatarIdade(dtNasc) {
        if (!dtNasc) return 'Idade não informada';
        const nascimento = new Date(dtNasc + 'T00:00:00');
        const hoje = new Date();
        let meses = (hoje.getFullYear() - nascimento.getFullYear()) * 12 + (hoje.getMonth() - nascimento.getMonth());
        if (meses < 12) return meses <= 1 ? '1 mês' : `${meses} meses`;
        const anos = Math.floor(meses / 12);
        return anos === 1 ? '1 ano' : `${anos} anos`;
    }

    const PORTE_LABELS = { pequeno: 'Pequeno Porte', medio: 'Médio Porte', grande: 'Grande Porte' };

    function criarElementoCard(animal) {
        const fotos = animal.fotos && animal.fotos.length ? animal.fotos : [`${urlBase}/assets/img/perfil-placeholder.png`];
        const sentinela = document.getElementById('sentinela-fim-feed');

        const card = document.createElement('div');
        card.className = 'feed-card shrink-0 w-full lg:w-[400px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 relative flex flex-col';
        card.dataset.animalId = animal.animal_id;

        const segmentosHtml = fotos.map((_, i) => `<div class="h-1 flex-1 rounded-full bg-white/40 overflow-hidden"><div class="h-full bg-white foto-segmento ${i === 0 ? 'w-full' : 'w-0'}"></div></div>`).join('');
        const fotosHtml = fotos.map((src, i) => `<img src="${src}" alt="Foto de ${animal.nome}" class="foto-item absolute inset-0 w-full h-full object-cover ${i === 0 ? '' : 'hidden'}" onerror="this.onerror=null;this.src='${urlBase}/assets/img/perfil-placeholder.png';">`).join('');
        const setasHtml = fotos.length > 1
            ? `<button type="button" class="btn-foto-anterior absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/70 hover:bg-white flex items-center justify-center text-text-dark shadow">&lsaquo;</button>
               <button type="button" class="btn-foto-proxima absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/70 hover:bg-white flex items-center justify-center text-text-dark shadow">&rsaquo;</button>`
            : '';

        const porte = PORTE_LABELS[animal.porte] || animal.porte;
        const badges = [
            `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white">${porte}</span>`,
            `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white">${animal.raca_nome || animal.especie_nome}</span>`,
        ];
        if (animal.castrado) badges.push('<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sucesso/15 text-sucesso">Castrado</span>');
        if (animal.vacinado) badges.push('<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-sucesso/15 text-sucesso">Vacinado</span>');

        card.innerHTML = `
            <div class="relative flex-1 bg-black/5">
                <div class="absolute top-3 left-3 right-3 z-20 flex gap-1.5">${segmentosHtml}</div>
                <a href="${urlBase}/pagina?id=${animal.protetor_id}" onclick="event.stopPropagation()"
                   class="absolute top-10 left-3 z-20 flex items-center gap-2 bg-white/90 dark:bg-preto1/90 rounded-full pl-1 pr-3 py-1 shadow hover:bg-white transition">
                    <span class="w-7 h-7 rounded-full bg-rosa-1 flex items-center justify-center text-xs">🏠</span>
                    <span class="text-xs font-bold text-text-dark">${animal.nome_fantasia || 'Protetor independente'}</span>
                </a>
                <div class="foto-carrossel absolute inset-0">${fotosHtml}</div>
                ${setasHtml}
                <span class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-white/80 flex items-center justify-center text-erro" title="Denunciar (em breve)">🚩</span>
                <button type="button" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 bg-rosa-1 hover:bg-rosa-2 text-text-dark font-shantell font-bold text-lg px-8 py-3 rounded-full shadow-lg transition active:scale-95">Dar Petisco!</button>
            </div>
            <div class="p-4 bg-branco dark:bg-preto1">
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    <h2 class="font-shantell text-xl font-bold text-text-dark dark:text-white">${animal.nome}</h2>
                    <span class="text-primary dark:text-roxinhoFofo font-bold text-sm">${formatarIdade(animal.dt_nasc)}</span>
                    ${animal.nome_regiao ? `<span class="ml-auto flex items-center gap-1 bg-sucesso/15 text-sucesso text-xs font-bold px-2.5 py-1 rounded-full">📍 ${animal.nome_regiao}</span>` : ''}
                </div>
                <div class="flex flex-wrap gap-1.5">${badges.join('')}</div>
                <a href="${animal.url_detalhes}" class="inline-block mt-3 text-xs font-bold text-primary dark:text-roxinhoFofo underline">Ver perfil completo</a>
            </div>
        `;

        card.querySelector('button.absolute.bottom-4')?.addEventListener('click', function () {
            if (typeof mostrarModalFeedback === 'function') {
                mostrarModalFeedback('informativo', 'Em breve você poderá dar petiscos direto por aqui! Essa etapa ainda está sendo implementada.');
            } else {
                alert('Em breve! Essa função ainda está sendo implementada.');
            }
        });

        sentinela.parentNode.insertBefore(card, sentinela);
        iniciarCarrossel(card);
        if (typeof window.registrarNovoCardParaObservador === 'function') {
            window.registrarNovoCardParaObservador(card);
        }
    }

    // ---------- Combobox digitável de bairro/região (mesmo padrão do onboarding) ----------
    const inputBairro = document.getElementById('feed-input-busca-bairro');
    const hiddenRegiaoId = document.getElementById('feed-regiao-id-hidden');

    function sincronizarRegiaoIdFeed() {
        if (!inputBairro || !hiddenRegiaoId) return;

        let encontradoId = '';
        document.querySelectorAll('#feed-lista-regioes option').forEach(function (opcao) {
            if (opcao.value.trim().toLowerCase() === inputBairro.value.trim().toLowerCase()) {
                encontradoId = opcao.getAttribute('data-id');
            }
        });
        hiddenRegiaoId.value = encontradoId;
    }

    inputBairro?.addEventListener('input', sincronizarRegiaoIdFeed);

    // ---------- Cascata Espécie -> Raça no modal de filtros ----------
    const selectEspecie = document.getElementById('filtro-especie');
    const selectRaca = document.getElementById('filtro-raca');

    async function carregarRacasFiltro(especieId, racaParaSelecionar) {
        if (!selectRaca) return;
        if (!especieId) {
            selectRaca.innerHTML = '<option value="">Todas</option>';
            return;
        }
        selectRaca.innerHTML = '<option value="">Carregando...</option>';
        try {
            const resposta = await fetch(`${urlBase}/raca/json?especie_id=${especieId}`, { headers: { Accept: 'application/json' } });
            const resultado = await resposta.json();
            const lista = resultado.dados || resultado.data || [];
            selectRaca.innerHTML = '<option value="">Todas</option>' + lista.map(r => `<option value="${r.raca_id || r.id}">${r.nome}</option>`).join('');
            if (racaParaSelecionar) selectRaca.value = String(racaParaSelecionar);
        } catch (erro) {
            selectRaca.innerHTML = '<option value="">Erro ao carregar</option>';
        }
    }

    selectEspecie?.addEventListener('change', function () { carregarRacasFiltro(this.value, null); });
    if (selectEspecie?.value) {
        carregarRacasFiltro(selectEspecie.value, selectRaca?.dataset.oldValue);
    }
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
