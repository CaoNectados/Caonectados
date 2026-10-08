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

// Verifica se há algum filtro ativo
$temFiltroAtivo = false;
foreach ($filtrosAtuais as $chave => $valor) {
    if (!empty($valor)) {
        $temFiltroAtivo = true;
        break;
    }
}

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
    /* Trilha do feed: vertical no mobile, horizontal no desktop */
    #feed-track {
        scroll-snap-type: y mandatory;
        overscroll-behavior: contain;
        scroll-behavior: smooth;
    }

    #feed-track .feed-card {
        scroll-snap-align: start;
    }

    @media (min-width: 1024px) {
        #feed-track {
            scroll-snap-type: x mandatory;
            flex-direction: row !important;
            padding-left: calc(50% - 240px) !important;
            padding-right: calc(50% - 240px) !important;
            scroll-padding-left: calc(50% - 240px);
            scroll-padding-right: calc(50% - 240px);
        }

        #feed-track .feed-card {
            scroll-snap-align: center;
            transition: opacity 0.3s ease, border-color 0.3s ease;
        }

        #feed-track .feed-card:not(.is-active) {
            opacity: 0.45;
        }
    }

    #feed-track::-webkit-scrollbar {
        display: none;
    }

    #feed-track {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
</style>

<div class="w-full max-w-7xl mx-auto pb-24 lg:pb-10 relative">

    <!-- Cabeçalho da tela + botão de filtros -->
    <div class="flex items-center justify-between px-4 pt-4 lg:px-8">
        <div>
            <h1 class="font-shantell text-xl lg:text-2xl font-bold text-text-dark dark:text-white">Feed de Adoção</h1>
            <p class="text-xs text-text-muted hidden sm:block">Deslize para conhecer os animais disponíveis</p>
        </div>

        <!-- Botão de filtro com destaque em verde se houver filtro ativo -->
        <button type="button" onclick="document.getElementById('modal-filtros-feed').classList.remove('hidden')"
            class="flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold transition shrink-0 shadow-sm hover:shadow-md <?= $temFiltroAtivo ? 'bg-sucesso/10 text-sucesso border-2 border-sucesso' : 'bg-surface dark:bg-preto1 border border-rosa-2 dark:border-preto3 text-text-dark dark:text-white' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
            </svg> Filtros <?= $temFiltroAtivo ? '<span class="w-2.5 h-2.5 rounded-full bg-sucesso inline-block ml-0.5 animate-pulse"></span>' : '' ?>
        </button>
    </div>

    <?php if (empty($animais)): ?>
        <div class="mx-4 mt-6 bg-surface dark:bg-preto1 rounded-3xl border border-rosa-2 dark:border-preto3 p-10 text-center shadow-sm">
            <div class="flex items-center justify-center gap-3">
                <img src="<?= $urlBase ?>/assets/img/patinha-baixo.png" alt="" class="w-8 h-8 -rotate-12">
                <img src="<?= $urlBase ?>/assets/img/patinha-cima.png" alt="" class="w-8 h-8 rotate-12 mt-3">
            </div>
            <p class="font-poppins text-text-dark dark:text-white font-bold mb-1">Nenhum animal encontrado.</p>
            <p class="font-poppins text-text-muted text-sm mb-4">Tente ajustar os filtros ou volte mais tarde.</p>
            <?php if ($temFiltroAtivo): ?>
                <a href="<?= $urlBase ?>/feed" class="btn-primario inline-flex">Limpar Filtros</a>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <!-- ÁREA DO FEED: setas grandes (desktop) + trilha de cards -->
        <div class="relative flex items-center justify-center mt-4 lg:mt-6 px-2 lg:px-4">

            <!-- Botão Anterior estilizado -->
            <button type="button" id="btn-anterior-animal" aria-label="Animal anterior"
                class="hidden lg:flex h-10 w-10 items-center justify-center rounded-full border-4 border-primary bg-branco text-primary shadow-lg ring-2 ring-primary/20 transition hover:bg-rosaClaro2 dark:bg-preto2 dark:text-branco shrink-0 mr-4 z-10 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div id="feed-track" class="flex flex-col lg:flex-row gap-6 overflow-y-auto lg:overflow-y-hidden lg:overflow-x-auto w-full h-[72vh] max-h-[700px] items-stretch lg:items-center py-2">
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

                    // Validação de destaques por filtro ativo
                    $fPorteAtivo = !empty($filtrosAtuais['porte']) && $filtrosAtuais['porte'] === $animal['porte'];
                    $fSexoAtivo = !empty($filtrosAtuais['sexo']) && $filtrosAtuais['sexo'] === $animal['sexo'];
                    $fCastradoAtivo = !empty($filtrosAtuais['castrado']);
                    $fVacinadoAtivo = !empty($filtrosAtuais['vacinado']);
                    $fRacaEspecieAtivo = !empty($filtrosAtuais['raca_id']) || !empty($filtrosAtuais['especie_id']);
                    $urlPerfilAnimal = $urlBase . '/animal/mostrar?id=' . (int) $animal['animal_id'];
                    ?>
                    <div class="feed-card <?= $indice === 0 ? 'is-active' : '' ?> shrink-0 w-full lg:w-[480px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 relative flex flex-col"
                        data-animal-id="<?= (int) $animal['animal_id'] ?>">

                        <!-- Área de foto otimizada (Clique na imagem redireciona para o perfil) -->
                        <div class="relative flex-1 bg-preto1/5 dark:bg-preto2/40 overflow-hidden flex items-center justify-center">
                            <div class="absolute top-3 left-3 right-3 z-20 flex gap-1.5 pointer-events-none">
                                <?php foreach ($fotos as $idxFoto => $foto): ?>
                                    <div class="h-1 flex-1 rounded-full bg-white/40 overflow-hidden">
                                        <div class="h-full bg-white foto-segmento <?= $idxFoto === 0 ? 'w-full' : 'w-0' ?>"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <a href="<?= $urlBase ?>/pagina?id=<?= (int) $animal['protetor_id'] ?>" onclick="event.stopPropagation()"
                                class="absolute top-10 left-3 z-20 flex items-center gap-2 bg-white/90 dark:bg-preto1/90 rounded-full pl-1 pr-3 py-1 shadow hover:bg-white transition">
                                <span class="w-7 h-7 rounded-full bg-rosa-1 flex items-center justify-center text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-heart-fill" viewBox="0 0 16 16">
                                        <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.707L8 2.207 1.354 8.853a.5.5 0 1 1-.708-.707z" />
                                        <path d="m14 9.293-6-6-6 6V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5zm-6-.811c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.691 0-5.018" />
                                    </svg>
                                </span>
                                <span class="text-xs font-bold text-text-dark dark:text-white"><?= htmlspecialchars($animal['nome_fantasia'] ?? 'Protetor independente') ?></span>
                            </a>

                            <div class="foto-carrossel absolute inset-0 w-full h-full flex items-center justify-center cursor-pointer"
                                onclick="window.location.href='<?= $urlPerfilAnimal ?>'">
                                <?php foreach ($fotos as $idxFoto => $foto): ?>
                                    <img src="<?= htmlspecialchars($foto) ?>"
                                        alt="Foto de <?= htmlspecialchars($animal['nome']) ?>"
                                        class="foto-item absolute inset-0 w-full h-full object-cover object-top <?= $idxFoto === 0 ? '' : 'hidden' ?>"
                                        onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($fotos) > 1): ?>
                                <button type="button" class="btn-foto-anterior absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/80 hover:bg-white flex items-center justify-center text-text-dark shadow font-bold" aria-label="Foto anterior">&lsaquo;</button>
                                <button type="button" class="btn-foto-proxima absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/80 hover:bg-white flex items-center justify-center text-text-dark shadow font-bold" aria-label="Próxima foto">&rsaquo;</button>
                            <?php endif; ?>

                            <button type="button" class="btn-dar-petisco absolute bottom-4 left-1/2 -translate-x-1/2 z-20 bg-rosa-1 hover:bg-rosa-2 text-text-dark font-shantell font-bold text-lg px-8 py-3 rounded-full shadow-lg transition active:scale-95">
                                Dar Petisco!
                            </button>
                        </div>

                        <!-- Info do animal -->
                        <div class="p-5 bg-branco dark:bg-preto1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap mb-2">
                                    <h2 class="font-shantell text-xl lg:text-2xl font-bold text-text-dark dark:text-white"><?= htmlspecialchars($animal['nome']) ?></h2>
                                    <span class="text-primary dark:text-roxinhoFofo font-bold text-sm"><?= htmlspecialchars(feedFormatarIdade($animal['dt_nasc'])) ?></span>
                                    <?php if (!empty($animal['nome_regiao'])): ?>
                                        <span class="ml-auto flex items-center gap-1 bg-sucesso/15 text-sucesso text-xs font-bold px-2.5 py-1 rounded-full">📍 <?= htmlspecialchars($animal['nome_regiao']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Badges com destaque verde caso filtradas -->
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold transition-colors <?= $fPorteAtivo ? 'bg-sucesso text-white' : 'bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white' ?>">
                                        <?= htmlspecialchars($porte) ?>
                                    </span>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold transition-colors <?= $fRacaEspecieAtivo ? 'bg-sucesso text-white' : 'bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white' ?>">
                                        <?= htmlspecialchars($animal['raca_nome'] ?? $animal['especie_nome']) ?>
                                    </span>

                                    <?php if ($comportamento): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white">
                                            <?= htmlspecialchars($comportamento) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($animal['castrado']): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $fCastradoAtivo ? 'bg-sucesso text-white font-extrabold' : 'bg-sucesso/15 text-sucesso' ?>">
                                            Castrado
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($animal['vacinado']): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $fVacinadoAtivo ? 'bg-sucesso text-white font-extrabold' : 'bg-sucesso/15 text-sucesso' ?>">
                                            Vacinado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a href="<?= $urlPerfilAnimal ?>"
                                class="block w-full text-center mt-4 py-2.5 px-4 rounded-full bg-[#4e4870] hover:bg-[#3f3a5b] text-white text-xs lg:text-sm font-bold transition shadow-sm">
                                Ver perfil completo
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($temMais)): ?>
                    <div id="card-fim-feed" class="feed-card is-active shrink-0 w-full lg:w-[480px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 flex flex-col items-center justify-center text-center p-8 gap-4">
                        <div class="flex items-center justify-center gap-3">
                            <img src="<?= $urlBase ?>/assets/img/patinha-baixo.png" alt="" class="w-8 h-8 -rotate-12">
                            <img src="<?= $urlBase ?>/assets/img/patinha-cima.png" alt="" class="w-8 h-8 rotate-12 mt-3">
                        </div>
                        <p class="font-poppins text-text-dark dark:text-white font-bold">Você já viu todos os animais disponíveis por aqui!</p>
                        <p class="font-poppins text-text-muted text-sm">Volte outra hora para conferir novidades.</p>
                        <button type="button" onclick="window.location.href='<?= $urlBase ?>/feed'" class="btn-primario"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z" />
                                <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466" />
                            </svg> Atualizar Feed</button>
                    </div>
                <?php else: ?>
                    <div id="sentinela-fim-feed" class="shrink-0 w-2 h-2 lg:w-2 lg:h-full"></div>
                <?php endif; ?>
            </div>

            <!-- Botão Próximo estilizado -->
            <button type="button" id="btn-proximo-animal" aria-label="Próximo animal"
                class="hidden lg:flex h-10 w-10 items-center justify-center rounded-full border-4 border-primary bg-branco text-primary shadow-lg ring-2 ring-primary/20 transition hover:bg-rosaClaro2 dark:bg-preto2 dark:text-branco shrink-0 ml-4 z-10 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <div id="loading-mais-animais" class="hidden text-center py-6 text-sm text-text-muted">Carregando mais animais...</div>
    <?php endif; ?>
