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
        <a href="<?= URL_BASE ?>/perfil" class="text-2xl hover:scale-110 transition-transform text-text-dark dark:text-white">&larr;</a>
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
                        <div class="absolute bottom-1 right-1 bg-surface dark:bg-preto1 p-2 rounded-full shadow border border-rosa-2 text-text-muted group-hover:bg-rosa-1 transition">
                            ✏️
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
                    <span class="font-bold text-lg text-text-dark dark:text-white">👤 Dados Principais</span>
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
                                    <span id="icone-trava">🔒</span> <span id="texto-trava">Alterar documento</span>
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
                            <p class="text-xs font-semibold text-aviso flex items-center gap-1">
                                ⚠️ <strong>Atenção:</strong> Ao alterar o documento, é obrigatório enviar o novo comprovante e sua conta entrará em análise novamente.
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
                        <span class="font-bold text-lg text-text-dark dark:text-white">📍 Localização</span>
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
                        <span class="font-bold text-lg text-text-dark dark:text-white">🏠 Sua Casa e Preferências</span>
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
                        <span class="font-bold text-lg text-text-dark dark:text-white">❤️ Doações e Redes</span>
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
                    <span class="font-bold text-lg text-text-dark dark:text-white">🔒 Segurança</span>
                    <span id="icon-acc-seguranca" class="text-text-muted transition-transform duration-300">▼</span>
                </button>
                <div id="acc-seguranca" class="hidden px-5 py-4 space-y-4 border-t border-rosa-2 dark:border-preto3">
                    <div>
                        <label class="label-padrao">E-mail Atual</label>
                        <p class="text-sm font-bold text-text-dark dark:text-white mb-2"><?= htmlspecialchars($emailMascarado ?? '') ?></p>
                        <a href="<?= URL_BASE ?>/perfil/trocar-email" class="inline-block bg-roxinhoFofo text-primary py-2 px-4 rounded-xl font-bold text-xs hover:opacity-90 transition shadow-sm">
                            ✉️ Trocar E-mail
                        </a>
                    </div>
                    <hr class="border-rosa-2 dark:border-preto3 my-2">
                    <div>
                        <label class="label-padrao">Senha de Acesso</label>
                        <p class="text-xs text-text-muted mb-3">Para garantir sua segurança, a troca de senha exige verificação por e-mail.</p>
                        <a href="<?= URL_BASE ?>/perfil/redefinir-senha" class="inline-block bg-roxinhoFofo text-primary py-2 px-5 rounded-xl font-bold text-sm hover:opacity-90 transition shadow-sm">
                            🔑 Redefinir Senha
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
            iconeTrava.innerText = '🔓';
            textoTrava.innerText = 'Cancelar alteração';
            inputDoc.focus();
        } else {
            inputDoc.setAttribute('readonly', 'readonly');
            inputDoc.classList.add('bg-surface/50', 'text-text-muted', 'cursor-not-allowed');
            inputDoc.classList.remove('bg-surface', 'text-text-dark', 'dark:text-white', 'border-roxinhoFofo', 'ring-2', 'ring-roxinhoFofo/20');
            containerComprovante.classList.add('hidden');
            iconeTrava.innerText = '🔒';
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