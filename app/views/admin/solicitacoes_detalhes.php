<?php 
$solicitacao = $solicitacao ?? [];
require_once __DIR__ . '/../templates/header.php'; 

// Tratamento do Comprovante de Documento
$docNome = (string) ($solicitacao['comprovante_documento'] ?? '');
$caminhoDoc = null;
$extensao = '';

if (!empty($docNome)) {
    $docLimpo = trim($docNome);
    if (strpos($docLimpo, 'http') === 0) {
        $caminhoDoc = $docLimpo;
    } else {
        $docLimpo = ltrim($docLimpo, '/');
        $docLimpo = preg_replace('#^(assets/)?(uploads/)+#', '', $docLimpo);
        $caminhoDoc = URL_BASE . '/assets/uploads/' . htmlspecialchars($docLimpo);
    }
    $extensao = strtolower(pathinfo($docLimpo, PATHINFO_EXTENSION));
}

// Tratamento da Foto de Perfil
$fotoNomeDet = $solicitacao['foto_perfil'] ?? null;
$caminhoFotoDet = null;

if (!empty($fotoNomeDet)) {
    $fotoLimpaDet = trim($fotoNomeDet);
    if (strpos($fotoLimpaDet, 'http') === 0) {
        $caminhoFotoDet = $fotoLimpaDet;
    } else {
        $fotoLimpaDet = ltrim($fotoLimpaDet, '/');
        $fotoLimpaDet = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpaDet);
        $caminhoFotoDet = URL_BASE . '/assets/uploads/' . htmlspecialchars($fotoLimpaDet);
    }
}
?>

