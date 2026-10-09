<?php
require_once __DIR__ . '/../templates/header.php';

$tipoPerfil = $_SESSION['perfil_ativo']['tipo'] ?? $_SESSION['tipo_perfil'] ?? 'adotante';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';
?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<div class="max-w-md mx-auto bg-background min-h-screen pb-20 text-text-dark">

    <!-- CABEÇALHO -->
    <div class="py-4 px-6 flex items-center gap-4 rounded-b-[2rem] mb-6">
        <a href="<?= URL_BASE ?>/perfil" class="text-text-dark dark:text-white hover:scale-110 transition-transform flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
        </a>
    </div>

    <div class="px-4">
        <form action="<?= URL_BASE ?>/perfil/atualizar" method="POST" id="form-editar-perfil" enctype="multipart/form-data" class="space-y-3">

            <!-- FOTO DE PERFIL COM TRIGGER PARA A MODAL -->
            <div class="flex flex-col items-center mb-6">
                <input type="hidden" name="foto_cortada" id="foto_cortada_base64">

                <div class="relative <?= $tipoPerfil !== 'administrador' ? 'cursor-pointer group' : '' ?>" <?= $tipoPerfil !== 'administrador' ? 'onclick="abrirSeletorFoto()"' : '' ?>>
                    <div class="w-32 h-32 rounded-full border-[5px] border-rosa-3 dark:border-preto3 overflow-hidden bg-surface dark:bg-preto2 flex items-center justify-center shadow p-1">
                        <?php
                        $caminhoDB = $especifico['foto_perfil'] ?? $_SESSION['foto_perfil'] ?? '';
                        if ($tipoPerfil === 'administrador') {
                            $fotoSrc = $urlBase . '/assets/img/logo.png';
                        } elseif (!empty($caminhoDB)) {
                            $fotoLimpa = ltrim(trim($caminhoDB), '/');
                            $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
                            $fotoSrc = $urlBase . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
                        } else {
                            $fotoSrc = $urlBase . '/assets/img/perfil-placeholder.png';
                        }
                        ?>
                        <img src="<?= htmlspecialchars($fotoSrc) ?>" id="preview-foto" alt="Sua foto" class="w-full h-full rounded-full <?= $tipoPerfil === 'administrador' ? 'object-contain' : 'object-cover' ?>" onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                    </div>

                    <!-- Lápis flutuante APENAS se NÃO for administrador -->
                    <?php if ($tipoPerfil !== 'administrador'): ?>
                        <div class="absolute bottom-1 right-1 bg-surface dark:bg-preto1 p-2 rounded-full shadow border border-rosa-2 text-text-muted group-hover:bg-rosa-1 transition flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($tipoPerfil !== 'administrador'): ?>
                    <span class="text-xs text-text-muted mt-2">Clique na foto para ajustar</span>
                    <input type="file" id="input-arquivo-original" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="iniciarCropper(event)">
                <?php else: ?>
                    <span class="text-xs text-text-muted mt-2">A foto do administrador é fixa.</span>
                <?php endif; ?>
            </div>

            <!-- ACORDEÃO 1: SOBRE MIM -->
            <div class="bg-surface dark:bg-preto1 rounded-2xl shadow-sm overflow-hidden border border-rosa-2 dark:border-preto3">
                <button type="button" class="w-full px-5 py-4 flex justify-between items-center bg-rosa-1/20 dark:bg-preto2 hover:bg-rosa-1/30 transition focus:outline-none" onclick="toggleAccordion('acc-sobre')">
                    <span class="font-bold text-lg text-text-dark dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                          <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                        </svg>
                        Dados Principais
                    </span>
                    <span id="icon-acc-sobre" class="text-text-muted transition-transform duration-300">▼</span>
                </button>
                <div id="acc-sobre" class="hidden px-5 py-4 space-y-4 border-t border-rosa-2 dark:border-preto3">

                    <div>
                        <label class="label-padrao">Nome (Responsável) *</label>
                        <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                    </div>

                    <div>
                        <label class="label-padrao">Telefone / WhatsApp *</label>
                        <input type="tel" name="telefone" id="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                    </div>

                    <div>
                        <label class="label-padrao">Data de Nascimento</label>
                        <input type="date" value="<?= htmlspecialchars($usuario['dt_nasc'] ?? '') ?>" disabled class="input-padrao bg-surface/50 dark:bg-preto3 text-text-muted cursor-not-allowed">
                        <p class="text-[10px] text-text-muted mt-1">A data de nascimento não pode ser alterada.</p>
                    </div>

                    <!-- ESPECÍFICO PARA ONGS E PROTETORES -->
                    <?php if (in_array($tipoPerfil, ['ong', 'protetor'])): ?>
                        <?php
                        $isOng = ($tipoPerfil === 'ong');
                        $labelDoc = $isOng ? 'CNPJ da ONG *' : 'CPF do Protetor *';
                        $placeholderDoc = $isOng ? '00.000.000/0000-00' : '000.000.000-00';
                        ?>
                        <hr class="border-rosa-2 dark:border-preto3 my-2">
                        <div>
                            <label class="label-padrao"><?= $isOng ? 'Nome da Instituição (ONG) *' : 'Nome Fantasia / Atuação *' ?></label>
                            <input type="text" name="nome_fantasia" id="nome_fantasia" value="<?= htmlspecialchars($especifico['nome_fantasia'] ?? '') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                        </div>

                        <!-- CAMPO DE DOCUMENTO COM CHAVE DE DESBLOQUEIO -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="label-padrao mb-0"><?= $labelDoc ?></label>
                                <button type="button" onclick="toggleEditarDocumento()" id="btn-trava-doc" class="text-xs text-roxinhoFofo font-bold flex items-center gap-1 hover:underline cursor-pointer">
                                    <span id="icone-trava" class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-lock-fill" viewBox="0 0 16 16">
                                          <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                                        </svg>
                                    </span>
                                    <span id="texto-trava">Alterar documento</span>
                                </button>
                            </div>

                            <div class="relative">
                                <input type="text" name="codigo_documento" id="codigo_documento"
                                    value="<?= htmlspecialchars($especifico['codigo_documento'] ?? '') ?>"
                                    placeholder="<?= $placeholderDoc ?>"
                                    readonly
                                    class="input-padrao bg-surface/50 dark:bg-preto3 text-text-muted cursor-not-allowed transition-colors">
                                <input type="hidden" name="codigo_documento_atual" value="<?= htmlspecialchars($especifico['codigo_documento'] ?? '') ?>">
                            </div>
                        </div>

                        <div id="container-novo-comprovante" class="hidden p-3 bg-aviso/10 border border-aviso/30 rounded-xl space-y-2">
                            <p class="text-xs font-semibold text-aviso flex items-start gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-0.5" viewBox="0 0 16 16">
                                  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                                </svg>
                                <span><strong>Atenção:</strong> Ao alterar o documento, é obrigatório enviar o novo comprovante e sua conta entrará em análise novamente.</span>
                            </p>
                            <div>
                                <label class="block text-xs font-bold text-text-dark dark:text-white mb-1">Novo Comprovante de Atividade (PDF ou Imagem) *</label>
                                <input type="hidden" name="comprovante_atual" value="<?= htmlspecialchars($especifico['comprovante_documento'] ?? '') ?>">
                                <input type="file" name="comprovante_documento" id="comprovante_documento" accept=".pdf, .jpg, .jpeg, .png" class="input-padrao bg-surface dark:bg-preto2 text-xs py-2">
                            </div>
                        </div>

                        <div>
                            <label class="label-padrao">Descrição / Causa</label>
                            <textarea name="descricao" id="descricao" rows="3" placeholder="Apresente sua causa..." class="input-padrao bg-branco dark:bg-preto2 dark:text-white"><?= htmlspecialchars($especifico['descricao'] ?? '') ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACORDEÃO 2: LOCALIZAÇÃO -->
            <?php if ($tipoPerfil !== 'administrador'): ?>
                <div class="bg-surface dark:bg-preto1 rounded-2xl shadow-sm overflow-hidden border border-rosa-2 dark:border-preto3">
                    <button type="button" class="w-full px-5 py-4 flex justify-between items-center bg-rosa-1/20 dark:bg-preto2 hover:bg-rosa-1/30 transition focus:outline-none" onclick="toggleAccordion('acc-local')">
                        <span class="font-bold text-lg text-text-dark dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                              <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
                              <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            </svg>
                            Localização
                        </span>
                        <span id="icon-acc-local" class="text-text-muted transition-transform duration-300">▼</span>
                    </button>
                    <div id="acc-local" class="hidden px-5 py-4 space-y-4 border-t border-rosa-2 dark:border-preto3">
                        <div class="relative">
                            <label class="label-padrao">Bairro / Região *</label>
                            <?php
                            $nomeRegiaoAtual = '';
                            if (!empty($regiaoAtual)) {
                                $nomeRegiaoAtual = is_array($regiaoAtual) ? ($regiaoAtual['nome_regiao'] ?? '') : $regiaoAtual->getNomeRegiao();
                            }
                            ?>
                            <input type="text" id="input-busca-bairro" list="lista-regioes" autocomplete="off" class="input-padrao input-com-seta bg-branco dark:bg-preto2 dark:text-white"
                                value="<?= htmlspecialchars($nomeRegiaoAtual) ?>"
                                oninput="CaonectadosValidator.validarRegiao('input-busca-bairro', 'regiao_id_hidden', 'lista-regioes')">

                            <datalist id="lista-regioes">
                                <?php if (!empty($regioes)): ?>
                                    <?php foreach ($regioes as $regiao): ?>
                                        <?php
                                        $regId = is_array($regiao) ? $regiao['regiao_id'] : $regiao->getRegiaoId();
                                        $regNome = is_array($regiao) ? $regiao['nome_regiao'] : $regiao->getNomeRegiao();
                                        ?>
                                        <option data-id="<?= $regId ?>" value="<?= htmlspecialchars($regNome) ?>"></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </datalist>

                            <input type="hidden" name="regiao_id" id="regiao_id_hidden" value="<?= htmlspecialchars($usuario['regiao_id'] ?? '') ?>">
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="label-padrao">Logradouro *</label>
                                <input type="text" name="logradouro" id="logradouro" value="<?= htmlspecialchars($usuario['logradouro'] ?? '') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                            </div>
                            <div class="col-span-1">
                                <label class="label-padrao">Número *</label>
                                <input type="text" name="numero" id="numero" value="<?= htmlspecialchars($usuario['numero'] ?? '') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $espCachorro = null;
            $espGato = null;
            $outrasEspecies = [];

            if (!empty($especies)) {
                foreach ($especies as $esp) {
                    $idStr = (string)(is_array($esp) ? $esp['especie_id'] : $esp->getEspecieId());
                    $nome = is_array($esp) ? $esp['nome'] : $esp->getNome();
                    $nomeLc = strtolower(trim($nome));

                    if ($idStr === '1' || str_contains($nomeLc, 'cão') || str_contains($nomeLc, 'cachorro')) {
                        $espCachorro = ['id' => $idStr, 'nome' => $nome];
                    } elseif ($idStr === '2' || str_contains($nomeLc, 'gato')) {
                        $espGato = ['id' => $idStr, 'nome' => $nome];
                    } else {
                        $outrasEspecies[] = ['id' => $idStr, 'nome' => $nome];
                    }
                }
            }
            ?>

            <!-- ACORDEÃO 3: PREFERÊNCIAS / DOAÇÕES -->
            <?php if ($tipoPerfil === 'adotante' || $tipoPerfil === 'usuario'): ?>
                <?php
                // Os valores já vêm normalizados do PerfilController::editar() (compatível com os
                // dois formatos históricos de JSON já salvos em ADOTANTE.detalhes: chaves planas
                // "preferencias_especie" e a estrutura antiga aninhada "preferencias.especie").
                $prefEspecie = array_map('strval', $especifico['preferencias_especie'] ?? []);
                $prefPorte   = $especifico['preferencias_porte'] ?? [];
                $prefSexo    = $especifico['preferencias_sexo'] ?? [];
                ?>
                <div class="bg-surface dark:bg-preto1 rounded-2xl shadow-sm overflow-hidden border border-rosa-2 dark:border-preto3">
                    <button type="button" class="w-full px-5 py-4 flex justify-between items-center bg-rosa-1/20 dark:bg-preto2 hover:bg-rosa-1/30 transition focus:outline-none" onclick="toggleAccordion('acc-pref')">
                        <span class="font-bold text-lg text-text-dark dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                              <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                            </svg>
                            Sua Casa e Preferências
                        </span>
                        <span id="icon-acc-pref" class="text-text-muted transition-transform duration-300">▼</span>
                    </button>
                    <div id="acc-pref" class="hidden px-5 py-4 space-y-4 border-t border-rosa-2 dark:border-preto3">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="label-padrao">Moradia</label>
                                <select name="tipo_morada" id="tipo_morada" class="input-padrao bg-surface dark:bg-preto2 text-sm">
                                    <option value="casa" <?= (($especifico['tipo_moradia'] ?? '') === 'casa') ? 'selected' : '' ?>>Casa</option>
                                    <option value="apartamento" <?= (($especifico['tipo_moradia'] ?? '') === 'apartamento') ? 'selected' : '' ?>>Apto.</option>
                                    <option value="sitio" <?= (($especifico['tipo_moradia'] ?? '') === 'sitio') ? 'selected' : '' ?>>Sítio / Chácara</option>
                                    <option value="outro" <?= (($especifico['tipo_moradia'] ?? '') === 'outro') ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-padrao">Espaço Interno</label>
                                <select name="tamanho_interno_morada" id="tamanho_interno_morada" class="input-padrao bg-surface dark:bg-preto2 text-sm">
                                    <option value="pequeno" <?= (($especifico['tamanho_interno_moradia'] ?? '') === 'pequeno') ? 'selected' : '' ?>>Pequeno</option>
                                    <option value="medio" <?= (($especifico['tamanho_interno_moradia'] ?? '') === 'medio') ? 'selected' : '' ?>>Médio</option>
                                    <option value="grande" <?= (($especifico['tamanho_interno_moradia'] ?? '') === 'grande') ? 'selected' : '' ?>>Grande</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="label-padrao">Espaço Externo / Quintal</label>
                            <select name="espaco_externo" id="espaco_externo" class="input-padrao bg-surface dark:bg-preto2 text-sm">
                                <option value="nenhum" <?= (($especifico['espaco_externo'] ?? '') === 'nenhum') ? 'selected' : '' ?>>Não possui quintal</option>
                                <option value="pequeno" <?= (($especifico['espaco_externo'] ?? '') === 'pequeno') ? 'selected' : '' ?>>Quintal pequeno</option>
                                <option value="medio" <?= (($especifico['espaco_externo'] ?? '') === 'medio') ? 'selected' : '' ?>>Quintal médio</option>
                                <option value="grande" <?= (($especifico['espaco_externo'] ?? '') === 'grande') ? 'selected' : '' ?>>Quintal grande</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="label-padrao">Crianças em casa?</label>
                                <select name="possui_criancas" id="possui_criancas" class="input-padrao bg-surface dark:bg-preto2 text-sm">
                                    <option value="sim" <?= (($especifico['possui_criancas'] ?? '') === 'sim') ? 'selected' : '' ?>>Sim</option>
                                    <option value="nao" <?= (($especifico['possui_criancas'] ?? '') === 'nao') ? 'selected' : '' ?>>Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-padrao">Outros pets?</label>
                                <select name="possui_outros_pets" id="possui_outros_pets" class="input-padrao bg-surface dark:bg-preto2 text-sm">
                                    <option value="sim" <?= (($especifico['possui_outros_pets'] ?? '') === 'sim') ? 'selected' : '' ?>>Sim</option>
                                    <option value="nao" <?= (($especifico['possui_outros_pets'] ?? '') === 'nao') ? 'selected' : '' ?>>Não</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-rosa-2 dark:border-preto3 my-4">
                        <h4 class="font-bold text-md text-text-dark dark:text-white">Preferências de Adoção</h4>

                        <!-- Espécies -->
                        <div>
                            <span class="text-sm font-medium text-text-dark dark:text-white block mb-2">Espécie:</span>
                            <div class="space-y-2">
                                <?php if ($espCachorro): ?>
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-sm text-text-dark dark:text-white">
                                        <input type="checkbox" name="preferencias_especie[]" value="<?= $espCachorro['id'] ?>" <?= in_array($espCachorro['id'], $prefEspecie, true) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> <?= htmlspecialchars($espCachorro['nome']) ?>
                                    </label>
                                <?php endif; ?>

                                <?php if ($espGato): ?>
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-sm text-text-dark dark:text-white">
                                        <input type="checkbox" name="preferencias_especie[]" value="<?= $espGato['id'] ?>" <?= in_array($espGato['id'], $prefEspecie, true) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> <?= htmlspecialchars($espGato['nome']) ?>
                                    </label>
                                <?php endif; ?>

                                <?php if (!empty($outrasEspecies)): ?>
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-sm text-text-dark dark:text-white">
                                        <input type="checkbox" id="checkbox-outras-especies" onchange="toggleOutrasEspecies()" value="outros" class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Outros
                                    </label>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($outrasEspecies)): ?>
                                <div id="container-outras-especies" class="hidden mt-3 p-3 border border-rosa-2 dark:border-preto3 rounded-lg bg-surface dark:bg-preto2">
                                    <label class="block font-medium mb-2 text-xs text-text-muted">Selecione outras espécies desejadas:</label>
                                    <div class="space-y-2 max-h-40 overflow-y-auto pl-1">
                                        <?php foreach ($outrasEspecies as $espOutra): ?>
                                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-roxinhoFofo transition text-text-dark dark:text-white">
                                                <input type="checkbox" name="preferencias_especie[]" value="<?= $espOutra['id'] ?>" <?= in_array($espOutra['id'], $prefEspecie, true) ? 'checked' : '' ?> class="check-outras text-roxinhoFofo focus:ring-roxinhoFofo rounded">
                                                <?= htmlspecialchars($espOutra['nome']) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Porte e Sexo -->
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <span class="text-sm font-medium text-text-dark dark:text-white block mb-2">Porte:</span>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-xs text-text-dark dark:text-white"><input type="checkbox" name="preferencias_porte[]" value="pequeno" <?= in_array('pequeno', $prefPorte) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Pequeno</label>
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-xs text-text-dark dark:text-white"><input type="checkbox" name="preferencias_porte[]" value="medio" <?= in_array('medio', $prefPorte) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Médio</label>
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-xs text-text-dark dark:text-white"><input type="checkbox" name="preferencias_porte[]" value="grande" <?= in_array('grande', $prefPorte) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Grande</label>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-text-dark dark:text-white block mb-2">Sexo:</span>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-xs text-text-dark dark:text-white"><input type="checkbox" name="preferencias_sexo[]" value="femea" <?= in_array('femea', $prefSexo) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Fêmea</label>
                                    <label class="flex items-center gap-2 p-2 bg-surface dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-lg cursor-pointer hover:bg-rosa-1/20 text-xs text-text-dark dark:text-white"><input type="checkbox" name="preferencias_sexo[]" value="macho" <?= in_array('macho', $prefSexo) ? 'checked' : '' ?> class="text-roxinhoFofo focus:ring-roxinhoFofo rounded"> Macho</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php elseif (in_array($tipoPerfil, ['ong', 'protetor'])): ?>
                <div class="bg-surface dark:bg-preto1 rounded-2xl shadow-sm overflow-hidden border border-rosa-2 dark:border-preto3">
                    <button type="button" class="w-full px-5 py-4 flex justify-between items-center bg-rosa-1/20 dark:bg-preto2 hover:bg-rosa-1/30 transition focus:outline-none" onclick="toggleAccordion('acc-pref')">
                        <span class="font-bold text-lg text-text-dark dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
                              <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                            </svg>
                            Doações e Redes
                        </span>
                        <span id="icon-acc-pref" class="text-text-muted transition-transform duration-300">▼</span>
                    </button>
                    <div id="acc-pref" class="hidden px-5 py-4 space-y-4 border-t border-rosa-2 dark:border-preto3">
                        <div>
                            <label class="label-padrao">Chave PIX (Para receber doações)</label>
                            <input type="text" name="chave_pix" id="chave_pix" value="<?= htmlspecialchars($especifico['chave_pix'] ?? '') ?>" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                        </div>
                        <div>
                            <label class="label-padrao">Link do Instagram</label>
                            <input type="text" name="instagram" id="instagram" value="<?= htmlspecialchars($redes['instagram'] ?? '') ?>" placeholder="https://instagram.com/seu_perfil" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                        </div>
                        <div>
                            <label class="label-padrao">Link do Facebook</label>
                            <input type="text" name="facebook" id="facebook" value="<?= htmlspecialchars($redes['facebook'] ?? '') ?>" placeholder="https://facebook.com/seu_perfil" class="input-padrao bg-branco dark:bg-preto2 dark:text-white">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ACORDEÃO 4: SEGURANÇA -->
            <div class="bg-surface dark:bg-preto1 rounded-2xl shadow-sm overflow-hidden border border-rosa-2 dark:border-preto3">
                <button type="button" class="w-full px-5 py-4 flex justify-between items-center bg-rosa-1/20 dark:bg-preto2 hover:bg-rosa-1/30 transition focus:outline-none" onclick="toggleAccordion('acc-seguranca')">
                    <span class="font-bold text-lg text-text-dark dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                          <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1"/>
                        </svg>
                        Segurança
                    </span>
                    <span id="icon-acc-seguranca" class="text-text-muted transition-transform duration-300">▼</span>
                </button>
                <div id="acc-seguranca" class="hidden px-5 py-4 space-y-4 border-t border-rosa-2 dark:border-preto3">
                    <div>
                        <label class="label-padrao">E-mail Atual</label>
                        <p class="text-sm font-bold text-text-dark dark:text-white mb-2"><?= htmlspecialchars($emailMascarado ?? '') ?></p>
                        <a href="<?= URL_BASE ?>/perfil/trocar-email" class="inline-flex items-center gap-2 bg-roxinhoFofo text-primary py-2 px-4 rounded-xl font-bold text-xs hover:opacity-90 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                              <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                            </svg>
                            Trocar E-mail
                        </a>
                    </div>
                    <hr class="border-rosa-2 dark:border-preto3 my-2">
                    <div>
                        <label class="label-padrao">Senha de Acesso</label>
                        <p class="text-xs text-text-muted mb-3">Para garantir sua segurança, a troca de senha exige verificação por e-mail.</p>
                        <a href="<?= URL_BASE ?>/perfil/redefinir-senha" class="inline-flex items-center gap-2 bg-roxinhoFofo text-primary py-2 px-5 rounded-xl font-bold text-sm hover:opacity-90 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                              <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                              <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                            </svg>
                            Redefinir Senha
                        </a>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primario w-full mt-6 mb-8 text-lg">
                Salvar Alterações
            </button>
        </form>
    </div>
