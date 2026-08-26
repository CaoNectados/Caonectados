<?php 
require_once __DIR__ . '/../templates/header.php';

$tipoPerfil = $_SESSION['perfil_ativo']['tipo'] ?? $_SESSION['tipo_perfil'] ?? 'adotante';
$nomeUsuario = $_SESSION['usuario']['nome'] ?? $_SESSION['usuario_nome'] ?? 'Nome de Usuário';

$fotoPerfilSessao = $_SESSION['foto_perfil'] ?? null;
$petiscosDiarios = null;

$statusSolicitacaoProtetor = null; // null = nunca solicitou virar Protetor/ONG
$tipoSolicitacaoProtetor = null;   // 'protetor' (cpf) ou 'ong' (cnpj), pra rotular a mensagem

if ($tipoPerfil === 'adotante') {
    $adotanteInfo = (new \app\repositories\AdotanteRepository())->buscarPorUsuarioId((int)$_SESSION['usuario_id']);
    if (empty($fotoPerfilSessao)) {
        $fotoPerfilSessao = $adotanteInfo['foto_perfil'] ?? null;
    }
    // RN 02: 10 petiscos/dia, contados a partir das próprias solicitações de hoje — não a
    // partir da coluna ADOTANTE.petiscos_diarios (que exigiria um job de reset à meia-noite).
    // Ver SolicitacaoAdocaoRepository::contarSolicitacoesHoje().
    $usadosHoje = !empty($adotanteInfo['adotante_id'])
        ? (new \app\repositories\SolicitacaoAdocaoRepository())->contarSolicitacoesHoje((int)$adotanteInfo['adotante_id'])
        : 0;
    $petiscosDiarios = max(0, 10 - $usadosHoje);

    // RF 20: status da solicitação de upgrade para Protetor/ONG (se houver). Reaproveita os
    // mesmos campos (validado/deletado_em) já usados pelo admin em /admin/solicitacoes.
    $solicitacaoProtetor = (new \app\repositories\ProtetorRepository())->buscarPorUsuarioId((int)$_SESSION['usuario_id']);
    if ($solicitacaoProtetor) {
        $tipoSolicitacaoProtetor = (strtolower($solicitacaoProtetor['tipo_documento'] ?? 'cpf') === 'cnpj') ? 'ONG' : 'Protetor';
        if (!empty($solicitacaoProtetor['deletado_em'])) {
            $statusSolicitacaoProtetor = 'recusada';
        } elseif (!empty($solicitacaoProtetor['validado'])) {
            $statusSolicitacaoProtetor = 'aprovada';
        } else {
            $statusSolicitacaoProtetor = 'pendente';
        }
    }
} elseif (in_array($tipoPerfil, ['protetor', 'ong'], true)) {
    $protetorInfo = (new \app\repositories\ProtetorRepository())->buscarPorUsuarioId((int)$_SESSION['usuario_id']);
    if ($protetorInfo) {
        $paginaInfo = (new \app\repositories\PaginaRepository())->buscarPorProtetorId((int)$protetorInfo['protetor_id']);
        if (empty($fotoPerfilSessao)) {
            $fotoPerfilSessao = $paginaInfo['foto_perfil'] ?? null;
        }
    }

    // RF 20 (inverso): Protetor/ONG ainda sem perfil de Adotante pode solicitar um.
    $jaEhAdotante = (new \app\repositories\AdotanteRepository())->buscarPorUsuarioId((int)$_SESSION['usuario_id']) !== null;
    // 'usuario' (sem nenhum perfil ativo) e 'administrador' caem no placeholder padrão abaixo.
}

$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

if ($tipoPerfil === 'administrador' || $tipoPerfil === 'admin') {
    $srcFoto = $urlBase . '/assets/img/logo.png';
} elseif (!empty($fotoPerfilSessao)) {
    $fotoLimpa = ltrim(trim($fotoPerfilSessao), '/');
    $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
    $srcFoto = $urlBase . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
} else {
    $srcFoto = $urlBase . '/assets/img/perfil-placeholder.png';
}

$labelsPerfil = [
    'usuario'       => 'Perfil Incompleto',
    'adotante'      => 'Adotante',
    'protetor'      => 'Protetor',
    'ong'           => 'ONG',
    'administrador' => 'Administrador',
    'admin'         => 'Administrador',
];

