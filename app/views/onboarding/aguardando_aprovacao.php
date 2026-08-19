<?php
/** @var bool|null $recusado */
/** @var bool|null $validado */
/** @var string|null $motivoRecusa */
/** @var string|null $tipoDocumento */

$recusado = $recusado ?? false;
$validado = $validado ?? false;
$tipoDoc = strtolower($tipoDocumento ?? 'cpf');
$rotaEdicao = ($tipoDoc === 'cnpj') ? '/onboarding/ong' : '/onboarding/protetor';

require_once __DIR__ . '/../templates/header.php';
?>

<div class="max-w-md mx-auto p-6 text-center mt-10 bg-surface rounded-3xl shadow-sm border border-cinzaMarrom/20">
    
    <?php if ($recusado): ?>
        <!-- ESTADO: RECUSADO -->
        <div class="w-20 h-20 bg-erro/10 text-erro rounded-full flex items-center justify-center mx-auto mb-5 text-3xl">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>

        <h1 class="font-shantell text-2xl font-black text-text-dark mb-2">Solicitação Recusada</h1>
        
        <p class="text-sm text-text-muted mb-6">
            Infelizmente sua solicitação para perfil de Protetor/ONG não pôde ser aprovada no momento.
        </p>

        <div class="bg-erro/5 border border-erro/20 rounded-2xl p-4 mb-6 text-left">
            <p class="text-xs font-bold text-erro uppercase tracking-wider mb-1">Motivo informado pela equipe:</p>
            <p class="text-sm text-text-dark font-medium italic">"<?= htmlspecialchars($motivoRecusa ?? 'Documento ilegível ou dados divergentes.') ?>"</p>
        </div>

        <a href="<?= URL_BASE . $rotaEdicao ?>" class="btn-primario w-full py-3 rounded-xl mb-4 text-center block font-bold text-white shadow-md">
            <i class="fa-solid fa-pen-to-square mr-1"></i> Corrigir e Enviar Novamente
        </a>

    <?php else: ?>
        <!-- ESTADO: PENDENTE EM ANÁLISE -->
        <div class="w-20 h-20 bg-laranja-1/10 text-laranja-1 rounded-full flex items-center justify-center mx-auto mb-5 text-3xl">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>

        <h1 class="font-shantell text-2xl font-black text-text-dark mb-2">Aguardando Aprovação</h1>
        
        <p class="text-sm text-text-muted mb-6">
            Sua solicitação está em análise pela equipe administrativa. Assim que for validada, você receberá um e-mail e terá acesso completo.
        </p>

        <div class="bg-branco border border-cinzaMarrom/20 rounded-2xl p-4 mb-6 text-xs text-text-muted text-left space-y-1.5 shadow-inner">
            <p class="font-bold text-text-dark">Etapas da análise:</p>
            <p>1. Conferência do documento enviado.</p>
            <p>2. Validação dos dados cadastrais.</p>
            <p>3. Liberação para cadastro de animais e gestão.</p>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= URL_BASE ?>/logout" class="inline-block text-xs font-semibold text-text-muted hover:text-text-dark underline">
            Sair da conta
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>