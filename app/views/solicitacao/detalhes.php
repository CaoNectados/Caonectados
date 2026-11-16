<?php
$solicitacao = $solicitacao ?? [];
require_once __DIR__ . '/../templates/header.php';
echo renderVoltar('/solicitacoes');

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$status = $solicitacao['status_solicitacao'] ?? 'pendente';
$finalizada = in_array($status, ['aprovada', 'reprovada', 'cancelada'], true);

$tipoMoradiaLabels = ['casa' => 'Casa', 'apartamento' => 'Apartamento', 'sitio' => 'Sítio', 'outro' => 'Outro'];
$tamanhoLabels = ['pequeno' => 'Pequeno', 'medio' => 'Médio', 'grande' => 'Grande'];
$espacoExternoLabels = ['nenhum' => 'Não possui quintal', 'pequeno' => 'Quintal pequeno (até 20 m²)', 'medio' => 'Quintal médio (mais de 20 até 50 m²)', 'grande' => 'Quintal grande (mais de 50 m²)'];

$detalhesAdotante = json_decode($solicitacao['adotante_detalhes'] ?? '{}', true) ?: [];
$possuiCriancas = $detalhesAdotante['possui_criancas'] ?? null;
$possuiOutrosPets = $detalhesAdotante['possui_outros_pets'] ?? null;
$espacoExterno = $detalhesAdotante['espaco_externo'] ?? null;

$idade = null;
if (!empty($solicitacao['adotante_dt_nasc'])) {
    $idade = (new DateTime())->diff(new DateTime($solicitacao['adotante_dt_nasc']))->y;
}

$caminhoFoto = $solicitacao['animal_foto'] ?? '';
$fotoAnimal = $caminhoFoto ? (preg_match('#^https?://#i', $caminhoFoto) ? $caminhoFoto : $urlBase . '/' . ltrim($caminhoFoto, '/')) : null;
$caminhoFotoAdotante = $solicitacao['adotante_foto'] ?? '';
$fotoAdotante = $caminhoFotoAdotante ? (preg_match('#^https?://#i', $caminhoFotoAdotante) ? $caminhoFotoAdotante : $urlBase . '/' . ltrim($caminhoFotoAdotante, '/')) : null;
$preferencias = $detalhesAdotante['preferencias'] ?? [];
$preferenciasLabels = ['especie' => 'Espécie', 'porte' => 'Porte', 'sexo' => 'Sexo'];
$valoresPreferencias = ['cao'=>'Cão', 'cachorro'=>'Cão', 'gato'=>'Gato', 'pequeno'=>'Pequeno', 'medio'=>'Médio', 'grande'=>'Grande', 'macho'=>'Macho', 'femea'=>'Fêmea', 'indiferente'=>'Indiferente'];
?>

