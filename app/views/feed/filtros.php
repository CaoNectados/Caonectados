<?php
$idModalFiltros = $idModalFiltros ?? 'modal-filtros-feed';
$destinoFiltros = $destinoFiltros ?? '/feed';
?>
<!-- MODAL DE FILTROS -->
<div role="dialog" aria-modal="true" aria-label="Filtros de animais" id="<?= e($idModalFiltros) ?>" class="fixed inset-0 bg-black/70 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-branco dark:bg-preto1 rounded-3xl max-w-md w-full p-6 max-h-[85vh] overflow-y-auto border border-rosa-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-shantell text-xl font-bold text-text-dark dark:text-white">Filtros</h3>
            <button type="button" onclick="document.getElementById('<?= e($idModalFiltros) ?>').classList.add('hidden')" aria-label="Fechar filtros" class="text-text-muted hover:text-erro text-3xl font-bold min-w-11 min-h-11">&times;</button>
        </div>

        <form method="GET" action="<?= $urlBase ?><?= e($destinoFiltros) ?>" class="space-y-4">
<label for="busca-feed">Buscar animal ou responsável</label>
<input class="input-padrao" id="busca-feed" name="q" maxlength="100" value="<?= htmlspecialchars($filtrosAtuais['q'] ?? '') ?>">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="filtro-porte" class="label-padrao">Porte</label>
                    <select id="filtro-porte" name="porte" class="input-padrao">
                        <option value="">Todos</option>
                        <option value="pequeno" <?= ($filtrosAtuais['porte'] ?? '') === 'pequeno' ? 'selected' : '' ?>>Pequeno</option>
                        <option value="medio" <?= ($filtrosAtuais['porte'] ?? '') === 'medio' ? 'selected' : '' ?>>Médio</option>
                        <option value="grande" <?= ($filtrosAtuais['porte'] ?? '') === 'grande' ? 'selected' : '' ?>>Grande</option>
                    </select>
                </div>
                <div>
                    <label for="filtro-sexo" class="label-padrao">Sexo</label>
                    <select id="filtro-sexo" name="sexo" class="input-padrao">
                        <option value="">Todos</option>
                        <option value="macho" <?= ($filtrosAtuais['sexo'] ?? '') === 'macho' ? 'selected' : '' ?>>Macho</option>
                        <option value="femea" <?= ($filtrosAtuais['sexo'] ?? '') === 'femea' ? 'selected' : '' ?>>Fêmea</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="filtro-especie" class="label-padrao">Espécie</label>
                    <select name="especie_id" id="filtro-especie" class="input-padrao">
                        <option value="">Todas</option>
                        <?php foreach ($especies as $especie): ?>
                            <option value="<?= $especie['especie_id'] ?>" <?= (string) ($filtrosAtuais['especie_id'] ?? '') === (string) $especie['especie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($especie['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filtro-raca" class="label-padrao">Raça</label>
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
                <label for="filtro-protetor" class="label-padrao">ONG / Protetor</label>
                <select id="filtro-protetor" name="protetor_id" class="input-padrao">
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
                <a href="<?= $urlBase ?><?= e($destinoFiltros) ?>" class="flex-1 text-center bg-cinzaMarrom/20 text-text-dark dark:text-white py-3 rounded-full font-bold text-sm">Limpar</a>
                <button type="submit" class="flex-1 btn-primario justify-center">Aplicar</button>
            </div>
        </form>
    </div>
</div>
