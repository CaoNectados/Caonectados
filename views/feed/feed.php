<?php
require_once __DIR__ . '/../templates/header.php';

/** @var array $animais */
$animais = $animais ?? [];
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$porteLabels = ['pequeno' => 'Pequeno', 'medio' => 'Médio', 'grande' => 'Grande'];
$sexoLabels  = ['macho' => 'Macho', 'femea' => 'Fêmea', 'indefinido' => 'Indefinido'];

$filtros = $filtros ?? [];
$especies = $especies ?? [];
$regioes = $regioes ?? [];
$protetores = $protetores ?? [];
$paginaAtual = $paginaAtual ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$total = $total ?? count($animais);

// Reaproveita os filtros atuais ao montar os links de paginação
$queryFiltros = array_filter([
    'porte'       => $filtros['porte'] ?? '',
    'sexo'        => $filtros['sexo'] ?? '',
    'castrado'    => $filtros['castrado'] ?? '',
    'especie_id'  => $filtros['especie_id'] ?? '',
    'protetor_id' => $filtros['protetor_id'] ?? '',
    'regiao_id'   => $filtros['regiao_id'] ?? '',
], fn($v) => $v !== '');
?>

<div class="min-h-screen bg-background pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="font-shantell text-2xl sm:text-3xl font-bold text-text-dark dark:text-white">Feed de Adoção</h1>
            <p class="text-sm text-text-muted mt-1">Conheça os animais disponíveis para adoção nas ONGs e protetores parceiros.</p>
        </div>

        <!-- Filtros -->
        <form method="GET" action="<?= $urlBase ?>/feed" class="card-padrao mb-6 border border-rosa-1 dark:border-preto3 p-4 bg-surface dark:bg-surface">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <select name="especie_id" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="">Espécie: Todas</option>
                    <?php foreach ($especies as $especie): ?>
                        <option value="<?= (int) $especie['especie_id'] ?>" <?= ((string) ($filtros['especie_id'] ?? '') === (string) $especie['especie_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($especie['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="porte" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="">Porte: Todos</option>
                    <?php foreach ($porteLabels as $valor => $label): ?>
                        <option value="<?= $valor ?>" <?= (($filtros['porte'] ?? '') === $valor) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="sexo" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="">Sexo: Todos</option>
                    <?php foreach ($sexoLabels as $valor => $label): ?>
                        <option value="<?= $valor ?>" <?= (($filtros['sexo'] ?? '') === $valor) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="castrado" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="">Castrado: Todos</option>
                    <option value="1" <?= (($filtros['castrado'] ?? '') === '1') ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= (($filtros['castrado'] ?? '') === '0') ? 'selected' : '' ?>>Não</option>
                </select>

                <select name="regiao_id" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="">Região: Todas</option>
                    <?php foreach ($regioes as $regiao): ?>
                        <option value="<?= (int) $regiao->getRegiaoId() ?>" <?= ((string) ($filtros['regiao_id'] ?? '') === (string) $regiao->getRegiaoId()) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($regiao->getNomeRegiao()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="protetor_id" onchange="this.form.submit()" class="input-padrao py-1.5 px-3 text-xs bg-branco dark:bg-preto2 text-text-dark dark:text-white border-cinzaMarrom/40">
                    <option value="">Responsável: Todos</option>
                    <?php foreach ($protetores as $protetor): ?>
                        <option value="<?= (int) $protetor['protetor_id'] ?>" <?= ((string) ($filtros['protetor_id'] ?? '') === (string) $protetor['protetor_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($protetor['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($queryFiltros)): ?>
                <div class="mt-3 flex justify-end">
                    <a href="<?= $urlBase ?>/feed" class="text-xs font-semibold text-primary dark:text-roxinhoFofo hover:underline">Limpar Filtros</a>
                </div>
            <?php endif; ?>
        </form>

        <p class="text-xs text-text-muted mb-4"><?= (int) $total ?> animal(is) encontrado(s)</p>

        <?php if (empty($animais)): ?>
            <div class="bg-surface dark:bg-preto1 rounded-3xl border border-rosa-2 dark:border-preto3 p-10 text-center shadow-sm">
                <img src="<?= $urlBase ?>/assets/img/patinha-baixo.png" alt="" class="w-12 h-12 mx-auto mb-4 opacity-60">
                <p class="font-poppins text-text-muted">Nenhum animal disponível para adoção no momento. Volte em breve!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <?php foreach ($animais as $animal): ?>
                    <?php
                    $fotoPrincipal = $animal['foto_principal'] ?? null;
                    if (!empty($fotoPrincipal)) {
                        $fotoLimpa = ltrim(trim($fotoPrincipal), '/');
                        $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
                        $srcFoto = $urlBase . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
                    } else {
                        $srcFoto = $urlBase . '/assets/img/perfil-placeholder.png';
                    }
                    $porte = $porteLabels[$animal['porte']] ?? ucfirst((string) ($animal['porte'] ?? ''));
                    $sexo  = $sexoLabels[$animal['sexo']] ?? ucfirst((string) ($animal['sexo'] ?? ''));
                    ?>
                    <a href="<?= $urlBase ?>/animal/mostrar?id=<?= (int) $animal['animal_id'] ?>"
                       class="group bg-surface dark:bg-preto1 rounded-2xl overflow-hidden border border-rosa-2 dark:border-preto3 shadow-sm hover:shadow-md transition">
                        <div class="aspect-square w-full overflow-hidden bg-branco dark:bg-preto2">
                            <img src="<?= $srcFoto ?>"
                                 alt="Foto de <?= htmlspecialchars($animal['nome']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                        </div>
                        <div class="p-3">
                            <h3 class="font-shantell font-bold text-text-dark dark:text-white truncate"><?= htmlspecialchars($animal['nome']) ?></h3>
                            <p class="text-xs text-text-muted truncate"><?= htmlspecialchars($animal['raca_nome'] ?? 'Raça não informada') ?></p>
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($porte) ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($sexo) ?></span>
                            </div>
                            <p class="text-[11px] text-text-muted mt-2 truncate inline-flex items-center gap-1"><?= icone('home', 'h-3.5 w-3.5 shrink-0') ?> <?= htmlspecialchars($animal['nome_fantasia'] ?? 'Protetor independente') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <div class="flex justify-between items-center px-4 py-4 mt-6 bg-surface dark:bg-surface rounded-xl border border-cinzaMarrom/30 text-xs">
                    <span class="text-text-muted">
                        Página <?= (int) $paginaAtual ?> de <?= (int) $totalPaginas ?> (Total: <?= (int) $total ?>)
                    </span>
                    <div class="flex gap-1">
                        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                            <a href="<?= $urlBase ?>/feed?<?= http_build_query(array_merge($queryFiltros, ['pagina' => $p])) ?>"
                               class="px-3 py-1 rounded-lg font-bold <?= $p === (int) $paginaAtual ? 'bg-primary text-white' : 'bg-branco dark:bg-preto1 text-text-dark dark:text-white border border-cinzaMarrom/30' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
