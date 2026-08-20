<?php
require_once __DIR__ . '/../templates/header.php';

$animalRaw = $_SESSION['animal'] ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

if (!$animalRaw) {
    echo "<main class='flex flex-col items-center justify-center p-4'>";
    echo "<div class='bg-branco dark:bg-preto2 p-8 rounded-[2rem] shadow-lg text-center border border-cinzaMarrom/20 dark:border-branco/10'>";
    echo "<h2 class='font-shantell text-2xl font-bold text-text-dark dark:text-branco mb-4'>Erro: Animal não encontrado.</h2>";
    echo '<a href="' . URL_BASE . '/animal" class="text-rosaAlerta hover:underline dark:text-rosa-1 font-bold">Voltar</a>';
    echo "</div></main>";
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}

$isObj = is_object($animalRaw);

$animalId       = $isObj ? $animalRaw->getAnimalId() : ($animalRaw['animal_id'] ?? 0);
$racaId         = $isObj ? $animalRaw->getRacaId() : ($animalRaw['raca_id'] ?? 0);

$nome           = !empty($old['nome']) ? $old['nome'] : ($isObj ? $animalRaw->getNome() : ($animalRaw['nome'] ?? ''));
$dtNasc         = !empty($old['dt_nasc']) ? $old['dt_nasc'] : ($isObj ? $animalRaw->getDtNasc() : ($animalRaw['dt_nasc'] ?? ''));
$sexo           = !empty($old['sexo']) ? $old['sexo'] : ($isObj ? $animalRaw->getSexo() : ($animalRaw['sexo'] ?? ''));
$porte          = !empty($old['porte']) ? $old['porte'] : ($isObj ? $animalRaw->getPorte() : ($animalRaw['porte'] ?? ''));
$status         = !empty($old['status']) ? $old['status'] : ($isObj ? $animalRaw->getStatus() : ($animalRaw['status'] ?? 'disponivel'));
$comportamento  = !empty($old['comportamento']) ? $old['comportamento'] : ($isObj ? $animalRaw->getComportamento() : ($animalRaw['comportamento'] ?? ''));
$historicoSaude = !empty($old['historico_saude']) ? $old['historico_saude'] : ($isObj ? $animalRaw->getHistoricoSaude() : ($animalRaw['historico_saude'] ?? ''));
$descricao      = !empty($old['descricao']) ? $old['descricao'] : ($isObj ? $animalRaw->getDescricao() : ($animalRaw['descricao'] ?? ''));

$vacinado = !empty($old) ? !empty($old['vacinado']) : ($isObj ? $animalRaw->isVacinado() : !empty($animalRaw['vacinado']));
$castrado = !empty($old) ? !empty($old['castrado']) : ($isObj ? $animalRaw->isCastrado() : !empty($animalRaw['castrado']));

$fotoPrincipal = $isObj ? $animalRaw->getFotoPrincipal() : ($animalRaw['foto_principal'] ?? null);
?>