</div>

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

<script>
    const tipoPerfilAtual = '<?= $tipoPerfil ?>';

    function toggleAccordion(id) {
        const conteudo = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (!conteudo) return;

        if (conteudo.classList.contains('hidden')) {
            conteudo.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            conteudo.classList.add('hidden');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }

    function abrirAccordion(id) {
        const conteudo = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (!conteudo) return;

        if (conteudo.classList.contains('hidden')) {
            conteudo.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }

    function toggleEditarDocumento() {
        const inputDoc = document.getElementById('codigo_documento');
        const containerComprovante = document.getElementById('container-novo-comprovante');
        const iconeTrava = document.getElementById('icone-trava');
        const textoTrava = document.getElementById('texto-trava');

        if (inputDoc.hasAttribute('readonly')) {
            inputDoc.removeAttribute('readonly');
            inputDoc.classList.remove('bg-surface/50', 'text-text-muted', 'cursor-not-allowed');
            inputDoc.classList.add('bg-surface', 'text-text-dark', 'dark:text-white', 'border-roxinhoFofo', 'ring-2', 'ring-roxinhoFofo/20');
            containerComprovante.classList.remove('hidden');
            iconeTrava.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-unlock-fill" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2"/></svg>';
            textoTrava.innerText = 'Cancelar alteração';
            inputDoc.focus();
        } else {
            inputDoc.setAttribute('readonly', 'readonly');
            inputDoc.classList.add('bg-surface/50', 'text-text-muted', 'cursor-not-allowed');
            inputDoc.classList.remove('bg-surface', 'text-text-dark', 'dark:text-white', 'border-roxinhoFofo', 'ring-2', 'ring-roxinhoFofo/20');
            containerComprovante.classList.add('hidden');
            iconeTrava.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-lock-fill" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>';
            textoTrava.innerText = 'Alterar documento';

            const docAtual = document.querySelector('input[name="codigo_documento_atual"]');
            if (docAtual) inputDoc.value = docAtual.value;

            const fileDoc = document.getElementById('comprovante_documento');
            if (fileDoc) fileDoc.value = '';
        }
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

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('container-outras-especies');
        if (container) {
            const temOutros = Array.from(container.querySelectorAll('.check-outras')).some(cb => cb.checked);
            if (temOutros) {
                const cbOutros = document.getElementById('checkbox-outras-especies');
                if (cbOutros) cbOutros.checked = true;
                container.classList.remove('hidden');
            }
        }
    });

    let cropper = null;

    function abrirSeletorFoto() {
        document.getElementById('input-arquivo-original').click();
    }

    function iniciarCropper(event) {
        const fileInput = event.target;
        if (fileInput.files && fileInput.files.length > 0) {
            if (!CaonectadosValidator.validarTamanhoArquivo(fileInput, 5)) {
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
                document.getElementById('imagem-para-cortar').src = e.target.result;
                document.getElementById('modal-cropper').classList.remove('hidden');
                if (cropper) cropper.destroy();
                cropper = new Cropper(document.getElementById('imagem-para-cortar'), {
                    aspectRatio: 1 / 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
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
        document.getElementById('input-arquivo-original').value = '';
    }

    function salvarRecorte() {
        if (!cropper) return;
        const base64String = cropper.getCroppedCanvas({
            width: 400,
            height: 400
        }).toDataURL('image/png');
        document.getElementById('preview-foto').src = base64String;
        document.getElementById('foto_cortada_base64').value = base64String;
        fecharModalCropper();
    }

    document.getElementById('form-editar-perfil').addEventListener('submit', async function(event) {
        event.preventDefault();

        const nome = document.getElementById('nome');
        if (nome && !CaonectadosValidator.validarNome(nome.value)) {
            if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('aviso', 'O nome deve conter pelo menos 2 caracteres.');
            abrirAccordion('acc-sobre');
            nome.focus();
            return;
        }

        const telefone = document.getElementById('telefone');
        if (telefone && !CaonectadosValidator.validarTelefone(telefone.value)) {
            if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('aviso', 'Telefone inválido. Inclua o DDD.');
            abrirAccordion('acc-sobre');
            telefone.focus();
            return;
        }

        const docInput = document.getElementById('codigo_documento');
        const docAtual = document.querySelector('input[name="codigo_documento_atual"]');

        if (docInput && docAtual && !docInput.hasAttribute('readonly')) {
            const docLimpo = docInput.value.replace(/[^\d]+/g, '');
            const docAtualLimpo = docAtual.value.replace(/[^\d]+/g, '');

            if (tipoPerfilAtual === 'ong' && (docLimpo.length !== 14 || !CaonectadosValidator.isCnpjValido(docLimpo))) {
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'O CNPJ informado é inválido.');
                abrirAccordion('acc-sobre');
                docInput.focus();
                return;
            } else if (tipoPerfilAtual === 'protetor' && (docLimpo.length !== 11 || !CaonectadosValidator.isCpfValido(docLimpo))) {
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'O CPF informado é inválido.');
                abrirAccordion('acc-sobre');
                docInput.focus();
                return;
            }

            if (docLimpo !== docAtualLimpo) {
                const comprovanteInput = document.getElementById('comprovante_documento');
                if (!comprovanteInput || comprovanteInput.files.length === 0) {
                    if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('aviso', 'Como você alterou o seu documento, é obrigatório anexar o novo comprovante.');
                    abrirAccordion('acc-sobre');
                    return;
                }
            }
        }

        const comprovanteGeral = document.getElementById('comprovante_documento');
        if (comprovanteGeral && comprovanteGeral.files.length > 0) {
            if (!CaonectadosValidator.validarTamanhoArquivo(comprovanteGeral, 5)) {
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'O comprovante excede o tamanho máximo de 5MB.');
                abrirAccordion('acc-sobre');
                return;
            }
        }

        const inputBuscaBairro = document.getElementById('input-busca-bairro');
        if (inputBuscaBairro) {
            const isRegiaoValida = CaonectadosValidator.validarRegiao('input-busca-bairro', 'regiao_id_hidden', 'lista-regioes');
            if (!isRegiaoValida) {
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('aviso', 'Selecione um bairro válido da lista.');
                abrirAccordion('acc-local');
                inputBuscaBairro.focus();
                return;
            }
        }

        const pix = document.getElementById('chave_pix');
        if (pix && pix.value.trim() !== '' && !CaonectadosValidator.validarChavePix(pix.value.trim())) {
            if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'A chave PIX informada é inválida.');
            abrirAccordion('acc-pref');
            pix.focus();
            return;
        }

        const insta = document.getElementById('instagram');
        if (insta && insta.value.trim() !== '' && !CaonectadosValidator.validarLinkSocial(insta.value.trim(), 'instagram')) {
            if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'Link do Instagram inválido.');
            abrirAccordion('acc-pref');
            insta.focus();
            return;
        }

        const face = document.getElementById('facebook');
        if (face && face.value.trim() !== '' && !CaonectadosValidator.validarLinkSocial(face.value.trim(), 'facebook')) {
            if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'Link do Facebook inválido.');
            abrirAccordion('acc-pref');
            face.focus();
            return;
        }

        const form = event.target;
        const formData = new FormData(form);
        const btnSubmit = form.querySelector('button[type="submit"]');
        const txtBtn = btnSubmit.innerHTML;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Salvando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'erro') {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = txtBtn;
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', result.mensagem);

                if (result.campo) {
                    const campoEl = document.getElementById(result.campo) || document.querySelector(`[name="${result.campo}"]`);
                    if (campoEl) {
                        const accordionPai = campoEl.closest('[id^="acc-"]');
                        if (accordionPai) abrirAccordion(accordionPai.id);
                        campoEl.focus();
                    }
                }
            } else {
                if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('sucesso', result.mensagem);
                setTimeout(() => window.location.href = result.redirect_url, 1500);
            }
        } catch (error) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = txtBtn;
            if (typeof mostrarModalFeedback === 'function') mostrarModalFeedback('erro', 'Erro ao comunicar com o servidor.');
        }
    });
</script>

<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>