<div class="min-h-screen bg-background text-text-dark p-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-4xl bg-surface rounded-3xl md:rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20 transition-colors flex flex-col justify-between">
        
        <div>
            <!-- Cabeçalho -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-2 border-b border-cinzaMarrom/20">
                <div>
                    <h2 class="font-shantell text-2xl font-bold text-text-dark tracking-tight">Detalhes da Solicitação</h2>
                    <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                        <?= htmlspecialchars(($solicitacao['nome_fantasia'] ?? '') ?: ($solicitacao['usuario_nome'] ?? 'ONG')) ?> •
                        <?= htmlspecialchars(strtoupper($solicitacao['tipo_documento'] ?? 'Documento')) ?>: <strong class="text-text-dark"><?= htmlspecialchars($solicitacao['codigo_documento'] ?? 'Não informado') ?></strong>
                    </p>
                </div>
                <a href="<?= URL_BASE ?>/admin/solicitacoes" class="self-start sm:self-center px-4 py-2 border-2 border-preto dark:border-cinzaMarrom rounded-xl text-xs sm:text-sm font-bold text-text-dark hover:bg-preto hover:text-white dark:hover:bg-primary transition flex items-center gap-2">
                    <span>Voltar para lista</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            </div>

            <!-- Preview do Documento -->
            <div class="border-2 border-preto dark:border-cinzaMarrom rounded-2xl overflow-hidden mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] dark:shadow-[4px_4px_0px_0px_rgba(255,255,255,0.15)] bg-branco dark:bg-preto2">
                <div class="bg-zinc-100/70 dark:bg-preto1/60 min-h-[280px] flex flex-col items-center justify-center p-4 text-center">
                    <?php if ($caminhoDoc): ?>
                        <?php if (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                            <img src="<?= $caminhoDoc ?>" alt="Comprovante" class="max-h-96 object-contain rounded-xl shadow-sm">
                        <?php elseif ($extensao === 'pdf'): ?>
                            <iframe src="<?= $caminhoDoc ?>" class="w-full h-96 rounded-xl border-0 bg-white" title="Visualizador do PDF"></iframe>
                        <?php else: ?>
                            <div class="py-8 text-text-dark">
                                <i class="fa-solid fa-file-lines text-5xl text-primary dark:text-roxinhoFofo mb-3"></i>
                                <p class="font-bold">Documento anexado no cadastro</p>
                                <a href="<?= $caminhoDoc ?>" target="_blank" class="mt-3 btn-primario text-xs">
                                    Abrir Documento em Nova Aba
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="font-bold text-text-muted text-sm sm:text-base">Documento PDF/Imagem: comprovante não anexado.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-branco dark:bg-preto2 px-5 py-3.5 border-t-2 border-preto dark:border-cinzaMarrom flex justify-between items-center">
                    <div>
                        <p class="text-xs font-bold text-text-dark uppercase tracking-wider">
                            <?= htmlspecialchars(strtoupper($solicitacao['tipo_documento'] ?? 'Documento')) ?>
                        </p>
                        <p class="text-xs text-text-muted font-medium">
                            <?= htmlspecialchars($solicitacao['codigo_documento'] ?? '1 documento anexado') ?>
                        </p>
                    </div>
                    <?php if ($caminhoDoc): ?>
                        <a href="<?= $caminhoDoc ?>" download class="text-xs font-bold text-primary dark:text-roxinhoFofo hover:underline flex items-center gap-1.5">
                            <i class="fa-solid fa-download"></i> Baixar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informações da Organização -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-text-dark mb-1">Informações da ONG</h3>
                <p class="text-xs text-text-muted mb-4">Verifique os dados antes de decidir</p>

                <div class="space-y-4 divide-y divide-cinzaMarrom/20">
                    <!-- Foto e Nome -->
                    <div class="flex items-start gap-4 pt-3 first:pt-0">
                        <?php if ($caminhoFotoDet): ?>
                            <img src="<?= $caminhoFotoDet ?>" 
                                 alt="Perfil" 
                                 class="w-14 h-14 rounded-2xl object-cover border border-cinzaMarrom/30 shrink-0 bg-white"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-14 h-14 bg-rosa-1 dark:bg-preto2 rounded-2xl shrink-0 flex items-center justify-center text-primary dark:text-roxinhoFofo border border-rosa-2/40\'><i class=\'fa-solid fa-building text-2xl\'></i></div>';">
                        <?php else: ?>
                            <div class="w-14 h-14 bg-rosa-1 dark:bg-preto2 rounded-2xl shrink-0 flex items-center justify-center text-primary dark:text-roxinhoFofo border border-rosa-2/40">
                                <i class="fa-solid fa-building text-2xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1">
                            <h4 class="font-bold text-text-dark text-sm sm:text-base">Nome</h4>
                            <p class="text-xs text-text-muted font-medium"><?= htmlspecialchars(($solicitacao['nome_fantasia'] ?? '') ?: ($solicitacao['usuario_nome'] ?? 'Não informado')) ?></p>
                            <p class="text-xs font-semibold text-text-dark mt-1"><?= htmlspecialchars($solicitacao['pagina_descricao'] ?? 'Organização voltada ao resgate e adoção responsável') ?></p>
                        </div>
                    </div>

                    <!-- Status Atual -->
                    <div class="flex items-start gap-4 pt-3">
                        <div class="w-14 h-14 bg-rosa-1/50 dark:bg-preto2 rounded-2xl border border-cinzaMarrom/30 shrink-0 flex items-center justify-center text-primary dark:text-roxinhoFofo">
                            <i class="fa-solid fa-circle-info text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-text-dark text-sm sm:text-base">Status Atual</h4>
                            <?php $statusCalculado = $solicitacao['status'] ?? 'pendente'; ?>
                            <p class="text-xs font-bold mt-0.5 <?= $statusCalculado === 'recusado' ? 'text-erro' : ($statusCalculado === 'aprovado' ? 'text-sucesso' : 'text-laranja-1') ?>">
                                <?= $statusCalculado === 'recusado' ? 'Recusado' : ($statusCalculado === 'aprovado' ? 'Aprovado' : 'Aguardando Análise') ?>
                            </p>
                            <p class="text-xs text-text-muted mt-1">
                                <?= $statusCalculado === 'recusado' ? 'Solicitação rejeitada pelo administrador' : ($statusCalculado === 'aprovado' ? 'Cadastro ativo e validado no sistema' : 'Solicitação aguardando validação do Admin') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Data do Registro -->
                    <div class="flex items-start gap-4 pt-3">
                        <div class="w-14 h-14 bg-rosa-1/50 dark:bg-preto2 rounded-2xl border border-cinzaMarrom/30 shrink-0 flex items-center justify-center text-primary dark:text-roxinhoFofo">
                            <i class="fa-regular fa-calendar-days text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-text-dark text-sm sm:text-base">Data da Solicitação</h4>
                            <p class="text-xs text-text-muted font-semibold"><?= !empty($solicitacao['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($solicitacao['criado_em'])) : 'Data não informada' ?></p>
                            <div class="mt-1.5">
                                <?php if ($statusCalculado === 'pendente'): ?>
                                    <span class="px-2.5 py-0.5 text-xs font-bold bg-laranja-1/20 text-laranja-1 rounded-md border border-laranja-1/30">Revisão pendente</span>
                                <?php elseif ($statusCalculado === 'recusado'): ?>
                                    <span class="px-2.5 py-0.5 text-xs font-bold bg-erro/10 text-erro rounded-md border border-erro/20">Revisão concluída (Recusada)</span>
                                <?php elseif ($statusCalculado === 'aprovado'): ?>
                                    <span class="px-2.5 py-0.5 text-xs font-bold bg-sucesso/10 text-sucesso rounded-md border border-sucesso/20">Aprovada</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados de Contato -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-text-dark mb-1">Contato e Localidade</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                    <div class="p-3.5 bg-branco dark:bg-preto2 rounded-2xl border border-cinzaMarrom/20">
                        <span class="text-xs text-text-muted block">E-mail</span>
                        <strong class="text-xs sm:text-sm text-text-dark break-all"><?= htmlspecialchars($solicitacao['usuario_email'] ?? 'Não informado') ?></strong>
                    </div>
                    <div class="p-3.5 bg-branco dark:bg-preto2 rounded-2xl border border-cinzaMarrom/20">
                        <span class="text-xs text-text-muted block">Telefone</span>
                        <strong class="text-xs sm:text-sm text-text-dark"><?= !empty($solicitacao['usuario_telefone']) ? htmlspecialchars($solicitacao['usuario_telefone']) : 'Não informado' ?></strong>
                    </div>
                    <div class="p-3.5 bg-branco dark:bg-preto2 rounded-2xl border border-cinzaMarrom/20">
                        <span class="text-xs text-text-muted block">Região / Bairro</span>
                        <strong class="text-xs sm:text-sm text-text-dark"><?= htmlspecialchars($solicitacao['nome_regiao'] ?? 'Não especificada') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <?php if (($solicitacao['status'] ?? 'pendente') === 'pendente'): ?>
            <div class="pt-4 border-t border-cinzaMarrom/20">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <button type="button"
                            id="btnToggleRecusa"
                            class="w-full py-3 bg-erro hover:bg-[#5c0503] text-white font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:scale-95 transition">
                        Recusar Cadastro
                    </button>

                    <form method="POST" action="<?= URL_BASE ?>/admin/solicitacoes/aprovar" class="w-full m-0 p-0">
                        <input type="hidden" name="protetor_id" value="<?= $solicitacao['protetor_id'] ?? '' ?>">
                        <button type="submit" 
                                class="w-full py-3 bg-verdeMusgo hover:opacity-90 text-white font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:scale-95 transition">
                            Aprovar Cadastro
                        </button>
                    </form>
                </div>

                <!-- Box Expansível: Motivo da Recusa -->
                <div id="containerMotivoRecusa" class="hidden mt-4 p-4 bg-branco dark:bg-preto2 border-2 border-preto dark:border-cinzaMarrom rounded-2xl shadow-inner">
                    <label for="inputMotivo" class="block text-xs sm:text-sm font-bold text-text-dark mb-2">
                        Motivo da recusa (será enviado ao usuário por e-mail):
                    </label>
                    <form method="POST" action="<?= URL_BASE ?>/admin/solicitacoes/rejeitar" class="flex flex-col sm:flex-row gap-2">
                        <input type="hidden" name="protetor_id" value="<?= $solicitacao['protetor_id'] ?? '' ?>">
                        <input type="text"
                               id="inputMotivo"
                               name="motivo_recusa"
                               placeholder="Ex.: Documento ilegível, CNPJ inativo, fotos insuficientes..."
                               class="input-padrao flex-1 py-2 sm:py-2.5 text-sm">
                        <button type="submit" class="px-6 py-2.5 bg-erro text-white font-bold text-xs sm:text-sm rounded-xl hover:opacity-90 transition">
                            Confirmar Recusa
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="p-4 rounded-2xl text-center <?= ($solicitacao['status'] === 'aprovado') ? 'bg-sucesso/10 border border-sucesso/30 text-sucesso' : 'bg-erro/10 border border-erro/30 text-erro' ?>">
                <p class="font-bold text-sm sm:text-base">
                    <i class="fa-solid <?= ($solicitacao['status'] === 'aprovado') ? 'fa-check-circle' : 'fa-times-circle' ?> mr-1.5"></i>
                    Esta solicitação já foi <?= ($solicitacao['status'] === 'aprovado') ? 'Aprovada' : 'Recusada' ?>.
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const btnToggle = document.getElementById('btnToggleRecusa');
    const containerRecusa = document.getElementById('containerMotivoRecusa');
    const inputMotivo = document.getElementById('inputMotivo');

    btnToggle?.addEventListener('click', () => {
        containerRecusa.classList.toggle('hidden');
        if (!containerRecusa.classList.contains('hidden')) {
            inputMotivo.focus();
            containerRecusa.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>