$tituloCabecalho = 'Perfil';
$badgeTexto = $labelsPerfil[$tipoPerfil] ?? ucfirst($tipoPerfil);
$botoes = [];

if ($tipoPerfil === 'administrador' || $tipoPerfil === 'admin') {
    $tituloCabecalho = 'Configurações';
    $badgeTexto = 'Admin';
    $botoes = [
        ['label' => 'Editar Perfil',    'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil', 'icone' => 'alternar.svg',      'action' => 'abrirModalTrocaPerfil()'],
        ['label' => 'Termos de Uso',    'icone' => 'termos.svg',        'action' => 'abrirModalTermos()'],
        ['label' => 'Relatórios',      'icone' => 'relatorios.svg',    'url' => '/admin/relatorios'],
        ['label' => 'Sair',            'icone' => 'sair.svg',          'url' => '/logout'],
    ];
} elseif (in_array($tipoPerfil, ['ong', 'protetor'], true)) {
    $botoes = [
        ['label' => 'Editar Perfil',    'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil',  'icone' => 'alternar.svg',      'action' => 'abrirModalTrocaPerfil()'],
        ['label' => 'Página ' . ucfirst($tipoPerfil), 'icone' => 'pagina.svg', 'url' => '/pagina-perfil'],
        ['label' => 'Relatórios',       'icone' => 'relatorios.svg',    'url' => '/relatorios'],
        ['label' => 'Gerenciar Animais','icone' => 'patinha.svg',       'url' => '/animal'],
        ['label' => 'Solicitações',     'icone' => 'solicitacoes.svg',  'url' => '/solicitacoes'],
        ['label' => 'Termos de Uso',    'icone' => 'termos.svg',        'action' => 'abrirModalTermos()'],
        ['label' => 'Excluir Conta',    'icone' => 'excluir.svg',       'action' => 'abrirModalExcluirConta()'],
        ['label' => 'Sair',             'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',        'icone' => 'denunciar.svg',     'url' => '/denunciar'],
        ['label' => 'Minhas Denúncias', 'icone' => 'denunciar.svg',     'url' => '/minhas-denuncias'],
        ['label' => 'Minhas Contestações', 'icone' => 'relatorios.svg', 'url' => '/minhas-contestacoes'],
    ];

    // RF 20 (inverso): sem aprovação envolvida (Adotante não passa por validação), então o
    // botão só some quando a pessoa já tem o perfil de Adotante.
    if (!$jaEhAdotante) {
        $botoes[] = ['label' => 'Torne-se Adotante', 'icone' => 'torne-se.svg', 'url' => '/onboarding/adotante'];
    }
} elseif ($tipoPerfil === 'usuario') {
    // Sem nenhum perfil ativo (ex: admin desativou todos os perfis da pessoa, ou ela nunca
    // completou o onboarding). Não existe um Adotante/Protetor "de verdade" pra editar ou
    // alternar aqui, então oferecemos só completar o onboarding de novo.
    $tituloCabecalho = 'Perfil Incompleto';
    $botoes = [
        ['label' => 'Completar Perfil', 'icone' => 'torne-se.svg', 'url' => '/onboarding'],
        ['label' => 'Termos de Uso',    'icone' => 'termos.svg',   'action' => 'abrirModalTermos()'],
        ['label' => 'Excluir Conta',    'icone' => 'excluir.svg',  'action' => 'abrirModalExcluirConta()'],
        ['label' => 'Sair',             'icone' => 'sair.svg',     'url' => '/logout'],
    ];
} else {
    $botoes = [
        ['label' => 'Editar Perfil',          'icone' => 'editar-perfil.svg', 'url' => '/perfil/editar'],
        ['label' => 'Alternar Perfil',        'icone' => 'alternar.svg',      'action' => 'abrirModalTrocaPerfil()'],
        ['label' => 'Petiscos diários',       'icone' => 'petiscos.svg',      'url' => '/petiscos', 'valor' => (int)$petiscosDiarios],
        ['label' => 'Termos de Uso',          'icone' => 'termos.svg',        'action' => 'abrirModalTermos()'],
        ['label' => 'Excluir Conta',          'icone' => 'excluir.svg',       'action' => 'abrirModalExcluirConta()'],
        ['label' => 'Sair',                   'icone' => 'sair.svg',          'url' => '/logout'],
        ['label' => 'Denunciar',              'icone' => 'denunciar.svg',     'url' => '/denunciar'],
        ['label' => 'Minhas Denúncias',       'icone' => 'denunciar.svg',     'url' => '/minhas-denuncias'],
        ['label' => 'Minhas Contestações',    'icone' => 'relatorios.svg',    'url' => '/minhas-contestacoes'],
    ];

    // RF 20: "Torne-se Protetor/ONG" só aparece como botão clicável quando ainda não há
    // solicitação em andamento ou quando ela foi recusada (reenvio). Pendente vira um aviso
    // informativo (ver banner logo abaixo do nome); aprovada não mostra nada aqui (o aviso
    // de aprovação é uma notificação, não um card no perfil) — só some o botão.
    if ($statusSolicitacaoProtetor === null) {
        $botoes[] = ['label' => 'Torne-se Protetor/ONG', 'icone' => 'torne-se.svg', 'url' => '/onboarding'];
    } elseif ($statusSolicitacaoProtetor === 'recusada') {
        $botoes[] = ['label' => 'Reenviar Solicitação', 'icone' => 'torne-se.svg', 'url' => '/onboarding'];
    }
}

