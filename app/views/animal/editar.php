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

// Prévia da foto: se veio um recorte novo de uma tentativa anterior que deu erro (base64),
// usa ele; senão mostra a foto que já está salva no animal.
$fotoCortadaOld = $old['foto_cortada'] ?? '';
if (!empty($fotoCortadaOld)) {
    $previewFotoUrl = $fotoCortadaOld;
} elseif (!empty($fotoPrincipal)) {
    $previewFotoUrl = URL_BASE . '/' . ltrim($fotoPrincipal, '/');
} else {
    $previewFotoUrl = '';
}
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
                <label class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2 text-center">Foto do animal</label>
                <input type="hidden" name="foto_cortada" id="foto_cortada_base64" value="<?= htmlspecialchars($fotoCortadaOld) ?>">
                <input type="file" id="input-foto-original" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden" onchange="iniciarCropperAnimal(event)">

                <div class="flex justify-center">
                    <div id="container-foto-principal" onclick="document.getElementById('input-foto-original').click()"
                        class="relative w-40 h-52 sm:w-44 sm:h-56 rounded-2xl border-2 border-dashed border-text-dark/40 dark:border-branco/30 bg-transparent dark:bg-preto2 flex flex-col items-center justify-center gap-2 cursor-pointer overflow-hidden hover:border-rosaAlerta dark:hover:border-rosaAlerta transition-colors">
                        <img id="preview-foto-principal" src="<?= htmlspecialchars($previewFotoUrl) ?>" alt="Prévia da foto principal" class="<?= empty($previewFotoUrl) ? 'hidden' : '' ?> absolute inset-0 w-full h-full object-cover">
                        <div id="placeholder-foto-principal" class="<?= empty($previewFotoUrl) ? '' : 'hidden' ?> flex flex-col items-center justify-center gap-1 text-text-dark dark:text-branco/70 pointer-events-none">
                            <span class="text-4xl leading-none font-light">+</span>
                            <span class="font-poppins text-xs font-bold">Foto Principal</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL CROPPER: FOTO PRINCIPAL DO ANIMAL -->
            <div id="modal-cropper-animal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
                <div class="bg-branco dark:bg-fundoChat-escuro rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl border border-cinzaMarrom/20 dark:border-branco/10">
                    <h3 class="font-shantell text-xl font-bold mb-1 text-text-dark dark:text-branco">Ajustar Foto</h3>
                    <p class="text-xs text-text-muted dark:text-branco/60 mb-4 text-center">Arraste e use o zoom para centralizar.</p>

                    <div class="w-full h-80 bg-surface dark:bg-preto2 rounded-2xl overflow-hidden mb-4 flex items-center justify-center border border-cinzaMarrom/30">
                        <img id="imagem-para-cortar-animal" src="" alt="Cortar" class="max-w-full max-h-full">
                    </div>

                    <div class="flex gap-3 w-full">
                        <button type="button" onclick="fecharModalCropperAnimal()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-branco py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
                        <button type="button" onclick="salvarRecorteAnimal()" class="flex-1 bg-rosaAlerta text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">Aplicar</button>
                    </div>
                </div>
            </div>

            <div>
                <label for="nome" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Nome do animal <span class="text-rosaAlerta">*</span></label>
                <input type="text" id="nome" name="nome" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" value="<?= htmlspecialchars($nome) ?>" placeholder="Ex: Thor" maxlength="120">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="dt_nasc" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Data de nascimento <span class="text-rosaAlerta">*</span></label>
                    <input type="date" id="dt_nasc" name="dt_nasc" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors" value="<?= htmlspecialchars($dtNasc ?? '') ?>">
                </div>
                <div>
                    <label for="sexo" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Sexo <span class="text-rosaAlerta">*</span></label>
                    <select id="sexo" name="sexo" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors">
                        <option value="" class="dark:bg-preto2">Escolha</option>
                        <option value="macho" class="dark:bg-preto2" <?= $sexo === 'macho' ? 'selected' : '' ?>>Macho</option>
                        <option value="femea" class="dark:bg-preto2" <?= $sexo === 'femea' ? 'selected' : '' ?>>Fêmea</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="porte" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Porte <span class="text-rosaAlerta">*</span></label>
                    <select id="porte" name="porte" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors">
                        <option value="" class="dark:bg-preto2">Escolha</option>
                        <option value="pequeno" class="dark:bg-preto2" <?= $porte === 'pequeno' ? 'selected' : '' ?>>Pequeno</option>
                        <option value="medio" class="dark:bg-preto2" <?= $porte === 'medio' ? 'selected' : '' ?>>Médio</option>
                        <option value="grande" class="dark:bg-preto2" <?= $porte === 'grande' ? 'selected' : '' ?>>Grande</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Status <span class="text-rosaAlerta">*</span></label>
                    <select id="status" name="status" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none transition-colors">
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
                <textarea id="descricao" name="descricao" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl dark:bg-preto2 dark:text-branco focus:border-rosaAlerta outline-none resize-y transition-colors" rows="3" placeholder="História e detalhes..."><?= htmlspecialchars($descricao ?? '') ?></textarea>
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

<script>
    let cropperAnimal = null;

    function iniciarCropperAnimal(event) {
        const fileInput = event.target;
        if (!fileInput.files || fileInput.files.length === 0) return;

        if (typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarTamanhoArquivo(fileInput, 5)) {
            if (typeof mostrarModalFeedback === 'function') {
                mostrarModalFeedback('erro', 'A imagem é muito grande. Escolha uma de até 5MB.');
            } else {
                alert('A imagem é muito grande. Escolha uma de até 5MB.');
            }
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagem-para-cortar-animal').src = e.target.result;
            document.getElementById('modal-cropper-animal').classList.remove('hidden');

            if (cropperAnimal) cropperAnimal.destroy();
            cropperAnimal = new Cropper(document.getElementById('imagem-para-cortar-animal'), {
                aspectRatio: 4 / 5,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1
            });
        };
        reader.readAsDataURL(fileInput.files[0]);
    }

    function fecharModalCropperAnimal() {
        document.getElementById('modal-cropper-animal').classList.add('hidden');
        if (cropperAnimal) {
            cropperAnimal.destroy();
            cropperAnimal = null;
        }
        document.getElementById('input-foto-original').value = '';
    }

    function salvarRecorteAnimal() {
        if (!cropperAnimal) return;

        const base64String = cropperAnimal.getCroppedCanvas({
            width: 800,
            height: 1000
        }).toDataURL('image/png');

        const preview = document.getElementById('preview-foto-principal');
        preview.src = base64String;
        preview.classList.remove('hidden');
        document.getElementById('placeholder-foto-principal').classList.add('hidden');
        document.getElementById('foto_cortada_base64').value = base64String;

        fecharModalCropperAnimal();
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>