<div class="min-h-[70vh] bg-background text-text-dark px-0 py-4 sm:p-6 md:p-8 flex flex-col items-center">
    <div class="w-full max-w-5xl bg-surface rounded-3xl md:rounded-[2.5rem] p-4 sm:p-8 md:p-10 shadow-sm border border-cinzaMarrom/20 transition-colors flex flex-col justify-between">

        <div>
            <!-- Cabeçalho -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-2 border-b border-cinzaMarrom/20">
                <div class="flex min-w-0 items-center gap-3">
                    <?php if ($fotoAnimal): ?>
                        <img src="<?= htmlspecialchars($fotoAnimal) ?>" alt="" class="w-12 h-12 shrink-0 rounded-2xl object-cover border border-cinzaMarrom/30">
                    <?php endif; ?>
                    <div class="min-w-0">
                        <h2 class="font-shantell text-2xl font-bold text-text-dark tracking-tight">Detalhes da Solicitação</h2>
                        <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                            <?= htmlspecialchars($solicitacao['animal_nome']) ?> • solicitante: <strong class="text-text-dark"><?= htmlspecialchars($solicitacao['adotante_nome']) ?></strong>
                        </p>
                    </div>
                </div>
                <a href="<?= $urlBase ?>/solicitacoes" class="self-start sm:self-center px-4 py-2 border-2 border-preto dark:border-cinzaMarrom rounded-xl text-xs sm:text-sm font-bold text-text-dark hover:bg-preto hover:text-white dark:hover:bg-primary transition">
                    Voltar para lista &rsaquo;
                </a>
            </div>

            <!-- Informações do solicitante -->
            <div class="mb-8">
                <div class="flex items-center gap-4 rounded-2xl border border-rosa-2/60 bg-rosa-1/25 dark:bg-primary/20 p-4 sm:p-5 mb-5">
                    <?php if ($fotoAdotante): ?>
                        <img src="<?= htmlspecialchars($fotoAdotante) ?>" alt="Foto de <?= htmlspecialchars($solicitacao['adotante_nome']) ?>" class="h-16 w-16 sm:h-20 sm:w-20 shrink-0 rounded-full object-cover border-4 border-white dark:border-cinzaMarrom shadow-md">
                    <?php else: ?>
                        <div aria-hidden="true" class="h-16 w-16 sm:h-20 sm:w-20 shrink-0 rounded-full border-4 border-white dark:border-cinzaMarrom bg-primary/20 dark:bg-roxinhoFofo flex items-center justify-center text-2xl font-bold text-primary dark:text-white">
                            <?= htmlspecialchars(mb_strtoupper(mb_substr($solicitacao['adotante_nome'] ?? '?', 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-text-muted">Perfil de adotante</p>
                        <h3 class="font-shantell text-xl sm:text-2xl font-bold text-text-dark truncate"><?= htmlspecialchars($solicitacao['adotante_nome']) ?></h3>
                        <p class="text-sm text-text-muted"><?= $idade !== null ? htmlspecialchars((string)$idade) . ' anos' : 'Idade não informada' ?></p>
                    </div>
                    <span class="hidden sm:inline-flex items-center rounded-full bg-white/80 dark:bg-preto2 px-3 py-1 text-xs font-bold text-primary dark:text-white">Solicitante</span>
                </div>
                <h3 class="text-lg font-bold text-text-dark mb-1">Informações do solicitante</h3>
                <p class="text-xs text-text-muted mb-4">Conheça a rotina e o espaço que a família oferece antes de decidir.</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="min-w-0 rounded-2xl border border-cinzaMarrom/30 bg-white/40 dark:bg-preto2/30 p-4 sm:p-5">
                        <h4 class="flex items-center gap-2 font-bold text-text-dark text-sm sm:text-base"><img src="<?= $urlBase ?>/public/assets/icons/navbar/perfil.svg" alt="" class="h-5 w-5"> Contato</h4>
                        <p class="text-xs text-text-muted font-medium">
                            <?= htmlspecialchars($solicitacao['adotante_nome']) ?><?= $idade !== null ? ", {$idade} anos" : '' ?>
                        </p>
                        <p class="text-xs text-text-muted font-medium"><?= htmlspecialchars($solicitacao['adotante_email']) ?></p>
                        <p class="mt-2 text-sm text-text-dark">Telefone: <?= htmlspecialchars($solicitacao['adotante_telefone'] ?: 'Não informado') ?></p>
                        <p class="mt-2 text-xs text-text-muted">Solicitação enviada em <?= !empty($solicitacao['data_solicitacao']) ? date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) : 'Data não informada' ?></p>
                    </div>

                    <div class="min-w-0 rounded-2xl border border-cinzaMarrom/30 bg-white/40 dark:bg-preto2/30 p-4 sm:p-5">
                        <h4 class="flex items-center gap-2 font-bold text-text-dark text-sm sm:text-base"><img src="<?= $urlBase ?>/public/assets/icons/geral/casa-rosa.svg" alt="" class="h-5 w-5"> Moradia</h4>
                        <p class="text-xs text-text-muted font-medium">
                            <?= htmlspecialchars($tipoMoradiaLabels[$solicitacao['tipo_moradia']] ?? 'Não informado') ?><?= !empty($solicitacao['adotante_regiao']) ? ', ' . htmlspecialchars($solicitacao['adotante_regiao']) : '' ?>
                        </p>
                        <p class="text-xs text-text-muted">Espaço interno: <?= htmlspecialchars($tamanhoLabels[$solicitacao['tamanho_interno_moradia']] ?? 'Não informado') ?></p>
                        <p class="text-xs text-text-muted">Espaço externo: <?= htmlspecialchars($espacoExternoLabels[$espacoExterno] ?? 'Não informado') ?></p>
                    </div>

                    <div class="min-w-0 rounded-2xl border border-cinzaMarrom/30 bg-white/40 dark:bg-preto2/30 p-4 sm:p-5">
                        <h4 class="flex items-center gap-2 font-bold text-text-dark text-sm sm:text-base"><img src="<?= $urlBase ?>/public/assets/icons/navbar/patinha.svg" alt="" class="h-5 w-5"> Convivência</h4>
                        <p class="text-xs text-text-muted">Possui crianças em casa? <?= $possuiCriancas === 'sim' ? 'Sim' : ($possuiCriancas === 'nao' ? 'Não' : 'Não informado') ?></p>
                        <p class="text-xs text-text-muted">Possui outros pets? <?= $possuiOutrosPets === 'sim' ? 'Sim' : ($possuiOutrosPets === 'nao' ? 'Não' : 'Não informado') ?></p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-cinzaMarrom/30 bg-white/40 dark:bg-preto2/30 p-4 sm:p-5">
                        <h4 class="flex items-center gap-2 font-bold text-text-dark text-sm sm:text-base"><img src="<?= $urlBase ?>/public/assets/icons/geral/patinha-coracao.svg" alt="" class="h-5 w-5"> Preferências e apresentação</h4>
                        <?php foreach ($preferenciasLabels as $campo => $rotulo): ?>
                            <?php $itens = is_array($preferencias[$campo] ?? null) ? $preferencias[$campo] : []; ?>
                            <p class="mt-1 text-xs text-text-muted"><?= $rotulo ?>: <?= htmlspecialchars($itens ? implode(', ', array_map(static fn($item) => $valoresPreferencias[$item] ?? (string)$item, $itens)) : 'Sem preferência informada') ?></p>
                        <?php endforeach; ?>
                        <p class="mt-3 text-sm text-text-dark whitespace-pre-line"><?= htmlspecialchars($solicitacao['adotante_descricao'] ?: 'Apresentação não informada.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações de triagem -->
        <?php if (!$finalizada): ?>
            <div class="pt-4 border-t border-cinzaMarrom/20 space-y-3">
                <?php if ($status === 'pendente'): ?>
                    <form method="POST" action="<?= $urlBase ?>/solicitacoes/em-analise">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="id" value="<?= (int) $solicitacao['solicitacao_id'] ?>">
                        <button type="button"
                                onclick="abrirModalConfirmacao('Confirmar adoção', 'Confirmar a adoção? As demais solicitações enviadas para este animal serão canceladas.', () => document.getElementById('form-aprovar-solicitacao').requestSubmit(), 'Confirmar', 'Voltar')"
                                class="w-full py-3 bg-verdeMusgo hover:opacity-90 text-white font-bold text-sm sm:text-base rounded-2xl border-2 border-preto dark:border-cinzaMarrom shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] dark:shadow-[3px_3px_0px_0px_rgba(255,255,255,0.2)] active:scale-95 transition">
                            Confirmar
                        </button>
                    </form>
                </div>

                <div id="containerMotivoRecusa" class="hidden mt-2 p-4 bg-branco dark:bg-preto2 border-2 border-preto dark:border-cinzaMarrom rounded-2xl shadow-inner">
                    <label for="inputMotivo" class="block text-xs sm:text-sm font-bold text-text-dark mb-2">
                        Motivo da recusa (será enviado para o solicitante):
                    </label>
                    <form method="POST" action="<?= $urlBase ?>/solicitacoes/recusar" class="flex flex-col sm:flex-row gap-2">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                    'aprovada'  => ['texto' => 'Esta solicitação foi aprovada.', 'classe' => 'bg-sucesso/10 border-sucesso/30 text-sucesso'],
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