$paginasBotoes = array_chunk($botoes, 6);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<div class="max-w-md mx-auto bg-background min-h-screen pb-20">
    <div class="px-6 -mt-4 pt-10 flex flex-col items-center">

        <!-- Badge de Perfil Atual (formato de fita/banner, como no protótipo) -->
        <div class="relative flex items-center justify-center mb-6">
            <img src="<?= URL_BASE ?>/assets/icons/geral/patinha-coracao.svg" class="absolute -left-5 z-10 h-10 w-10 object-contain" alt="">
            <div class="ribbon-perfil bg-roxo2 dark:bg-primary text-white font-shantell font-bold text-xl px-12 py-2 shadow-sm">
                <?= htmlspecialchars($badgeTexto) ?>
            </div>
            <img src="<?= URL_BASE ?>/assets/icons/geral/patinha-coracao.svg" class="absolute -right-5 z-10 h-10 w-10 object-contain" alt="">
        </div>

        <!-- Foto de Perfil -->
        <div class="relative mb-4">
            <div class="w-32 h-32 rounded-full border-[6px] border-roxoApagado dark:border-preto3 overflow-hidden bg-surface dark:bg-preto2 flex items-center justify-center shadow-md">
                <img src="<?= htmlspecialchars($srcFoto) ?>"
                     id="foto-perfil-display"
                     alt="Foto de perfil"
                     class="w-full h-full rounded-full <?= ($tipoPerfil === 'administrador' || $tipoPerfil === 'admin') ? 'object-contain p-2 bg-white' : 'object-cover' ?>"
                     onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
            </div>

            <?php if (!in_array($tipoPerfil, ['administrador', 'admin', 'usuario'], true)): ?>
                <button type="button"
                        onclick="document.getElementById('input-foto-direta').click()"
                        class="absolute top-0 right-0 bg-surface dark:bg-preto1 p-2 rounded-full shadow border border-rosa-2 text-text-muted hover:bg-rosa-1 transition hover:scale-105 cursor-pointer"
                        title="Alterar foto">
                    ✏️
                </button>
                <input type="file" id="input-foto-direta" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden" onchange="processarSelecaoFoto(event)">
            <?php endif; ?>
        </div>

        <!-- Nome do Usuário -->
        <h2 class="font-shantell text-2xl font-bold text-text-dark dark:text-white text-center mb-6">
            <?= htmlspecialchars($nomeUsuario) ?>
        </h2>

        <?php if ($statusSolicitacaoProtetor !== null): ?>
            <!-- RF 20: Status da solicitação de upgrade para Protetor/ONG -->
            <?php
                $bannerConfig = [
                    'pendente' => [
                        'classes' => 'bg-amarelo/30 dark:bg-preto2 border-amarelo/60 text-text-dark',
                        'icone'   => '🕓',
                        'texto'   => "Sua solicitação para se tornar {$tipoSolicitacaoProtetor} está em análise (pendente).",
                    ],
                    'recusada' => [
                        'classes' => 'bg-erro/10 border-erro/30 text-erro',
                        'icone'   => '❌',
                        'texto'   => "Sua solicitação para se tornar {$tipoSolicitacaoProtetor} foi recusada. Verifique seu e-mail para mais detalhes e reenvie os dados corrigidos.",
                    ],
                    // 'aprovada' não tem banner aqui — o aviso de aprovação vai virar uma
                    // notificação (sistema de notificações), não um card fixo no perfil.
                ][$statusSolicitacaoProtetor] ?? null;
            ?>
            <?php if ($bannerConfig): ?>
                <div class="w-full flex items-start gap-2 rounded-2xl border px-4 py-3 mb-6 text-sm font-poppins font-medium <?= $bannerConfig['classes'] ?>">
                    <span class="text-lg leading-none"><?= $bannerConfig['icone'] ?></span>
                    <span class="text-text-dark dark:text-white"><?= htmlspecialchars($bannerConfig['texto']) ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Container de Ações -->
        <div class="w-full bg-gray-200 dark:bg-preto1 rounded-3xl p-5 shadow-inner relative border border-gray-300 dark:border-preto3">
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="text-xl">⚙️</span>
                <h3 class="font-bold text-lg text-text-dark dark:text-white"><?= htmlspecialchars($tituloCabecalho) ?></h3>
            </div>

            <!-- Grid de Botões -->
            <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide hide-scroll gap-4" id="slider-botoes">
                <?php foreach ($paginasBotoes as $pagina): ?>
                    <div class="min-w-full snap-center grid grid-cols-3 gap-3 auto-rows-max">
                        <?php foreach ($pagina as $botao): ?>
                            <?php if (isset($botao['action'])): ?>
                                <button type="button" onclick="<?= htmlspecialchars($botao['action']) ?>" class="flex flex-col items-center justify-center bg-branco dark:bg-preto2 rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-28 cursor-pointer border border-rosa-2 dark:border-preto3 w-full">
                                    <img src="<?= $urlBase ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= htmlspecialchars($botao['label']) ?>" class="h-11 w-11 mb-2 object-contain">
                                    <?php if (isset($botao['valor'])): ?>
                                        <span class="text-sm font-bold leading-none text-primary dark:text-roxinhoFofo mb-0.5"><?= (int)$botao['valor'] ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs font-bold leading-tight text-text-dark dark:text-white"><?= htmlspecialchars($botao['label']) ?></span>
                                </button>
                            <?php else: ?>
                                <a href="<?= $urlBase . $botao['url'] ?>" class="flex flex-col items-center justify-center bg-branco dark:bg-preto2 rounded-2xl p-3 shadow-sm hover:shadow-md transition text-center h-28 border border-rosa-2 dark:border-preto3">
                                    <img src="<?= $urlBase ?>/assets/icons/perfil/<?= $botao['icone'] ?>" alt="<?= htmlspecialchars($botao['label']) ?>" class="h-11 w-11 mb-2 object-contain">
                                    <?php if (isset($botao['valor'])): ?>
                                        <span class="text-sm font-bold leading-none text-primary dark:text-roxinhoFofo mb-0.5"><?= (int)$botao['valor'] ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs font-bold leading-tight text-text-dark dark:text-white"><?= htmlspecialchars($botao['label']) ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Indicadores do Carrossel -->
            <?php if (count($paginasBotoes) > 1): ?>
                <div class="flex justify-center gap-2 mt-4">
                    <?php foreach ($paginasBotoes as $index => $pagina): ?>
                        <div class="w-2.5 h-2.5 rounded-full <?= $index === 0 ? 'bg-primary dark:bg-roxinhoFofo' : 'bg-text-muted' ?> indicador-pagina transition-all duration-300" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="btn-prev" class="absolute inset-y-0 left-1 flex items-center px-2 cursor-pointer text-text-muted hover:text-text-dark dark:hover:text-white font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-95">&lsaquo;</button>
                <button type="button" id="btn-next" class="absolute inset-y-0 right-1 flex items-center px-2 cursor-pointer text-text-muted hover:text-text-dark dark:hover:text-white font-bold text-3xl drop-shadow-md z-10 transition-transform active:scale-95">&rsaquo;</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Trocar Perfil (Sem fotos listadas, apenas seleção de texto/papel) -->
