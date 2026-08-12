<?php
require_once __DIR__ . '/../templates/header.php';

$especies = $_SESSION['especies'] ?? [];
$old = $_SESSION['old'] ?? [];
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
            </div>
        <?php endif; ?>

        <form action="<?= URL_BASE ?>/animal" method="POST" class="w-full max-w-md flex flex-col gap-6">

            <div>
                <label for="nome" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Nome do animal <span class="text-rosaAlerta">*</span></label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Thor" maxlength="120" value="<?= htmlspecialchars($old['nome'] ?? '') ?>" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="protetor_id" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Protetor ID <span class="text-rosaAlerta">*</span></label>
                    <input type="number" id="protetor_id" name="protetor_id" placeholder="ID" value="<?= htmlspecialchars($old['protetor_id'] ?? '') ?>" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- <div class="form-group">
                    <label for="raca_id" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Raça:</label>
                    <select name="raca_id" id="raca_id" class="form-control w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="">Selecione uma Raça</option>
                        <?php //if (!empty($_SESSION['racas'])): 
                        ?>
                            <?php //foreach ($_SESSION['racas'] as $raca): 
                            ?>
                                <option value="<?php // $raca->getId(); 
                                                ?>" <?php // ($old['raca_id'] ?? '') == $raca->getId() ? 'selected' : '' 
                                                    ?>>
                                    <?php //htmlspecialchars($raca->getNome()); 
                                    ?>
                                </option>
                            <?php //endforeach; 
                            ?>
                        <?php //endif; 
                        ?>
                    </select>
                </div> -->
                <!-- Select de Espécie (Filtro visual, não precisa do attribute 'name') -->
                <div class="col-md-6 mb-3">
                    <label for="especie_id" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Espécie</label>
                    <select id="especie_id" class="form-control w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors">
                        <option value="">Selecione uma espécie</option>
                        <?php foreach ($especies as $especie): ?>
                            <option value="<?= $especie->getId() ?>"><?= htmlspecialchars($especie->getNome()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select de Raça (Enviado no formulário) -->
                <div class="col-md-6 mb-3">
                    <label for="raca_id" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Raça</label>
                    <select id="raca_id" name="raca_id" class="form-control w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none transition-colors" disabled>
                        <option value="">Selecione primeiro a espécie</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="dt_nasc" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Data de nascimento aproximada <span class="text-rosaAlerta">*</span></label>
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
                <textarea id="historico_saude" name="historico_saude" rows="3" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none resize-y transition-colors" placeholder="Tomou alguma vacina? Tem doença crônica? Use esse espaço para falar sobre a saúde do animal..."><?= htmlspecialchars($old['historico_saude'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="descricao" class="block font-poppins font-bold text-sm text-text-dark dark:text-branco/90 mb-2">Sobre/Mais</label>
                <textarea id="descricao" name="descricao" rows="4" class="w-full p-3 border-2 border-text-dark dark:border-branco/30 rounded-xl bg-transparent dark:bg-preto2 dark:text-branco focus:border-rosaAlerta dark:focus:border-rosaAlerta outline-none resize-y transition-colors" placeholder="Como ele chegou até você? Qual sua história? Use esse espaço para mais detalhes específicos..."><?= htmlspecialchars($old['descricao'] ?? '') ?></textarea>
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
    document.getElementById('especie_id').addEventListener('change', async function() {
        const especieId = this.value;
        const racaSelect = document.getElementById('raca_id');

        console.log('ID da espécie selecionada:', especieId);

        racaSelect.innerHTML = '<option value="">Carregando raças...</option>';
        racaSelect.disabled = true;

        if (!especieId) {
            racaSelect.innerHTML = '<option value="">Selecione primeiro a espécie</option>';
            return;
        }

        try {
            const url = `<?= URL_BASE ?>/raca/json?especie_id=${especieId}`;
            console.log('Requisitando:', url);

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const text = await response.text();
            console.log('Status HTTP:', response.status);
            console.log('Resposta bruta do servidor:', text);

            const result = JSON.parse(text);

            // Identifica a lista de raças independentemente da estrutura de retorno usada no Controller
            const listaRacas = result.data || result.dados || result.racas || (Array.isArray(result) ? result : []);

            if (listaRacas.length > 0) {
                racaSelect.innerHTML = '<option value="">Selecione uma raça</option>';
                listaRacas.forEach(raca => {
                    const option = document.createElement('option');
                    option.value = raca.raca_id || raca.id;
                    option.textContent = raca.nome;
                    racaSelect.appendChild(option);
                });
                racaSelect.disabled = false;
            } else {
                racaSelect.innerHTML = '<option value="">Nenhuma raça encontrada</option>';
            }
        } catch (error) {
            console.error('Erro detalhado no Fetch:', error);
            racaSelect.innerHTML = '<option value="">Erro ao carregar raças</option>';
        }
    });
</script>