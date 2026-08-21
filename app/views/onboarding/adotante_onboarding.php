<?php
require_once __DIR__ . '/../templates/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<!-- min-h + flex centraliza verticalmente a etapa visível do wizard (só uma fica sem "hidden"
     por vez, então o grupo inteiro — progresso, seta de voltar e a etapa atual — centraliza junto) -->
<form id="form-onboarding-adotante" action="<?= URL_BASE ?>/onboarding/salvar-adotante" method="POST" enctype="multipart/form-data" class="max-w-md mx-auto p-4 text-text-dark min-h-[80vh] flex flex-col justify-center">

    <input type="hidden" name="foto_perfil_cortada" id="foto_perfil_cortada">

    <!-- BARRA DE PROGRESSO GLOBAL (6 ETAPAS) -->
    <!-- Classe base precisa ser bg-gray-300: OnboardingManager.atualizarVisualEtapas() (onboarding.js)
         alterna especificamente entre bg-gray-300 (pendente) e bg-green-500 (concluída/atual). -->
    <div class="flex justify-center gap-2 mb-6">
        <div id="progresso-1" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-2" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-3" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-4" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-5" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
        <div id="progresso-6" class="h-2 w-8 rounded-full bg-gray-300 dark:bg-preto3 transition-colors duration-300"></div>
    </div>

    <!-- BOTÃO VOLTAR -->
    <button type="button" id="btn-voltar-global" onclick="OnboardingManager.voltarEtapa()" class="mb-4 text-xl font-bold cursor-pointer transition hover:opacity-75 text-primary" title="Voltar">
        <img src="<?= URL_BASE ?>/assets/icons/geral/seta-voltar.svg" alt="Voltar" class="w-8 h-8">
    </button>

    <!-- ETAPA 1: Como podemos te chamar? -->
    <div class="etapa-form" id="etapa-1">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark dark:text-white">Como podemos te chamar?</h1>

            <div class="flex flex-col items-center mb-6 mt-4">
                <div class="relative cursor-pointer group" onclick="document.getElementById('input-arquivo-perfil').click()">
                    <div class="w-28 h-28 rounded-full border-4 border-rosa-3 overflow-hidden bg-surface dark:bg-preto2 flex items-center justify-center shadow">
                        <img id="foto-placeholder-adotante" src="<?= URL_BASE ?>/assets/img/perfil-placeholder.png" alt="Adicionar foto de perfil" class="w-full h-full object-cover opacity-60">
                        <img id="preview-foto-perfil" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="absolute bottom-0 right-0 bg-surface dark:bg-preto1 p-1.5 rounded-full shadow border border-rosa-2 group-hover:bg-rosa-1">
                        <img src="<?= URL_BASE ?>/assets/icons/perfil/editar-perfil.svg" alt="Editar foto" class="w-4 h-4">
                    </div>
                </div>
                <span class="text-xs text-text-muted mt-2">Toque para escolher e ajustar sua foto</span>
                <input type="file" id="input-arquivo-perfil" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropperPerfil(event)">
            </div>

            <div class="mb-4 text-left">
                <label for="nome_usuario" class="label-padrao">Seu Nome *</label>
                <input type="text" name="nome_usuario" id="nome_usuario" placeholder="Digite seu nome aqui" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
            </div>

            <div class="mb-4 text-left">
                <label for="descricao" class="label-padrao">Fale um pouco sobre você (Opcional)</label>
                <textarea name="descricao" id="descricao" rows="3" placeholder="Por que você quer adotar um pet?" class="input-padrao bg-branco dark:bg-preto2 dark:text-white"></textarea>
            </div>

            <div class="space-y-4 mb-6 text-left">
                <div>
                    <label for="dt_nasc" class="label-padrao">Data de Nascimento *</label>
                    <input type="date" name="dt_nasc" id="dt_nasc" max="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                </div>

                <div>
                    <label for="telefone" class="label-padrao">Telefone (Opcional)</label>
                    <input type="tel" name="telefone" id="telefone" placeholder="(00) 00000-0000" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark dark:text-white">Ir para Preferências</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 2: Convivência -->
    <div class="etapa-form hidden" id="etapa-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark dark:text-white">Convivência</h1>
            <p class="text-sm text-text-muted">Conte-nos um pouco mais sobre sua rotina com outros pets ou família.</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label class="label-padrao">Possui filhos ou crianças em casa? *</label>
                <select name="possui_criancas" id="possui_criancas" class="input-padrao bg-surface dark:bg-preto2 text-sm appearance-none bg-no-repeat pr-10 bg-[right_0.75rem_center] bg-[length:1.1rem] bg-[url('data:image/svg+xml,%3Csvg_xmlns=%27http://www.w3.org/2000/svg%27_viewBox=%270_0_24_24%27_fill=%27none%27_stroke=%27%23716C93%27_stroke-width=%272.5%27_stroke-linecap=%27round%27_stroke-linejoin=%27round%27%3E%3Cpath_d=%27m6_9_6_6_6-6%27/%3E%3C/svg%3E')]">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>

            <div>
                <label class="label-padrao">Possui outros pets? *</label>
                <select name="possui_outros_pets" id="possui_outros_pets" class="input-padrao bg-surface dark:bg-preto2 text-sm appearance-none bg-no-repeat pr-10 bg-[right_0.75rem_center] bg-[length:1.1rem] bg-[url('data:image/svg+xml,%3Csvg_xmlns=%27http://www.w3.org/2000/svg%27_viewBox=%270_0_24_24%27_fill=%27none%27_stroke=%27%23716C93%27_stroke-width=%272.5%27_stroke-linecap=%27round%27_stroke-linejoin=%27round%27%3E%3Cpath_d=%27m6_9_6_6_6-6%27/%3E%3C/svg%3E')]">
                    <option value="" disabled selected>Escolha</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark dark:text-white">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 3: Moradia -->
    <div class="etapa-form hidden" id="etapa-3">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark dark:text-white">Moradia</h1>
            <p class="text-sm text-text-muted">Complete esse formulário para acharmos o pet Perfeito para você!</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <label for="tipo_moradia" class="label-padrao">Tipo de moradia *</label>
                <select name="tipo_moradia" id="tipo_moradia" class="input-padrao bg-surface dark:bg-preto2 text-sm appearance-none bg-no-repeat pr-10 bg-[right_0.75rem_center] bg-[length:1.1rem] bg-[url('data:image/svg+xml,%3Csvg_xmlns=%27http://www.w3.org/2000/svg%27_viewBox=%270_0_24_24%27_fill=%27none%27_stroke=%27%23716C93%27_stroke-width=%272.5%27_stroke-linecap=%27round%27_stroke-linejoin=%27round%27%3E%3Cpath_d=%27m6_9_6_6_6-6%27/%3E%3C/svg%3E')]">
                    <option value="" disabled selected>Escolha</option>
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                    <option value="chacara">Chácara/Sítio</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div>
                <label for="espaco_interior" class="label-padrao">Espaço interior *</label>
                <select name="espaco_interior" id="espaco_interior" class="input-padrao bg-surface dark:bg-preto2 text-sm appearance-none bg-no-repeat pr-10 bg-[right_0.75rem_center] bg-[length:1.1rem] bg-[url('data:image/svg+xml,%3Csvg_xmlns=%27http://www.w3.org/2000/svg%27_viewBox=%270_0_24_24%27_fill=%27none%27_stroke=%27%23716C93%27_stroke-width=%272.5%27_stroke-linecap=%27round%27_stroke-linejoin=%27round%27%3E%3Cpath_d=%27m6_9_6_6_6-6%27/%3E%3C/svg%3E')]">
                    <option value="" disabled selected>Escolha</option>
                    <option value="pequeno">Pequeno</option>
                    <option value="medio">Médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>
            <div>
                <label for="espaco_externo" class="label-padrao">Espaço externo *</label>
                <select name="espaco_externo" id="espaco_externo" class="input-padrao bg-surface dark:bg-preto2 text-sm appearance-none bg-no-repeat pr-10 bg-[right_0.75rem_center] bg-[length:1.1rem] bg-[url('data:image/svg+xml,%3Csvg_xmlns=%27http://www.w3.org/2000/svg%27_viewBox=%270_0_24_24%27_fill=%27none%27_stroke=%27%23716C93%27_stroke-width=%272.5%27_stroke-linecap=%27round%27_stroke-linejoin=%27round%27%3E%3Cpath_d=%27m6_9_6_6_6-6%27/%3E%3C/svg%3E')]">
                    <option value="" disabled selected>Escolha</option>
                    <option value="nenhum">Não possui quintal</option>
                    <option value="pequeno">Quintal pequeno</option>
                    <option value="medio">Quintal médio</option>
                    <option value="grande">Grande</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark dark:text-white">Clique para avançar</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 4: Região e Localização -->
    <div class="etapa-form hidden" id="etapa-4">
        <div class="text-center mb-6">
            <div class="relative w-40 h-40 sm:w-56 sm:h-56 mx-auto mb-5">
                <img src="<?= URL_BASE ?>/assets/img/mascote-localizacao.png" alt="" class="absolute inset-0 w-full h-full object-contain scale-150 pointer-events-none" onerror="this.style.display='none';">
            </div>
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark dark:text-white">Selecione a sua localização</h1>
            <p class="text-sm text-text-muted">Selecione a sua localização para podermos encontrar os pets mais próximos de você e dar início a um novo match!</p>
        </div>

        <div class="mb-4 relative text-left">
            <label for="input-busca-bairro" class="label-padrao">Pesquise seu Bairro / Região *</label>
            <input type="text" name="busca_bairro_texto" id="input-busca-bairro" list="lista-regioes" placeholder="Digite o nome do seu bairro..." autocomplete="off" oninput="OnboardingManager.sincronizarRegiaoId()" class="input-padrao input-com-seta bg-branco dark:bg-preto2 dark:text-white">

            <datalist id="lista-regioes">
                <?php if (!empty($regioes)): ?>
                    <?php foreach ($regioes as $regiao): ?>
                        <?php
                        $regId = is_array($regiao) ? $regiao['regiao_id'] : $regiao->getRegiaoId();
                        $regNome = is_array($regiao) ? $regiao['nome_regiao'] : $regiao->getNomeRegiao();
                        ?>
                        <option data-id="<?= $regId ?>" value="<?= e($regNome) ?>"></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </datalist>

            <input type="hidden" name="regiao_id" id="regiao_id_hidden">
            <p id="erro-bairro-invalido" class="text-xs text-erro mt-1 hidden">Selecione um bairro válido da lista fornecida.</p>
        </div>

        <div class="mb-4 text-left">
            <label for="obs_casa" class="label-padrao">Logradouro e Complemento *</label>
            <input type="text" name="obs_casa" id="obs_casa" placeholder="Ex: Avenida Brasil, Apto 42..." class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
        </div>

        <div class="mb-6 text-left">
            <label for="numero" class="label-padrao">Número da Residência *</label>
            <input type="text" name="numero" id="numero" placeholder="Ex: 123, S/N" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark dark:text-white">Ir para Preferências</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 5: Preferências -->
    <div class="etapa-form hidden" id="etapa-5">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark dark:text-white">Suas Preferências</h1>
            <p class="text-sm text-text-muted mb-6">Selecione suas preferências para montarmos o seu feed perfeito. Você pode escolher mais de uma opção!</p>
        </div>

        <div class="space-y-4 mb-6 text-left">
            <div>
                <span class="font-bold block mb-2 text-text-dark dark:text-white">Espécie:</span>
                <div id="container-especies-padrao" class="space-y-2">
                    <?php
                    $especie1 = null;
                    $especie2 = null;
                    $outrasEspecies = [];

                    if (!empty($especies)) {
                        foreach ($especies as $esp) {
                            $id = is_array($esp) ? $esp['especie_id'] : $esp->getEspecieId();
                            $nome = is_array($esp) ? $esp['nome'] : $esp->getNome();

                            if ((int)$id === 1) {
                                $especie1 = ['id' => $id, 'nome' => $nome];
                            } elseif ((int)$id === 2) {
                                $especie2 = ['id' => $id, 'nome' => $nome];
                            } else {
                                $outrasEspecies[] = ['id' => $id, 'nome' => $nome];
                            }
                        }
                    }
                    ?>

                    <div id="wrapper-especie-1">
                        <?php if ($especie1): ?>
                            <label id="label-especie-<?= $especie1['id'] ?>" class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white">
                                <input type="checkbox" name="preferencias_especie[]" value="<?= $especie1['id'] ?>" class="check-especie text-roxinhoFofo focus:ring-roxinhoFofo rounded"> <?= e($especie1['nome']) ?>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div id="wrapper-especie-2">
                        <?php if ($especie2): ?>
                            <label id="label-especie-<?= $especie2['id'] ?>" class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white">
                                <input type="checkbox" name="preferencias_especie[]" value="<?= $especie2['id'] ?>" class="check-especie text-roxinhoFofo focus:ring-roxinhoFofo rounded"> <?= e($especie2['nome']) ?>
                            </label>
                        <?php endif; ?>
                    </div>

                    <label id="label-opcao-outros" class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white <?= empty($outrasEspecies) ? 'hidden' : '' ?>">
                        <input type="checkbox" id="checkbox-outras-especies" onchange="toggleOutrasEspecies()" value="outros" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Outros
                    </label>
                </div>

                <div id="container-outras-especies" class="hidden mt-3 p-3 border border-rosa-2 dark:border-preto3 rounded-lg bg-surface dark:bg-preto2">
                    <label class="block font-medium mb-2 text-sm text-text-dark dark:text-white">Selecione outras espécies desejadas:</label>
                    <div id="lista-outras-especies-dinamica" class="space-y-2 max-h-40 overflow-y-auto pl-1">
                        <?php foreach ($outrasEspecies as $esp): ?>
                            <label id="label-especie-<?= $esp['id'] ?>" class="flex items-center gap-2 text-sm cursor-pointer text-text-dark dark:text-white">
                                <input type="checkbox" name="preferencias_especie[]" value="<?= $esp['id'] ?>" class="check-outras check-especie text-roxinhoFofo focus:ring-roxinhoFofo rounded">
                                <?= e($esp['nome']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <span class="font-bold block mb-2 text-text-dark dark:text-white">Porte:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white text-xs">
                        <input type="checkbox" name="preferencias_porte[]" value="pequeno" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Pequeno
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white text-xs">
                        <input type="checkbox" name="preferencias_porte[]" value="medio" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Médio
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white text-xs">
                        <input type="checkbox" name="preferencias_porte[]" value="grande" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Grande
                    </label>
                </div>
            </div>
            <div class="mt-4">
                <span class="font-bold block mb-2 text-text-dark dark:text-white">Sexo:</span>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white text-xs">
                        <input type="checkbox" name="preferencias_sexo[]" value="femea" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Fêmea
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white text-xs">
                        <input type="checkbox" name="preferencias_sexo[]" value="macho" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Macho
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark dark:text-white">Ler os Termos</span>
            <button type="button" onclick="proximaEtapa()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-2 transition">&rarr;</button>
        </div>
    </div>

    <!-- ETAPA 6: Termos -->
    <div class="etapa-form hidden" id="etapa-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-2 font-shantell text-text-dark dark:text-white">Termos de Responsabilidade</h1>
            <p class="text-sm text-text-muted">Por favor, leia atentamente as regras da nossa plataforma antes de finalizar.</p>
        </div>

        <div class="bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-xl p-5 mb-6 shadow-sm text-left max-h-60 overflow-y-auto text-sm text-text-dark dark:text-white space-y-3">
            <p><strong>1. Maioridade Civil:</strong> Declaro ser maior de 18 (dezoito) anos e ter plena capacidade civil para utilizar o sistema.</p>
            <p><strong>2. Proteção de Dados (LGPD):</strong> Autorizo a coleta e o armazenamento dos meus dados pessoais necessários para a criação do perfil e intermediação de adoções.</p>
            <p><strong>3. Responsabilidade da Plataforma:</strong> Compreendo que o CãoNectados atua exclusivamente como um <strong>intermediador digital</strong> (vitrine) para facilitar o encontro entre animais e adotantes.</p>
            <p><strong>4. Isenção Legal:</strong> A plataforma <strong>não possui</strong> qualquer responsabilidade legal, logística, veterinária ou financeira sobre o processo de adoção, sendo esta responsabilidade inteiramente do adotante e do protetor/ONG envolvidos.</p>
        </div>

        <div class="mb-6 text-left">
            <label class="flex items-center gap-3 cursor-pointer p-4 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-xl hover:bg-rosa-1/20 transition">
                <input type="checkbox" name="aceite_termos" id="aceite_termos" class="w-6 h-6 text-rosaAlerta rounded focus:ring-rosaAlerta">
                <span class="text-sm text-text-dark dark:text-white font-medium">
                    Li, compreendi e concordo com os Termos de Responsabilidade.
                </span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-8">
            <span class="font-medium text-text-dark dark:text-white">Ir para o Feed</span>
            <button type="button" onclick="submeterFormularioAdotante()" class="w-12 h-12 rounded-full bg-rosaAlerta text-xl font-bold flex items-center justify-center text-white hover:bg-rosa-3 transition">&rarr;</button>
        </div>
    </div>
</form>

<!-- MODAL CROPPER -->
<div id="modal-cropper" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-surface dark:bg-preto1 rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl border border-rosa-3">
        <h3 class="font-shantell text-xl font-bold mb-1 text-text-dark dark:text-white">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Arraste e use o zoom para centralizar.</p>

        <div class="w-full h-64 bg-surface dark:bg-preto2 rounded-2xl overflow-hidden mb-4 flex items-center justify-center border border-cinzaMarrom/30">
            <img id="imagem-para-cortar" src="" alt="Cortar" class="max-w-full max-h-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalCropper()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
            <button type="button" onclick="salvarRecorte()" class="flex-1 bg-rosaAlerta text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">Aplicar</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="<?= e(asset('assets/js/onboarding.js')) ?>"></script>
<script src="<?= e(asset('assets/js/autosave.js')) ?>"></script>

<script>
    let cropper = null;

    function iniciarCropperPerfil(event) {
        const fileInput = event.target;
        if (fileInput.files && fileInput.files.length > 0) {
            if (typeof CaonectadosValidator !== 'undefined' && !CaonectadosValidator.validarTamanhoArquivo(fileInput, 5)) {
                mostrarModalFeedback('erro', 'A imagem é muito grande. Escolha uma de até 5MB.');
                fileInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagem-para-cortar').src = e.target.result;
                document.getElementById('modal-cropper').classList.remove('hidden');
                if (cropper) cropper.destroy();
                cropper = new Cropper(document.getElementById('imagem-para-cortar'), {
                    aspectRatio: 1 / 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8
                });
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    function fecharModalCropper() {
        document.getElementById('modal-cropper').classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        document.getElementById('input-arquivo-perfil').value = '';
    }

    function salvarRecorte() {
        if (!cropper) return;
        const base64String = cropper.getCroppedCanvas({
            width: 400,
            height: 400
        }).toDataURL('image/png');

        const preview = document.getElementById('preview-foto-perfil');
        const placeholder = document.getElementById('foto-placeholder-adotante');

        preview.src = base64String;
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');

        document.getElementById('foto_perfil_cortada').value = base64String;
        fecharModalCropper();
    }

    async function sincronizarEspeciesAtivasAjax() {
        try {
            const resp = await fetch("<?= URL_BASE ?>/onboarding/especies-ativas");
            const res = await resp.json();
            if (res.status === 'sucesso' && Array.isArray(res.dados)) {
                const especiesAtivas = res.dados;
                const marcadasAntes = Array.from(document.querySelectorAll('.check-especie:checked')).map(cb => String(cb.value));

                const esp1 = especiesAtivas.find(e => String(e.especie_id) === '1');
                const esp2 = especiesAtivas.find(e => String(e.especie_id) === '2');
                const outras = especiesAtivas.filter(e => String(e.especie_id) !== '1' && String(e.especie_id) !== '2');

                const wrapper1 = document.getElementById('wrapper-especie-1');
                if (wrapper1) {
                    if (esp1) {
                        const checked = marcadasAntes.includes('1') ? 'checked' : '';
                        wrapper1.innerHTML = `
                            <label id="label-especie-1" class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white">
                                <input type="checkbox" name="preferencias_especie[]" value="1" ${checked} class="check-especie text-roxinhoFofo focus:ring-roxinhoFofo rounded"> ${esp1.nome}
                            </label>
                        `;
                    } else {
                        wrapper1.innerHTML = '';
                    }
                }

                const wrapper2 = document.getElementById('wrapper-especie-2');
                if (wrapper2) {
                    if (esp2) {
                        const checked = marcadasAntes.includes('2') ? 'checked' : '';
                        wrapper2.innerHTML = `
                            <label id="label-especie-2" class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 transition text-text-dark dark:text-white">
                                <input type="checkbox" name="preferencias_especie[]" value="2" ${checked} class="check-especie text-roxinhoFofo focus:ring-roxinhoFofo rounded"> ${esp2.nome}
                            </label>
                        `;
                    } else {
                        wrapper2.innerHTML = '';
                    }
                }

                const labelOutros = document.getElementById('label-opcao-outros');
                const containerOutras = document.getElementById('container-outras-especies');
                const listaOutras = document.getElementById('lista-outras-especies-dinamica');
                const checkOutrosPrincipal = document.getElementById('checkbox-outras-especies');

                if (outras.length === 0) {
                    if (labelOutros) labelOutros.classList.add('hidden');
                    if (containerOutras) containerOutras.classList.add('hidden');
                    if (checkOutrosPrincipal) checkOutrosPrincipal.checked = false;
                } else {
                    if (labelOutros) labelOutros.classList.remove('hidden');
                    if (listaOutras) {
                        listaOutras.innerHTML = '';
                        let temMarcadaEmOutros = false;
                        outras.forEach(esp => {
                            const isChecked = marcadasAntes.includes(String(esp.especie_id));
                            if (isChecked) temMarcadaEmOutros = true;
                            listaOutras.innerHTML += `
                                <label id="label-especie-${esp.especie_id}" class="flex items-center gap-2 text-sm cursor-pointer text-text-dark dark:text-white">
                                    <input type="checkbox" name="preferencias_especie[]" value="${esp.especie_id}" ${isChecked ? 'checked' : ''} class="check-outras check-especie text-roxinhoFofo focus:ring-roxinhoFofo rounded">
                                    ${esp.nome}
                                </label>
                            `;
                        });

                        if (temMarcadaEmOutros) {
                            if (checkOutrosPrincipal) checkOutrosPrincipal.checked = true;
                            if (containerOutras) containerOutras.classList.remove('hidden');
                        }
                    }
                }

                return especiesAtivas.map(e => String(e.especie_id));
            }
        } catch (e) {
            console.error("Falha ao sincronizar espécies ativas via AJAX", e);
        }
        return [];
    }

    function proximaEtapa() {
        OnboardingManager.avancarEtapa(function(etapaAtual) {
            if (etapaAtual === 1) {
                const nomeInput = document.getElementById('nome_usuario');
                if (!CaonectadosValidator.validarNome(nomeInput.value)) {
                    mostrarModalFeedback('erro', "Por favor, informe seu nome corretamente (mínimo de 2 caracteres).");
                    nomeInput.focus();
                    return false;
                }

                const dataNasc = document.getElementById('dt_nasc');
                if (!dataNasc || !dataNasc.value) {
                    mostrarModalFeedback('erro', "Por favor, informe sua data de nascimento.");
                    dataNasc.focus();
                    return false;
                } else if (!CaonectadosValidator.validarMaioridade(dataNasc.value)) {
                    mostrarModalFeedback('erro', "É necessário ter pelo menos 18 anos para se cadastrar.");
                    dataNasc.focus();
                    return false;
                }

                const telefoneInput = document.getElementById('telefone');
                if (telefoneInput.value.trim() !== '' && !CaonectadosValidator.validarTelefone(telefoneInput.value)) {
                    mostrarModalFeedback('erro', "O telefone informado é inválido. Certifique-se de incluir o DDD.");
                    telefoneInput.focus();
                    return false;
                }
            }

            if (etapaAtual === 2) {
                const criancas = document.getElementById('possui_criancas').value;
                const pets = document.getElementById('possui_outros_pets').value;
                if (!criancas || !pets) {
                    mostrarModalFeedback('aviso', "Por favor, responda às perguntas sobre convivência.");
                    return false;
                }
            }

            if (etapaAtual === 3) {
                const moradia = document.getElementById('tipo_moradia').value;
                const interior = document.getElementById('espaco_interior').value;
                const externo = document.getElementById('espaco_externo').value;
                if (!moradia || !interior || !externo) {
                    mostrarModalFeedback('aviso', "Por favor, preencha todas as perguntas sobre a sua moradia.");
                    return false;
                }
            }

            if (etapaAtual === 4) {
                const inputHidden = document.getElementById('regiao_id_hidden');
                const msgErro = document.getElementById('erro-bairro-invalido');
                const obsCasa = document.getElementById('obs_casa');
                const numMorada = document.getElementById('numero');

                OnboardingManager.sincronizarRegiaoId();

                if (!inputHidden.value) {
                    if (msgErro) msgErro.classList.remove('hidden');
                    mostrarModalFeedback('aviso', "Por favor, selecione um bairro válido da lista.");
                    document.getElementById('input-busca-bairro').focus();
                    return false;
                }

                if (!obsCasa || obsCasa.value.trim() === '') {
                    mostrarModalFeedback('aviso', "Por favor, informe o seu logradouro.");
                    obsCasa.focus();
                    return false;
                }

                if (!numMorada || numMorada.value.trim() === '') {
                    mostrarModalFeedback('aviso', "Por favor, informe o número da sua residência.");
                    numMorada.focus();
                    return false;
                }

                sincronizarEspeciesAtivasAjax();
            }

            if (etapaAtual === 5) {
                const checkboxesMarcados = document.querySelectorAll('.check-especie:checked');
                if (checkboxesMarcados.length === 0) {
                    mostrarModalFeedback('aviso', "Selecione pelo menos uma preferência de espécie para montar o seu feed.");
                    return false;
                }
            }

            return true;
        });
    }

    function toggleOutrasEspecies() {
        const checkbox = document.getElementById('checkbox-outras-especies');
        const container = document.getElementById('container-outras-especies');
        const checkboxesOutras = container ? container.querySelectorAll('.check-outras') : [];

        if (checkbox && container) {
            if (checkbox.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                checkboxesOutras.forEach(cb => cb.checked = false);
            }
        }
    }

    async function submeterFormularioAdotante() {
        const aceiteTermos = document.getElementById('aceite_termos');
        if (!aceiteTermos.checked) {
            mostrarModalFeedback('aviso', "Você deve ler e concordar com os Termos de Responsabilidade para continuar.");
            aceiteTermos.focus();
            return;
        }

        const form = document.getElementById('form-onboarding-adotante');
        const formData = new FormData(form);

        try {
            const btnSubmit = event.target;
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'Aguarde...';
            btnSubmit.disabled = true;

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'sucesso') {
                if (typeof limparAutoSave === 'function') limparAutoSave();
                window.location.href = result.redirect_url;
            } else {
                mostrarModalFeedback('erro', result.mensagem);
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        } catch (error) {
            console.error(error);
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        OnboardingManager.init({
            totalEtapas: 6,
            urlSelecionarPerfil: "<?= URL_BASE ?>/onboarding",
            validarEnvioFinal: () => true 
        });
    });
</script>

<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>