<div id="modalTrocarPerfil" class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-surface dark:bg-preto1 rounded-3xl shadow-xl w-full max-w-sm p-6 transform transition-all scale-100 border border-rosa-3">
        <div class="flex justify-between items-center mb-4 border-b border-cinzaMarrom/20 pb-3">
            <h3 class="text-xl font-shantell font-bold text-text-dark dark:text-white">Trocar Perfil</h3>
            <button onclick="fecharModalTrocaPerfil()" class="text-text-muted hover:text-erro text-3xl font-bold transition">&times;</button>
        </div>
        
        <p class="text-sm font-poppins text-text-muted mb-5">Selecione o perfil desejado para navegar:</p>

        <div class="space-y-3">
            <?php
            // O pseudo-perfil "usuario" (estado transitório pré-onboarding) nunca é uma
            // opção real de navegação — filtrado da listagem de troca de perfil.
            //
            // Lê de perfis_ativos (refrescado do banco a cada requisição autenticada em
            // Controller::sincronizarSessaoComBanco()), não de $_SESSION['perfis'] — esse
            // último só é montado uma vez, no login (AuthService::iniciarSessao), e nunca
            // mais é atualizado depois. Um perfil concedido durante a sessão (ex: RF 20 —
            // virar Protetor/ONG ou Adotante sem precisar logar de novo) nunca aparecia
            // aqui pra trocar, mesmo já valendo pra tudo mais no sistema.
            $perfis = array_values(array_filter($_SESSION['perfis_ativos'] ?? [], fn($tipo) => $tipo !== 'usuario'));
            $perfis = array_map(fn($tipo) => ['tipo' => $tipo], $perfis);
            if (!empty($perfis)):
                foreach ($perfis as $p):
                    $isCurrent = (isset($_SESSION['perfil_ativo']['tipo']) && $_SESSION['perfil_ativo']['tipo'] === $p['tipo']);
            ?>
                <div class="flex items-center justify-between p-4 rounded-xl transition border <?= $isCurrent ? 'bg-rosa-1/40 border-primary dark:bg-preto2' : 'bg-branco dark:bg-preto2 border-cinzaMarrom/30' ?>">
                    <div>
                        <p class="font-bold text-text-dark dark:text-white"><?= htmlspecialchars($labelsPerfil[$p['tipo']] ?? ucfirst($p['tipo'])) ?></p>
                    </div>
                    <?php if ($isCurrent): ?>
                        <span class="text-xs bg-primary text-white font-bold px-3 py-1.5 rounded-full shadow-sm">Ativo</span>
                    <?php else: ?>
                        <form action="<?= $urlBase ?>/perfil/trocar" method="POST" class="m-0">
                            <input type="hidden" name="tipo" value="<?= htmlspecialchars($p['tipo']) ?>">
                            <button type="submit" class="bg-text-dark hover:opacity-90 dark:bg-primary text-white text-xs font-bold px-4 py-2 rounded-full transition shadow cursor-pointer">
                                Acessar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php 
                endforeach; 
            else: 
            ?>
                <div class="p-4 bg-erro/10 text-erro border border-erro/20 rounded-xl text-center text-sm font-medium">
                    Nenhum outro perfil disponível.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Cropper Direto -->
