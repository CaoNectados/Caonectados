<?php
$solicitacao = $solicitacao ?? [];
require_once __DIR__ . '/../templates/header.php';

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$status = $solicitacao['status_solicitacao'] ?? 'pendente';
$finalizada = in_array($status, ['aprovada', 'reprovada', 'cancelada'], true);

$tipoMoradiaLabels = ['casa' => 'Casa', 'apartamento' => 'Apartamento', 'sitio' => 'Sítio', 'outro' => 'Outro'];
$tamanhoLabels = ['pequeno' => 'Pequeno', 'medio' => 'Médio', 'grande' => 'Grande'];
$espacoExternoLabels = ['nenhum' => 'Não possui quintal', 'pequeno' => 'Quintal pequeno', 'medio' => 'Quintal médio', 'grande' => 'Quintal grande'];

$detalhesAdotante = json_decode($solicitacao['adotante_detalhes'] ?? '{}', true) ?: [];
$possuiCriancas = $detalhesAdotante['possui_criancas'] ?? null;
$possuiOutrosPets = $detalhesAdotante['possui_outros_pets'] ?? null;
$espacoExterno = $detalhesAdotante['espaco_externo'] ?? null;

$idade = null;
if (!empty($solicitacao['adotante_dt_nasc'])) {
    $idade = (new DateTime())->diff(new DateTime($solicitacao['adotante_dt_nasc']))->y;
}

$fotoAnimal = !empty($solicitacao['animal_foto']) ? $urlBase . '/' . ltrim($solicitacao['animal_foto'], '/') : null;
?>