</div>

<!-- BARRA INFERIOR MOBILE -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-primary flex items-center justify-around h-16 shadow-[0_-4px_12px_rgba(0,0,0,0.2)]">
    <a href="<?= $urlBase ?>/feed" class="flex flex-col items-center justify-center text-white">
        <img src="<?= $urlBase ?>/assets/icons/navbar/home.svg" alt="" class="w-6 h-6 brightness-0 invert">
    </a>
    <button type="button" onclick="document.getElementById('modal-filtros-feed').classList.remove('hidden')" class="flex flex-col items-center justify-center text-white/70">
        <img src="<?= $urlBase ?>/assets/icons/navbar/pesquisar.svg" alt="Buscar" class="w-6 h-6 brightness-0 invert opacity-70">
    </button>
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
            <div>
                <label for="busca-feed" class="label-padrao">Buscar animal ou responsável</label>
                <input class="input-padrao" id="busca-feed" name="q" maxlength="100" value="<?= htmlspecialchars($filtrosAtuais['q'] ?? '') ?>">
            </div>

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
    (function() {
        'use strict';

        const urlBase = '<?= $urlBase ?>';
        const filtrosAtuais = <?= json_encode($filtrosAtuais, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        let proximoOffset = <?= (int) ($proximoOffset ?? 0) ?>;
        let temMais = <?= !empty($temMais) ? 'true' : 'false' ?>;
        let carregando = false;

        // ---------- RF 08: "Dar Petisco!" ----------
        async function enviarPetisco(botao, e) {
            if (e) e.stopPropagation();
            const card = botao.closest('.feed-card');
            const animalId = card ? card.dataset.animalId : null;
            if (!animalId) return;

            const textoOriginal = botao.textContent;
            botao.disabled = true;
            botao.textContent = 'Enviando...';

            try {
                const corpo = new URLSearchParams({
                    animal_id: animalId,
                    csrf_token: <?= json_encode($_SESSION['csrf_token'] ?? '') ?>
                });
                const resposta = await fetch(`${urlBase}/solicitacoes/criar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        Accept: 'application/json'
                    },
                    body: corpo
                });
                const resultado = await resposta.json();

                if (resposta.ok && resultado.status === 'sucesso') {
                    botao.textContent = 'Petisco enviado! 🐾';
                    if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('sucesso', resultado.mensagem);
                } else {
                    botao.disabled = false;
                    botao.textContent = textoOriginal;
                    if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', resultado.mensagem || 'Não foi possível enviar o petisco.');
                }
            } catch (erro) {
                botao.disabled = false;
                botao.textContent = textoOriginal;
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'Erro de conexão ao enviar o petisco.');
            }
        }

        document.querySelectorAll('.btn-dar-petisco').forEach(function(botao) {
            botao.addEventListener('click', function(e) {
                enviarPetisco(this, e);
            });
        });

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

            card.querySelector('.btn-foto-anterior')?.addEventListener('click', function(e) {
                e.stopPropagation();
                mostrar(indice - 1);
            });
            card.querySelector('.btn-foto-proxima')?.addEventListener('click', function(e) {
                e.stopPropagation();
                mostrar(indice + 1);
            });
        }

        document.querySelectorAll('.feed-card').forEach(iniciarCarrossel);

        // ---------- Controle do Feed e Navegação ----------
        const track = document.getElementById('feed-track');
        let cardAtivoAtual = null;

        if (track) {
            requestAnimationFrame(() => {
                track.scrollLeft = 0;
            });

            const observadorAtivo = new IntersectionObserver(function(entradas) {
                entradas.forEach(function(entrada) {
                    if (entrada.isIntersecting && entrada.intersectionRatio >= 0.5) {
                        document.querySelectorAll('.feed-card.is-active').forEach(c => c.classList.remove('is-active'));
                        entrada.target.classList.add('is-active');
                        cardAtivoAtual = entrada.target;
                    }
                });
            }, {
                root: track,
                threshold: 0.5
            });

            document.querySelectorAll('.feed-card').forEach(card => observadorAtivo.observe(card));

            const primeiroCard = track.querySelector('.feed-card');
            if (primeiroCard) cardAtivoAtual = primeiroCard;

            function irParaVizinho(direcao) {
                const cards = Array.from(track.querySelectorAll('.feed-card'));
                if (!cards.length) return;

                let indexAtual = cardAtivoAtual ? cards.indexOf(cardAtivoAtual) : 0;
                if (indexAtual === -1) indexAtual = 0;

                let novoIndex = indexAtual + direcao;
                if (novoIndex < 0) novoIndex = 0;
                if (novoIndex >= cards.length) novoIndex = cards.length - 1;

                const alvo = cards[novoIndex];
                if (alvo) {
                    alvo.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            }

            document.getElementById('btn-anterior-animal')?.addEventListener('click', () => irParaVizinho(-1));
            document.getElementById('btn-proximo-animal')?.addEventListener('click', () => irParaVizinho(1));

            // Scroll Infinito
            const sentinela = document.getElementById('sentinela-fim-feed');
            if (sentinela) {
                const observadorFim = new IntersectionObserver(function(entradas) {
                    entradas.forEach(function(entrada) {
                        if (entrada.isIntersecting) {
                            carregarMaisAnimais();
                        }
                    });
                }, {
                    root: track,
                    threshold: 0.1
                });

                observadorFim.observe(sentinela);

                window.registrarNovoCardParaObservador = function(card) {
                    observadorAtivo.observe(card);
                };
            }
        }

        async function carregarMaisAnimais() {
            if (carregando || !temMais) return;
            carregando = true;
            document.getElementById('loading-mais-animais')?.classList.remove('hidden');

            try {
                const params = new URLSearchParams({
                    ...filtrosAtuais,
                    offset: proximoOffset
                });
                Array.from(params.keys()).forEach(k => {
                    if (!params.get(k)) params.delete(k);
                });

                const response = await fetch(`${urlBase}/feed/carregar-mais?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const resultado = await response.json();

                if (response.ok && resultado.status === 'sucesso') {
                    resultado.animais.forEach(criarElementoCard);
                    proximoOffset = resultado.proximoOffset;
                    temMais = resultado.temMais;

                    if (!temMais) {
                        criarCardFimDeFeed();
                    }
                }
            } catch (erro) {
                console.error('Erro ao carregar mais animais:', erro);
            } finally {
                carregando = false;
                document.getElementById('loading-mais-animais')?.classList.add('hidden');
            }
        }

        function criarCardFimDeFeed() {
            const sentinela = document.getElementById('sentinela-fim-feed');
            if (!sentinela || document.getElementById('card-fim-feed')) return;

            const card = document.createElement('div');
            card.id = 'card-fim-feed';
            card.className = 'feed-card shrink-0 w-full lg:w-[480px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 flex flex-col items-center justify-center text-center p-8 gap-4';
            card.innerHTML = `
                <div class="flex items-center justify-center gap-3">
                    <img src="${urlBase}/assets/img/patinha-baixo.png" alt="" class="w-8 h-8 -rotate-12">
                    <img src="${urlBase}/assets/img/patinha-cima.png" alt="" class="w-8 h-8 rotate-12 mt-3">
                </div>
                <p class="font-poppins text-text-dark dark:text-white font-bold">Você já viu todos os animais disponíveis por aqui!</p>
                <p class="font-poppins text-text-muted text-sm">Volte outra hora para conferir novidades.</p>
                <button type="button" class="btn-primario">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z" />
                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466" />
                    </svg> Atualizar Feed
                </button>
            `;

            card.querySelector('button').addEventListener('click', () => {
                window.location.href = `${urlBase}/feed`;
            });

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

        const PORTE_LABELS = {
            pequeno: 'Pequeno Porte',
            medio: 'Médio Porte',
            grande: 'Grande Porte'
        };

        function escaparHtml(valor) {
            return String(valor ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function criarElementoCard(animal) {
            animal = {
                ...animal
            };
            for (const chave of ['nome', 'nome_fantasia', 'nome_regiao', 'raca_nome', 'especie_nome', 'porte', 'sexo', 'url_detalhes']) animal[chave] = escaparHtml(animal[chave]);
            animal.animal_id = Number(animal.animal_id);
            animal.protetor_id = Number(animal.protetor_id);
            animal.fotos = (animal.fotos || []).map(escaparHtml);
            const fotos = animal.fotos && animal.fotos.length ? animal.fotos : [`${urlBase}/assets/img/perfil-placeholder.png`];
            const sentinela = document.getElementById('sentinela-fim-feed');

            const card = document.createElement('div');
            card.className = 'feed-card shrink-0 w-full lg:w-[480px] h-full rounded-3xl overflow-hidden bg-branco dark:bg-preto1 shadow-xl border border-rosa-2 dark:border-preto3 relative flex flex-col';
            card.dataset.animalId = animal.animal_id;

            const segmentosHtml = fotos.map((_, i) => `<div class="h-1 flex-1 rounded-full bg-white/40 overflow-hidden"><div class="h-full bg-white foto-segmento ${i === 0 ? 'w-full' : 'w-0'}"></div></div>`).join('');
            const fotosHtml = fotos.map((src, i) => `<img src="${src}" alt="Foto de ${animal.nome}" class="foto-item absolute inset-0 w-full h-full object-cover object-top ${i === 0 ? '' : 'hidden'}" onerror="this.onerror=null;this.src='${urlBase}/assets/img/perfil-placeholder.png';">`).join('');
            const setasHtml = fotos.length > 1 ?
                `<button type="button" class="btn-foto-anterior absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/80 hover:bg-white flex items-center justify-center text-text-dark shadow font-bold">&lsaquo;</button>
               <button type="button" class="btn-foto-proxima absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/80 hover:bg-white flex items-center justify-center text-text-dark shadow font-bold">&rsaquo;</button>` :
                '';

            const porte = PORTE_LABELS[animal.porte] || animal.porte;

            const fPorteAtivo = filtrosAtuais.porte && filtrosAtuais.porte === animal.porte;
            const fCastradoAtivo = Boolean(filtrosAtuais.castrado);
            const fVacinadoAtivo = Boolean(filtrosAtuais.vacinado);
            const fRacaEspecieAtivo = Boolean(filtrosAtuais.raca_id || filtrosAtuais.especie_id);

            const badges = [
                `<span class="px-3 py-1 rounded-full text-xs font-bold transition-colors ${fPorteAtivo ? 'bg-sucesso text-white' : 'bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white'}">${porte}</span>`,
                `<span class="px-3 py-1 rounded-full text-xs font-bold transition-colors ${fRacaEspecieAtivo ? 'bg-sucesso text-white' : 'bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white'}">${animal.raca_nome || animal.especie_nome}</span>`,
            ];

            if (animal.castrado) {
                badges.push(`<span class="px-3 py-1 rounded-full text-xs font-bold ${fCastradoAtivo ? 'bg-sucesso text-white font-extrabold' : 'bg-sucesso/15 text-sucesso'}">Castrado</span>`);
            }
            if (animal.vacinado) {
                badges.push(`<span class="px-3 py-1 rounded-full text-xs font-bold ${fVacinadoAtivo ? 'bg-sucesso text-white font-extrabold' : 'bg-sucesso/15 text-sucesso'}">Vacinado</span>`);
            }

            card.innerHTML = `
            <div class="relative flex-1 bg-preto1/5 dark:bg-preto2/40 overflow-hidden flex items-center justify-center">
                <div class="absolute top-3 left-3 right-3 z-20 flex gap-1.5 pointer-events-none">${segmentosHtml}</div>
                <a href="${urlBase}/pagina?id=${animal.protetor_id}" onclick="event.stopPropagation()"
                   class="absolute top-10 left-3 z-20 flex items-center gap-2 bg-white/90 dark:bg-preto1/90 rounded-full pl-1 pr-3 py-1 shadow hover:bg-white transition">
                    <span class="w-7 h-7 rounded-full bg-rosa-1 flex items-center justify-center text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-heart-fill" viewBox="0 0 16 16">
                            <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.707L8 2.207 1.354 8.853a.5.5 0 1 1-.708-.707z"/>
                            <path d="m14 9.293-6-6-6 6V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5zm-6-.811c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.691 0-5.018"/>
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-text-dark dark:text-white">${animal.nome_fantasia || 'Protetor independente'}</span>
                </a>
                <div class="foto-carrossel absolute inset-0 w-full h-full flex items-center justify-center cursor-pointer" onclick="window.location.href='${animal.url_detalhes}'">${fotosHtml}</div>
                ${setasHtml}
                <button type="button" class="btn-dar-petisco absolute bottom-4 left-1/2 -translate-x-1/2 z-20 bg-rosa-1 hover:bg-rosa-2 text-text-dark font-shantell font-bold text-lg px-8 py-3 rounded-full shadow-lg transition active:scale-95">Dar Petisco!</button>
            </div>
            <div class="p-5 bg-branco dark:bg-preto1 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <h2 class="font-shantell text-xl lg:text-2xl font-bold text-text-dark dark:text-white">${animal.nome}</h2>
                        <span class="text-primary dark:text-roxinhoFofo font-bold text-sm">${formatarIdade(animal.dt_nasc)}</span>
                        ${animal.nome_regiao ? `<span class="ml-auto flex items-center gap-1 bg-sucesso/15 text-sucesso text-xs font-bold px-2.5 py-1 rounded-full">📍 ${animal.nome_regiao}</span>` : ''}
                    </div>
                    <div class="flex flex-wrap gap-1.5 mt-2">${badges.join('')}</div>
                </div>
                <a href="${animal.url_detalhes}" class="block w-full text-center mt-4 py-2.5 px-4 rounded-full bg-[#4e4870] hover:bg-[#3f3a5b] text-white text-xs lg:text-sm font-bold transition shadow-sm">Ver perfil completo</a>
            </div>
            `;

            iniciarCarrossel(card);
            card.querySelector('.btn-dar-petisco')?.addEventListener('click', function(e) {
                enviarPetisco(this, e);
            });

            if (sentinela) sentinela.before(card);
            else track.appendChild(card);
            if (typeof window.registrarNovoCardParaObservador === 'function') window.registrarNovoCardParaObservador(card);
        }

        // Filtro Raça assíncrono
        // Filtro Raça assíncrono
        const selEspecie = document.getElementById('filtro-especie');
        const selRaca = document.getElementById('filtro-raca');
        if (selEspecie && selRaca) {
            let racaInicialCarregada = false; // Controla se é o carregamento inicial da página

            selEspecie.addEventListener('change', async function() {
                const eId = this.value;
                selRaca.innerHTML = '<option value="">Todas</option>';
                if (!eId) return;

                try {
                    const r = await fetch(`${urlBase}/raca/json?especie_id=${eId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const res = await r.json();
                    if (r.ok && res.sucesso === true)  {
                        const oldRaca = selRaca.dataset.oldValue;
                        res.dados.forEach(raca => {
                            const opt = document.createElement('option');
                            opt.value = raca.raca_id;
                            opt.textContent = raca.nome;
                            // Aplica o valor antigo apenas na primeira vez que a página carrega
                            if (!racaInicialCarregada && String(raca.raca_id) === String(oldRaca)) {
                                opt.selected = true;
                            }
                            selRaca.appendChild(opt);
                        });
                        racaInicialCarregada = true; // Evita que selecione a raça antiga ao trocar de espécie manualmente
                    }
                } catch (e) {
                    console.error('Erro ao carregar raças:', e);
                }
            });
            if (selEspecie.value) {
                selEspecie.dispatchEvent(new Event('change'));
            }
        }

        // Auto-complete Bairro/Região (Datalist invisível mapeando Nome -> ID)
        const inputBairro = document.getElementById('feed-input-busca-bairro');
        const idHidden = document.getElementById('feed-regiao-id-hidden');
        if (inputBairro && idHidden) {
            const opcoes = Array.from(document.querySelectorAll('#feed-lista-regioes option'));
            inputBairro.addEventListener('input', function() {
                const val = this.value.trim().toLowerCase();
                const obj = opcoes.find(o => o.value.toLowerCase() === val);
                idHidden.value = obj ? obj.dataset.id : '';
            });
            inputBairro.addEventListener('change', function() {
                const val = this.value.trim().toLowerCase();
                const obj = opcoes.find(o => o.value.toLowerCase() === val);
                idHidden.value = obj ? obj.dataset.id : '';
            });
        }
    })();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>