<div id="modal-cropper-direto" class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 hidden">
    <div class="bg-surface dark:bg-preto1 rounded-3xl max-w-sm w-full p-6 flex flex-col items-center shadow-2xl border border-rosa-3">
        <h3 class="font-shantell text-xl font-bold mb-1 text-text-dark dark:text-white">Ajustar Foto</h3>
        <p class="text-xs text-text-muted mb-4 text-center">Enquadre sua foto de perfil perfeitamente.</p>
        
        <div class="w-full h-64 bg-branco dark:bg-preto2 rounded-2xl overflow-hidden mb-4 flex items-center justify-center border border-cinzaMarrom/30">
            <img id="imagem-cropper-direto" src="" alt="Recorte" class="max-w-full max-h-full">
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharCropperDireto()" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition">Cancelar</button>
            <button type="button" onclick="salvarFotoDireta()" id="btn-salvar-foto-direta" class="flex-1 bg-rosaAlerta text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">Aplicar</button>
        </div>
    </div>
</div>

<!-- Modal Termos de Uso -->
<div id="modalTermos" class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-surface dark:bg-preto1 rounded-3xl shadow-xl w-full max-w-lg max-h-[85vh] flex flex-col border border-rosa-3">
        <div class="flex justify-between items-center p-6 pb-3 border-b border-cinzaMarrom/20">
            <h3 class="text-xl font-shantell font-bold text-text-dark dark:text-white">Termos de Uso</h3>
            <button onclick="fecharModalTermos()" class="text-text-muted hover:text-erro text-3xl font-bold transition cursor-pointer">&times;</button>
        </div>

        <div class="px-6 py-4 overflow-y-auto text-sm font-poppins text-text-dark dark:text-white space-y-4">
            <p>Bem-vindo(a) ao CãoNectados! Ao utilizar nossa plataforma, você concorda com os termos abaixo.</p>

            <div>
                <h4 class="font-bold mb-1">1. Objetivo da plataforma</h4>
                <p class="text-text-muted">O CãoNectados conecta adotantes a ONGs e protetores independentes, facilitando o processo de adoção responsável de animais.</p>
            </div>

            <div>
                <h4 class="font-bold mb-1">2. Cadastro e veracidade das informações</h4>
                <p class="text-text-muted">O usuário é responsável por fornecer informações verdadeiras e manter seus dados atualizados. ONGs e protetores devem comprovar sua identidade e/ou documentação para validação da conta.</p>
            </div>

            <div>
                <h4 class="font-bold mb-1">3. Conduta e responsabilidade</h4>
                <p class="text-text-muted">É proibido o uso da plataforma para maus-tratos, abandono, fraude ou qualquer conduta que prejudique os animais ou outros usuários. Contas podem ser advertidas, suspensas ou excluídas em caso de violação.</p>
            </div>

            <div>
                <h4 class="font-bold mb-1">4. Petiscos e solicitações de adoção</h4>
                <p class="text-text-muted">Adotantes recebem uma quantidade diária de "petiscos", utilizados para demonstrar interesse em animais disponíveis. O envio de um petisco não garante a adoção, que segue critérios do responsável pelo animal.</p>
            </div>

            <div>
                <h4 class="font-bold mb-1">5. Privacidade</h4>
                <p class="text-text-muted">Os dados pessoais informados são utilizados exclusivamente para o funcionamento da plataforma e não são compartilhados com terceiros sem consentimento, exceto quando exigido por lei.</p>
            </div>

            <div>
                <h4 class="font-bold mb-1">6. Exclusão de conta</h4>
                <p class="text-text-muted">O usuário pode solicitar a exclusão de sua conta a qualquer momento através do menu de perfil. A exclusão desativa o acesso e os dados vinculados de acordo com a política de retenção da plataforma.</p>
            </div>
        </div>

        <div class="p-6 pt-4">
            <button type="button" onclick="fecharModalTermos()" class="w-full bg-text-dark hover:opacity-90 dark:bg-primary text-white py-2.5 rounded-xl font-bold text-sm transition cursor-pointer">
                Entendi
            </button>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão de Conta -->