<div class="min-h-screen bg-background text-text-dark p-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-4xl bg-surface rounded-3xl md:rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20 transition-colors flex flex-col justify-between">

        <div>
            <!-- Cabeçalho -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-2 border-b border-cinzaMarrom/20">
                <div class="flex items-center gap-3">
                    <?php if ($fotoAnimal): ?>
                        <img src="<?= htmlspecialchars($fotoAnimal) ?>" alt="" class="w-12 h-12 rounded-2xl object-cover border border-cinzaMarrom/30">
                    <?php endif; ?>
                    <div>
                        <h2 class="font-shantell text-2xl font-bold text-text-dark tracking-tight">Detalhes da Solicitação</h2>
                        <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                            <?= htmlspecialchars($solicitacao['animal_nome']) ?> 🐾 • solicitante: <strong class="text-text-dark"><?= htmlspecialchars($solicitacao['adotante_nome']) ?></strong>
                        </p>
                    </div>
                </div>
                <a href="<?= $urlBase ?>/solicitacoes" class="self-start sm:self-center px-4 py-2 border-2 border-preto dark:border-cinzaMarrom rounded-xl text-xs sm:text-sm font-bold text-text-dark hover:bg-preto hover:text-white dark:hover:bg-primary transition">
                    Voltar para lista &rsaquo;
                </a>
            </div>

            <!-- Informações do solicitante -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-text-dark mb-1">Informações do solicitante</h3>
                <p class="text-xs text-text-muted mb-4">Verifique os dados antes de decidir</p>

                <div class="space-y-4 divide-y divide-cinzaMarrom/20">
                    <div class="pt-3 first:pt-0">
                        <h4 class="font-bold text-text-dark text-sm sm:text-base">Nome</h4>
                        <p class="text-xs text-text-muted font-medium">
                            <?= htmlspecialchars($solicitacao['adotante_nome']) ?><?= $idade !== null ? ", {$idade} anos" : '' ?>
                        </p>
                        <p class="text-xs text-text-muted font-medium"><?= htmlspecialchars($solicitacao['adotante_email']) ?></p>
                    </div>

                    <div class="pt-3">
                        <h4 class="font-bold text-text-dark text-sm sm:text-base">Sobre a moradia</h4>
                        <p class="text-xs text-text-muted font-medium">
                            <?= htmlspecialchars($tipoMoradiaLabels[$solicitacao['tipo_moradia']] ?? 'Não informado') ?><?= !empty($solicitacao['adotante_regiao']) ? ', ' . htmlspecialchars($solicitacao['adotante_regiao']) : '' ?>
                        </p>
                        <p class="text-xs text-text-muted">Espaço interno: <?= htmlspecialchars($tamanhoLabels[$solicitacao['tamanho_interno_moradia']] ?? 'Não informado') ?></p>
                        <p class="text-xs text-text-muted">Espaço externo: <?= htmlspecialchars($espacoExternoLabels[$espacoExterno] ?? 'Não informado') ?></p>
                    </div>

                    <div class="pt-3">
                        <h4 class="font-bold text-text-dark text-sm sm:text-base">Sobre a convivência</h4>
                        <p class="text-xs text-text-muted">Possui crianças em casa? <?= $possuiCriancas === 'sim' ? 'Sim' : ($possuiCriancas === 'nao' ? 'Não' : 'Não informado') ?></p>
                        <p class="text-xs text-text-muted">Possui outros pets? <?= $possuiOutrosPets === 'sim' ? 'Sim' : ($possuiOutrosPets === 'nao' ? 'Não' : 'Não informado') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações de triagem -->
        <?php if (!$finalizada): ?>
            <div class="pt-4 border-t border-cinzaMarrom/20 space-y-3">
                <?php if ($status === 'pendente'): ?>
                    <form method="POST" action="<?= $urlBase ?>/solicitacoes/em-analise">
                        <input type="hidden" name="id" value="<?= (int) $solicitacao['solicitacao_id'] ?>">
                        <button type="submit" class="w-full py-2.5 bg-surface hover:bg-rosa-1/30 text-text-dark font-bold text-sm rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition active:scale-95">
                            Colocar em Análise
                        </button>
                    </form>
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <button type="button" id="btnToggleRecusa"
                            class="w-full py-3 bg-erro hover:bg-[#5c0503] text-white font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:scale-95 transition">
                        Recusar adoção
                    </button>

                    <form id="form-aprovar-solicitacao" method="POST" action="<?= $urlBase ?>/solicitacoes/aprovar" class="w-full m-0 p-0">
                        <input type="hidden" name="id" value="<?= (int) $solicitacao['solicitacao_id'] ?>">
                        <button type="button"
                                onclick="abrirModalConfirmacao('Confirmar adoção', 'Confirmar a adoção de <?= htmlspecialchars(addslashes($solicitacao['animal_nome'])) ?> por <?= htmlspecialchars(addslashes($solicitacao['adotante_nome'])) ?>? As demais solicitações pendentes para este animal serão canceladas automaticamente.', function () { document.getElementById('form-aprovar-solicitacao').submit(); }, 'Confirmar 🐾', 'Voltar')"
                                class="w-full py-3 bg-verdeMusgo hover:opacity-90 text-white font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:scale-95 transition">
                            Confirmar 🐾
                        </button>
                    </form>
                </div>

                <div id="containerMotivoRecusa" class="hidden mt-2 p-4 bg-branco dark:bg-preto2 border-2 border-preto dark:border-cinzaMarrom rounded-2xl shadow-inner">
                    <label for="inputMotivo" class="block text-xs sm:text-sm font-bold text-text-dark mb-2">
                        Motivo da recusa (será enviado para o solicitante):
                    </label>
                    <form method="POST" action="<?= $urlBase ?>/solicitacoes/recusar" class="flex flex-col sm:flex-row gap-2">
                        <input type="hidden" name="id" value="<?= (int) $solicitacao['solicitacao_id'] ?>">
                        <input type="text" id="inputMotivo" name="justificativa" required
                               placeholder="Ex.: Pouco espaço, o pet não é sociável..."
                               class="input-padrao flex-1 py-2 sm:py-2.5 text-sm">
                        <button type="submit" class="px-6 py-2.5 bg-erro text-white font-bold text-xs sm:text-sm rounded-xl hover:opacity-90 transition">
                            Confirmar Recusa
                        </button>
                    </form>
                    <p class="text-[11px] text-text-muted mt-2">Campo aparece somente ao clicar em "Recusar adoção"</p>
                </div>
            </div>
        <?php else: ?>
            <?php
                $mensagensFinalizada = [
                    'aprovada'  => ['texto' => 'Esta solicitação foi aprovada. O chat com o adotante já está disponível.', 'classe' => 'bg-sucesso/10 border-sucesso/30 text-sucesso'],
                    'reprovada' => ['texto' => 'Esta solicitação foi recusada.', 'classe' => 'bg-erro/10 border-erro/30 text-erro'],
                    'cancelada' => ['texto' => 'Esta solicitação foi cancelada.', 'classe' => 'bg-cinzaMarrom/10 border-cinzaMarrom/30 text-text-muted'],
                ];
                $devolvido = $status === 'aprovada' && ($solicitacao['animal_status'] ?? '') !== 'adotado';
                if ($devolvido) {
                    $msg = ['texto' => 'Esta adoção foi desfeita — o animal voltou a ficar disponível no catálogo.', 'classe' => 'bg-cinzaMarrom/10 border-cinzaMarrom/30 text-text-muted'];
                } else {
                    $msg = $mensagensFinalizada[$status] ?? ['texto' => 'Esta solicitação já foi finalizada.', 'classe' => 'bg-cinzaMarrom/10 border-cinzaMarrom/30 text-text-muted'];
                }
            ?>
            <div class="p-4 rounded-2xl text-center border <?= $msg['classe'] ?>">
                <p class="font-bold text-sm sm:text-base"><?= htmlspecialchars($msg['texto']) ?></p>
                <?php if ($status === 'reprovada' && !empty($solicitacao['justificativa_recusa'])): ?>
                    <p class="text-xs mt-1">Motivo: <?= htmlspecialchars($solicitacao['justificativa_recusa']) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($status === 'aprovada' && !$devolvido): ?>
                <div class="mt-3">
                    <form id="form-devolver-animal" method="POST" action="<?= $urlBase ?>/solicitacoes/devolver">
                        <input type="hidden" name="id" value="<?= (int) $solicitacao['solicitacao_id'] ?>">
                        <button type="button"
                                onclick="abrirModalConfirmacao('Registrar devolução', 'Confirmar que <?= htmlspecialchars(addslashes($solicitacao['adotante_nome'])) ?> devolveu <?= htmlspecialchars(addslashes($solicitacao['animal_nome'])) ?>? O animal volta pro catálogo e o adotante recebe uma advertência por inadimplência (RN 12/14).', function () { document.getElementById('form-devolver-animal').submit(); }, 'Registrar Devolução', 'Cancelar')"
                                class="w-full py-2.5 bg-cinzaMarrom/40 hover:bg-cinzaMarrom/60 text-text-dark font-bold text-xs sm:text-sm rounded-2xl border-2 border-preto dark:border-cinzaMarrom transition">
                            Registrar Devolução
                        </button>
                    </form>
                </div>
            <?php endif; ?>
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