<main class="flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-3xl flex flex-col items-center py-10 px-8 relative bg-branco dark:bg-fundoChat-escuro shadow-[0_8px_20px_rgba(0,0,0,0.12)] rounded-[2.5rem] border border-cinzaMarrom/20 dark:border-branco/10 transition-colors duration-300">

        <h1 class="font-shantell text-[32px] font-bold text-text-dark dark:text-branco text-center mb-10 transition-colors duration-300">
            Editar o Animal
        </h1>

        <!-- Exibição de Erros da Sessão -->
        <?php if (!empty($_SESSION['erros'])): ?>
            <div class="w-full max-w-md p-4 mb-6 text-sm rounded-xl font-poppins text-center bg-red-100 text-red-800 border border-red-300">
                <?php foreach ($_SESSION['erros'] as $erro): ?>
                    <p><?= htmlspecialchars($erro) ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['erros']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= URL_BASE ?>/animal/editar" method="POST" enctype="multipart/form-data" class="w-full max-w-md flex flex-col gap-6" data-no-autosave>
            <input type="hidden" name="id" value="<?= htmlspecialchars($animalId) ?>">
            <input type="hidden" name="raca_id" value="<?= htmlspecialchars($racaId) ?>">

            <div>
                <label for="foto" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Foto do animal</label>
                <?php if (!empty($fotoPrincipal)): ?>
                    <img src="<?= URL_BASE ?>/<?= htmlspecialchars($fotoPrincipal) ?>" alt="Foto atual de <?= htmlspecialchars($nome) ?>" class="w-24 h-24 object-cover rounded-xl mb-3 border-2 border-text-dark dark:border-branco/30">
                <?php endif; ?>
                <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors">
            </div>

            <div>
                <label for="nome" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Nome do animal <span class="text-rosaAlerta">*</span></label>
                <input type="text" id="nome" name="nome" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" value="<?= htmlspecialchars($nome) ?>" placeholder="Ex: Thor" maxlength="120" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="dt_nasc" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Data de nascimento <span class="text-rosaAlerta">*</span></label>
                    <input type="date" id="dt_nasc" name="dt_nasc" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" value="<?= htmlspecialchars($dtNasc ?? '') ?>">
                </div>
                <div>
                    <label for="sexo" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Sexo <span class="text-rosaAlerta">*</span></label>
                    <select id="sexo" name="sexo" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" required>
                        <option value="" class="dark:bg-preto2">Escolha</option>
                        <option value="macho" class="dark:bg-preto2" <?= $sexo === 'macho' ? 'selected' : '' ?>>Macho</option>
                        <option value="femea" class="dark:bg-preto2" <?= $sexo === 'femea' ? 'selected' : '' ?>>Fêmea</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="porte" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Porte <span class="text-rosaAlerta">*</span></label>
                    <select id="porte" name="porte" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" required>
                        <option value="" class="dark:bg-preto2">Escolha</option>
                        <option value="pequeno" class="dark:bg-preto2" <?= $porte === 'pequeno' ? 'selected' : '' ?>>Pequeno</option>
                        <option value="medio" class="dark:bg-preto2" <?= $porte === 'medio' ? 'selected' : '' ?>>Médio</option>
                        <option value="grande" class="dark:bg-preto2" <?= $porte === 'grande' ? 'selected' : '' ?>>Grande</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Status <span class="text-rosaAlerta">*</span></label>
                    <select id="status" name="status" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" required>
                        <option value="disponivel" class="dark:bg-preto2" <?= $status === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                        <option value="em_analise" class="dark:bg-preto2" <?= $status === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                        <option value="adotado" class="dark:bg-preto2" <?= $status === 'adotado' ? 'selected' : '' ?>>Adotado</option>
                        <option value="desativado" class="dark:bg-preto2" <?= $status === 'desativado' ? 'selected' : '' ?>>Desativado</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="comportamento" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Comportamento</label>
                <select id="comportamento" name="comportamento" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors">
                    <option value="" class="dark:bg-preto2">Escolha (Opcional)</option>
                    <option value="calmo" class="dark:bg-preto2" <?= $comportamento === 'calmo' ? 'selected' : '' ?>>Calmo</option>
                    <option value="ativo" class="dark:bg-preto2" <?= $comportamento === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="docil" class="dark:bg-preto2" <?= $comportamento === 'docil' ? 'selected' : '' ?>>Dócil</option>
                    <option value="arisco" class="dark:bg-preto2" <?= $comportamento === 'arisco' ? 'selected' : '' ?>>Arisco</option>
                </select>
            </div>

            <div class="flex items-center gap-8 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="vacinado" value="1" <?= $vacinado ? 'checked' : '' ?> class="w-5 h-5 accent-rosaAlerta dark:bg-preto2 border-2 border-text-dark dark:border-branco/30 rounded focus:ring-roxinhoFofo focus:ring-2">
                    <span class="font-poppins text-sm font-bold text-text-dark dark:text-branco/90">Vacinado</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="castrado" value="1" <?= $castrado ? 'checked' : '' ?> class="w-5 h-5 accent-rosaAlerta dark:bg-preto2 border-2 border-text-dark dark:border-branco/30 rounded focus:ring-roxinhoFofo focus:ring-2">
                    <span class="font-poppins text-sm font-bold text-text-dark dark:text-branco/90">Castrado</span>
                </label>
            </div>

            <div>
                <label for="historico_saude" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Saúde</label>
                <textarea id="historico_saude" name="historico_saude" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none resize-y transition-colors" rows="3" placeholder="Saúde do animal..."><?= htmlspecialchars($historicoSaude ?? '') ?></textarea>
            </div>

            <div>
                <label for="descricao" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Sobre/Mais <span class="text-rosaAlerta">*</span></label>
                <textarea id="descricao" name="descricao" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none resize-y transition-colors" rows="3" placeholder="História e detalhes..." required><?= htmlspecialchars($descricao ?? '') ?></textarea>
            </div>

            <div class="flex flex-col items-center gap-4 mt-8">
                <button type="submit" class="w-full max-w-[200px] py-4 bg-rosaAlerta hover:bg-rosa-2 text-white dark:hover:text-text-dark font-bold rounded-full shadow-md transition-all duration-300 hover:-translate-y-1">
                    Continuar
                </button>
                <a href="<?= URL_BASE ?>/animal" class="font-poppins text-sm font-medium text-text-muted dark:text-branco/60 hover:text-rosaAlerta dark:hover:text-branco transition-colors underline">
                    Cancelar e Voltar
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>