<div id="modalExcluirConta" class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-surface dark:bg-preto1 rounded-3xl shadow-xl w-full max-w-sm p-6 border border-rosa-3">
        <div class="flex flex-col items-center text-center">
            <span class="text-4xl mb-3">⚠️</span>
            <h3 class="text-xl font-shantell font-bold text-text-dark dark:text-white mb-2">Excluir conta?</h3>
            <p class="text-sm font-poppins text-text-muted mb-6">
                Essa ação encerrará sua sessão e desativará sua conta. Você perderá o acesso à plataforma com esse usuário. Deseja continuar?
            </p>
        </div>

        <div class="flex gap-3 w-full">
            <button type="button" onclick="fecharModalExcluirConta()" id="btn-cancelar-exclusao" class="flex-1 bg-cinzaMarrom/30 text-text-dark dark:text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-80 transition cursor-pointer">Cancelar</button>
            <button type="button" onclick="confirmarExclusaoConta()" id="btn-confirmar-exclusao" class="flex-1 bg-erro text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition cursor-pointer">Sim, excluir</button>
        </div>
    </div>
</div>

<style>
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .cropper-view-box, .cropper-face { border-radius: 50%; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    function abrirModalTrocaPerfil() {
        document.getElementById('modalTrocarPerfil').classList.remove('hidden');
    }
    function fecharModalTrocaPerfil() {
        document.getElementById('modalTrocarPerfil').classList.add('hidden');
    }

    function abrirModalTermos() {
        document.getElementById('modalTermos').classList.remove('hidden');
    }
    function fecharModalTermos() {
        document.getElementById('modalTermos').classList.add('hidden');
    }

    function abrirModalExcluirConta() {
        document.getElementById('modalExcluirConta').classList.remove('hidden');
    }
    function fecharModalExcluirConta() {
        document.getElementById('modalExcluirConta').classList.add('hidden');
    }

    async function confirmarExclusaoConta() {
        const btnConfirmar = document.getElementById('btn-confirmar-exclusao');
        const btnCancelar = document.getElementById('btn-cancelar-exclusao');
        btnConfirmar.disabled = true;
        btnCancelar.disabled = true;
        btnConfirmar.innerText = 'Excluindo...';

        try {
            const response = await fetch('<?= $urlBase ?>/perfil/excluir', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.status === 'sucesso') {
                window.location.href = result.redirect_url || '<?= $urlBase ?>/login';
                return;
            }

            mostrarModalFeedback('erro', result.mensagem || 'Não foi possível excluir a conta.');
        } catch (err) {
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        } finally {
            btnConfirmar.disabled = false;
            btnCancelar.disabled = false;
            btnConfirmar.innerText = 'Sim, excluir';
            fecharModalExcluirConta();
        }
    }

    const slider = document.getElementById('slider-botoes');
    const indicadores = document.querySelectorAll('.indicador-pagina');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    if (slider) {
        slider.addEventListener('scroll', () => {
            let index = Math.round(slider.scrollLeft / slider.clientWidth);
            indicadores.forEach((ind, i) => {
                if (i === index) {
                    ind.classList.remove('bg-text-muted');
                    ind.classList.add('bg-primary', 'dark:bg-roxinhoFofo', 'scale-110');
                } else {
                    ind.classList.remove('bg-primary', 'dark:bg-roxinhoFofo', 'scale-110');
                    ind.classList.add('bg-text-muted');
                }
            });
        });

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                let curr = Math.round(slider.scrollLeft / slider.clientWidth);
                slider.scrollTo({ 
                    left: curr >= indicadores.length - 1 ? 0 : (curr + 1) * slider.clientWidth, 
                    behavior: 'smooth' 
                });
            });
        }
        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                let curr = Math.round(slider.scrollLeft / slider.clientWidth);
                slider.scrollTo({ 
                    left: curr === 0 ? (indicadores.length - 1) * slider.clientWidth : (curr - 1) * slider.clientWidth, 
                    behavior: 'smooth' 
                });
            });
        }
    }

    let cropperInstancia = null;

    function processarSelecaoFoto(event) {
        const files = event.target.files;
        if (files && files.length > 0) {
            if (files[0].size > 5 * 1024 * 1024) {
                mostrarModalFeedback('erro', 'A imagem excede o tamanho máximo de 5MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgEl = document.getElementById('imagem-cropper-direto');
                imgEl.src = e.target.result;
                document.getElementById('modal-cropper-direto').classList.remove('hidden');

                if (cropperInstancia) cropperInstancia.destroy();
                cropperInstancia = new Cropper(imgEl, {
                    aspectRatio: 1, 
                    viewMode: 1, 
                    dragMode: 'move', 
                    autoCropArea: 0.8
                });
            };
            reader.readAsDataURL(files[0]);
        }
    }

    function fecharCropperDireto() {
        document.getElementById('modal-cropper-direto').classList.add('hidden');
        if (cropperInstancia) { 
            cropperInstancia.destroy(); 
            cropperInstancia = null; 
        }
        document.getElementById('input-foto-direta').value = '';
    }

    async function salvarFotoDireta() {
        if (!cropperInstancia) return;
        const btn = document.getElementById('btn-salvar-foto-direta');
        btn.disabled = true; 
        btn.innerText = 'Enviando...';

        const base64Image = cropperInstancia.getCroppedCanvas({ width: 400, height: 400 }).toDataURL('image/png');
        const formData = new FormData(); 
        formData.append('foto_cortada', base64Image);

        try {
            const response = await fetch('<?= $urlBase ?>/perfil/atualizar-foto', { 
                method: 'POST', 
                body: formData 
            });
            const result = await response.json();

            if (result.status === 'sucesso' || result.sucesso === true) {
                document.getElementById('foto-perfil-display').src = base64Image;
                mostrarModalFeedback('sucesso', result.mensagem || 'Foto atualizada com sucesso!');
                fecharCropperDireto();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                mostrarModalFeedback('erro', result.mensagem || 'Falha ao atualizar foto.');
            }
        } catch (err) {
            mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
        } finally {
            btn.disabled = false; 
            btn.innerText = 'Aplicar';
        }
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>