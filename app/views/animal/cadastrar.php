<?php
require_once __DIR__ . '/../templates/header.php';

$especies = $especies ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>

<main class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">

    <div class="w-full max-w-3xl flex flex-col items-center py-10 px-8 relative bg-branco dark:bg-fundoChat-escuro shadow-[0_8px_20px_rgba(0,0,0,0.12)] rounded-[2.5rem] border border-cinzaMarrom/20 dark:border-branco/10 transition-colors duration-300">

        <h1 class="font-shantell text-[32px] font-bold text-text-dark dark:text-branco text-center mb-10 transition-colors duration-300">
            Cadastrar Novo Animal
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

        <form action="<?= URL_BASE ?>/animal" method="POST" enctype="multipart/form-data" class="w-full max-w-md flex flex-col gap-6">

            <div>
                <label class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2 text-center">Foto Principal</label>
                <input type="hidden" name="foto_cortada" id="foto_cortada_base64" value="<?= htmlspecialchars($old['foto_cortada'] ?? '') ?>">
                <input type="file" id="input-foto-original" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden" onchange="iniciarCropperAnimal(event)">

                <div class="flex justify-center">
                    <div id="container-foto-principal" onclick="document.getElementById('input-foto-original').click()"
                        class="relative w-40 h-52 sm:w-44 sm:h-56 rounded-2xl border-2 border-dashed border-text-dark/40 dark:border-branco/30 bg-transparent dark:bg-preto2 flex flex-col items-center justify-center gap-2 cursor-pointer overflow-hidden hover:border-rosaAlerta dark:hover:border-rosaAlerta transition-colors">
                        <img id="preview-foto-principal" src="" alt="Prévia da foto principal" class="hidden absolute inset-0 w-full h-full object-cover">
                        <div id="placeholder-foto-principal" class="flex flex-col items-center justify-center gap-1 text-text-dark dark:text-branco/70 pointer-events-none">
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
                <input type="text" id="nome" name="nome" placeholder="Ex: Thor" maxlength="120" value="<?= htmlspecialchars($old['nome'] ?? '') ?>" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Select de Espécie -->
                <div class="col-md-6 mb-3">
                    <label for="especie_id" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Espécie <span class="text-rosaAlerta">*</span></label>
                    <select id="especie_id" name="especie_id" class="form-control w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="">Selecione uma espécie</option>
                        <?php foreach ($especies as $especie): ?>
                            <?php 
                                $idEsp = is_array($especie) ? ($especie['especie_id'] ?? $especie['id']) : $especie->getId();
                                $nomeEsp = is_array($especie) ? $especie['nome'] : $especie->getNome();
                            ?>
                            <option value="<?= $idEsp ?>" <?= (string) ($old['especie_id'] ?? '') === (string) $idEsp ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nomeEsp) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select de Raça -->
                <div class="col-md-6 mb-3">
                    <label for="raca_id" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Raça <span class="text-rosaAlerta">*</span></label>
                    <select id="raca_id" name="raca_id" data-old-value="<?= htmlspecialchars((string) ($old['raca_id'] ?? '')) ?>" class="form-control w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors" disabled>
                        <option value="">Selecione primeiro a espécie</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="dt_nasc" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Data de nascimento aproximada</label>
                <input type="date" id="dt_nasc" name="dt_nasc" value="<?= htmlspecialchars($old['dt_nasc'] ?? '') ?>" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="sexo" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Sexo <span class="text-rosaAlerta">*</span></label>
                    <select id="sexo" name="sexo" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="" class="dark:bg-preto2">Escolha</option>
                        <option value="macho" class="dark:bg-preto2" <?= ($old['sexo'] ?? '') === 'macho' ? 'selected' : '' ?>>Macho</option>
                        <option value="femea" class="dark:bg-preto2" <?= ($old['sexo'] ?? '') === 'femea' ? 'selected' : '' ?>>Fêmea</option>
                    </select>
                </div>
                <div>
                    <label for="porte" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Porte <span class="text-rosaAlerta">*</span></label>
                    <select id="porte" name="porte" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="" class="dark:bg-preto2">Escolha</option>
                        <option value="pequeno" class="dark:bg-preto2" <?= ($old['porte'] ?? '') === 'pequeno' ? 'selected' : '' ?>>Pequeno</option>
                        <option value="medio" class="dark:bg-preto2" <?= ($old['porte'] ?? '') === 'medio' ? 'selected' : '' ?>>Médio</option>
                        <option value="grande" class="dark:bg-preto2" <?= ($old['porte'] ?? '') === 'grande' ? 'selected' : '' ?>>Grande</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="status" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Status <span class="text-rosaAlerta">*</span></label>
                    <select id="status" name="status" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="disponivel" class="dark:bg-preto2" <?= ($old['status'] ?? 'disponivel') === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                        <option value="em_analise" class="dark:bg-preto2" <?= ($old['status'] ?? '') === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                        <option value="adotado" class="dark:bg-preto2" <?= ($old['status'] ?? '') === 'adotado' ? 'selected' : '' ?>>Adotado</option>
                        <option value="desativado" class="dark:bg-preto2" <?= ($old['status'] ?? '') === 'desativado' ? 'selected' : '' ?>>Desativado</option>
                    </select>
                </div>
                <div>
                    <label for="comportamento" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Comportamento</label>
                    <select id="comportamento" name="comportamento" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="" class="dark:bg-preto2">Escolha (Opcional)</option>
                        <option value="calmo" class="dark:bg-preto2" <?= ($old['comportamento'] ?? '') === 'calmo' ? 'selected' : '' ?>>Calmo</option>
                        <option value="ativo" class="dark:bg-preto2" <?= ($old['comportamento'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="docil" class="dark:bg-preto2" <?= ($old['comportamento'] ?? '') === 'docil' ? 'selected' : '' ?>>Dócil</option>
                        <option value="arisco" class="dark:bg-preto2" <?= ($old['comportamento'] ?? '') === 'arisco' ? 'selected' : '' ?>>Arisco</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="historico_saude" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Saúde</label>
                <textarea id="historico_saude" name="historico_saude" rows="3" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none resize-y transition-colors" placeholder="Tomou alguma vacina? Tem doença crônica?"><?= htmlspecialchars($old['historico_saude'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="descricao" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Sobre/Mais <span class="text-rosaAlerta">*</span></label>
                <textarea id="descricao" name="descricao" rows="4" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none resize-y transition-colors" placeholder="História e detalhes..."><?= htmlspecialchars($old['descricao'] ?? '') ?></textarea>
            </div>

            <div class="flex items-center gap-8 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="vacinado" value="1" <?= !empty($old['vacinado']) ? 'checked' : '' ?> class="w-5 h-5 accent-rosaAlerta bg-transparent border-2 border-text-dark dark:border-branco/30 rounded focus:ring-roxinhoFofo focus:ring-2">
                    <span class="font-poppins text-sm font-bold text-text-dark dark:text-branco/90">Vacinado</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="castrado" value="1" <?= !empty($old['castrado']) ? 'checked' : '' ?> class="w-5 h-5 accent-rosaAlerta bg-transparent border-2 border-text-dark dark:border-branco/30 rounded focus:ring-roxinhoFofo focus:ring-2">
                    <span class="font-poppins text-sm font-bold text-text-dark dark:text-branco/90">Castrado</span>
                </label>
            </div>

            <div class="flex flex-col items-center gap-4 mt-8">
                <button type="submit" class="w-full max-w-[200px] py-4 bg-rosaAlerta hover:bg-rosa-2 text-white dark:hover:text-text-dark font-bold rounded-full shadow-md transition-all duration-300 hover:-translate-y-1">
                    Finalizar
                </button>
                <a href="<?= URL_BASE ?>/animal" class="font-poppins text-sm font-medium text-text-muted dark:text-branco/60 hover:text-rosaAlerta dark:hover:text-branco transition-colors underline">
                    Voltar
                </a>
            </div>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<script>
    async function carregarRacas(especieId, racaIdParaSelecionar) {
        const racaSelect = document.getElementById('raca_id');

        racaSelect.innerHTML = '<option value="">Carregando raças...</option>';
        racaSelect.disabled = true;

        if (!especieId) {
            racaSelect.innerHTML = '<option value="">Selecione primeiro a espécie</option>';
            return;
        }

        try {
            const url = `<?= URL_BASE ?>/raca/json?especie_id=${especieId}`;
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

            const textResponse = await response.text();
            let result;
            try {
                result = JSON.parse(textResponse);
            } catch (e) {
                console.error("Resposta não é um JSON válido:", textResponse);
                throw new Error("O servidor retornou uma resposta inválida.");
            }

            const listaRacas = result.dados || result.data || result.racas || (Array.isArray(result) ? result : []);

            if (listaRacas.length > 0) {
                racaSelect.innerHTML = '<option value="">Selecione uma raça</option>';
                listaRacas.forEach(raca => {
                    const option = document.createElement('option');
                    option.value = raca.raca_id || raca.id;
                    option.textContent = raca.nome;
                    racaSelect.appendChild(option);
                });
                racaSelect.disabled = false;

                if (racaIdParaSelecionar) {
                    racaSelect.value = String(racaIdParaSelecionar);
                }
            } else {
                racaSelect.innerHTML = '<option value="">Nenhuma raça encontrada</option>';
            }
        } catch (error) {
            console.error("Erro no fetch de raças:", error);
            racaSelect.innerHTML = '<option value="">Erro ao carregar raças</option>';
        }
    }

    document.getElementById('especie_id').addEventListener('change', function() {
        carregarRacas(this.value, null);
    });

    document.addEventListener('DOMContentLoaded', function() {
        const especieSelect = document.getElementById('especie_id');
        const racaSelect = document.getElementById('raca_id');
        const racaIdAntigo = racaSelect.dataset.oldValue;

        if (especieSelect.value && racaIdAntigo) {
            carregarRacas(especieSelect.value, racaIdAntigo);
        }

        const fotoAntiga = document.getElementById('foto_cortada_base64').value;
        if (fotoAntiga) {
            const preview = document.getElementById('preview-foto-principal');
            preview.src = fotoAntiga;
            preview.classList.remove('hidden');
            document.getElementById('placeholder-foto-principal').classList.add('hidden');
        }
    });

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