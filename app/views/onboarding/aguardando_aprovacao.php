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

<div class="max-w-md mx-auto p-6 text-center mt-10 bg-surface dark:bg-preto1 rounded-3xl shadow-md border border-rosa-2 dark:border-preto3 transition-colors">

    <?php if ($recusado): ?>
        <!-- ESTADO: RECUSADO -->
        <!-- Caminho reservado para a imagem/gif de "recusado" — troque o arquivo em
             public/assets/img/solicitacao-recusada.gif quando tiver um pronto. -->
        <img src="<?= URL_BASE ?>/assets/img/solicitacao-recusada.gif" alt="" class="w-32 h-32 mx-auto mb-4 object-contain" onerror="this.style.display='none';">

        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white mb-2">Solicitação Recusada</h1>

        <p class="text-sm text-text-muted mb-6">
            Não desanime! Estamos quase lá — só precisamos ajustar alguns detalhes para liberar o seu acesso.
        </p>

        <div class="bg-erro/5 border border-erro/20 rounded-2xl p-4 mb-6 text-left">
            <p class="text-xs font-bold text-erro uppercase tracking-wider mb-1">Motivo informado pela equipe:</p>
            <p class="text-sm text-text-dark dark:text-white font-medium italic">"<?= htmlspecialchars($motivoRecusa ?? 'Documento ilegível ou dados divergentes.') ?>"</p>
        </div>

        <a href="<?= URL_BASE . $rotaEdicao ?>" class="btn-primario w-full py-3 rounded-xl mb-4 text-center block font-bold text-white shadow-md">
            ✏️ Corrigir e Enviar Novamente
        </a>

    <?php else: ?>
        <!-- ESTADO: PENDENTE EM ANÁLISE -->
        <!-- Caminho reservado para a imagem/gif de "aguardando" — troque o arquivo em
             public/assets/img/aguardando-aprovacao.gif quando tiver um pronto. -->
        <img src="<?= URL_BASE ?>/assets/img/aguardando-aprovacao.gif" alt="" class="w-36 h-36 mx-auto mb-4 object-contain" onerror="this.style.display='none';">

        <h1 class="font-shantell text-2xl font-bold text-text-dark dark:text-white mb-2">Aguardando Aprovação</h1>

        <p class="text-sm text-text-dark dark:text-white font-medium mb-1">
            Parabéns por dar esse passo! 🐾
        </p>
        <p class="text-sm text-text-muted mb-6">
            Sua solicitação já está com a nossa equipe. Assim que for validada, você receberá um e-mail e terá acesso completo para começar a transformar a vida de muitos animais.
        </p>

        <div class="bg-branco dark:bg-preto2 border border-rosa-2 dark:border-preto3 rounded-2xl p-4 mb-6 text-xs text-text-muted text-left space-y-1.5 shadow-inner">
            <p class="font-bold text-text-dark dark:text-white">Etapas da análise:</p>
            <p>1. Conferência do documento enviado.</p>
            <p>2. Validação dos dados cadastrais.</p>
            <p>3. Liberação para cadastro de animais e gestão.</p>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= URL_BASE ?>/logout" class="inline-block text-xs font-semibold text-text-muted hover:text-text-dark dark:hover:text-white underline">
            Sair da conta
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>