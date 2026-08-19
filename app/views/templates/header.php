<?php
require_once __DIR__ . '/../../helpers/ViewHelper.php';

// ------------------------------------------------------------------
// LÓGICA DE AUTENTICAÇÃO E PERFIL
// ------------------------------------------------------------------
$titulo = $titulo ?? 'CãoNectados';
$tipoPerfil = $_SESSION['tipo_perfil'] ?? null;
$validado = $_SESSION['validado'] ?? false;
$statusConta = $_SESSION['status_conta'] ?? 'ativo';

$uriAtual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$menuItens = [];

// ==================================================================
// ITENS DO MENU
// ==================================================================

$menuItens[] = ['url' => URL_BASE . '/', 'label' => 'Home', 'icone' => 'home.svg'];

if ($tipoPerfil === null) {
    $menuItens[] = ['url' => URL_BASE . '/cadastro', 'label' => 'Cadastre-se', 'icone' => 'cadastro.svg'];
}

// ---------------- PERFIL: ADMINISTRADOR ----------------
if ($tipoPerfil === 'administrador') {
    $menuItens[] = ['url' => URL_BASE . '/pesquisar', 'label' => 'Pesquisar', 'icone' => 'pesquisar.svg', 'apenas_desktop' => true];
    $menuItens[] = ['url' => URL_BASE . '/admin/dashboard', 'label' => 'Dashboard', 'icone' => 'dashboard.svg'];
    $menuItens[] = ['url' => URL_BASE . '/admin/solicitacoes', 'label' => 'Solicitações Ongs e Protetores', 'icone' => 'solicitacoes.png'];
    $menuItens[] = ['url' => URL_BASE . '/admin/gerenciar-usuarios', 'label' => 'Gerenciar Usuários', 'icone' => 'usuarios.svg'];
    $menuItens[] = ['url' => URL_BASE . '/admin/regiao', 'label' => 'Gerenciar Regiões', 'icone' => 'bairros.svg'];
    $menuItens[] = ['url' => URL_BASE . '/admin/gerenciar-especies-racas', 'label' => 'Gerenciar Espécies e Raças', 'icone' => 'gerenciar-animais.png'];
    $menuItens[] = ['url' => URL_BASE . '/admin/denuncias', 'label' => 'Denúncias', 'icone' => 'denuncia.svg'];
    $menuItens[] = ['url' => URL_BASE . '/admin/auditoria-logs', 'label' => 'Auditoria e Logs', 'icone' => 'auditoria.svg'];
    $menuItens[] = ['url' => URL_BASE . '/perfil', 'label' => 'Perfil', 'icone' => 'perfil.svg', 'apenas_desktop' => true];

// ---------------- PERFIL: PROTETOR OU ONG ----------------
} elseif ($tipoPerfil === 'protetor' || $tipoPerfil === 'ong') {

    // Se estiver validado (1/true), exibe todas as opções do painel
    if ($validado === true || $validado === 1 || $validado === '1') {
        $menuItens[] = ['url' => URL_BASE . '/feed',                 'label' => 'Feed',                   'icone' => 'dashboard.svg',        'apenas_desktop' => true];
        $menuItens[] = ['url' => URL_BASE . '/pesquisar',            'label' => 'Pesquisar',              'icone' => 'pesquisar.svg',        'apenas_desktop' => true];
        $menuItens[] = ['url' => URL_BASE . '/chats',                'label' => 'Chat',                   'icone' => 'chat.svg',             'apenas_desktop' => true];
        $menuItens[] = ['url' => URL_BASE . '/perfil',               'label' => 'Meu Perfil',             'icone' => 'perfil.svg',           'apenas_desktop' => true];
        $menuItens[] = ['url' => URL_BASE . '/gerenciar-animais',    'label' => 'Gerenciar Animais',      'icone' => 'gerenciar-animais.png'];
        $menuItens[] = ['url' => URL_BASE . '/solicitacoes',         'label' => 'Solicitações Recebidas', 'icone' => 'solicitacoes.png'];
        $menuItens[] = ['url' => URL_BASE . '/pagina-protetor',      'label' => 'Página',                 'icone' => 'pagina.svg'];
    } else {
        // Se estiver aguardando aprovação, mantém apenas a Home e a tela de status no menu
        $menuItens[] = ['url' => URL_BASE . '/aguardando-aprovacao', 'label' => 'Aguardando Aprovação',   'icone' => 'auditoria.svg'];
    }

// ---------------- PERFIL: ADOTANTE ----------------
} elseif ($tipoPerfil === 'adotante') {
    $menuItens[] = ['url' => URL_BASE . '/feed',      'label' => 'Feed',      'icone' => 'dashboard.svg', 'apenas_desktop' => true];
    $menuItens[] = ['url' => URL_BASE . '/pesquisar', 'label' => 'Pesquisar', 'icone' => 'pesquisar.svg', 'apenas_desktop' => true];
    $menuItens[] = ['url' => URL_BASE . '/chats',     'label' => 'Chat',      'icone' => 'chat.svg',      'apenas_desktop' => true];
    $menuItens[] = ['url' => URL_BASE . '/perfil',    'label' => 'Meu Perfil','icone' => 'perfil.svg',    'apenas_desktop' => true];

// ---------------- PERFIL: USUÁRIO GENÉRICO ----------------
} elseif ($tipoPerfil === 'usuario') {
    $menuItens[] = ['url' => URL_BASE . '/onboarding', 'label' => 'Completar Perfil', 'icone' => 'perfil.svg'];
}

$estaLogado = $tipoPerfil !== null;
$itemAuth   = $estaLogado
    ? ['url' => URL_BASE . '/logout', 'label' => 'Sair',   'icone' => 'logout.svg']
    : ['url' => URL_BASE . '/login',  'label' => 'Entrar', 'icone' => 'login2.svg'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>

    <script>
        (function() {
            var preferenciaSalva = localStorage.getItem('caonectados-tema');
            var prefereSistemaEscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;

            var deveUsarEscuro = preferenciaSalva ?
                preferenciaSalva === 'escuro' :
                prefereSistemaEscuro;

            if (deveUsarEscuro) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <?php include __DIR__ . '/tailwind_config.php'; ?>
    <link rel="icon" type="image/png" href="<?= e(URL_BASE) ?>/assets/img/logo.png">
</head>

<body class="flex h-[100dvh] overflow-hidden flex-col bg-background dark:bg-corFundo-escuro transition-colors">
    <header class="sticky top-0 z-50 h-16 bg-primary shadow-md">
        <div class="relative flex h-full items-center gap-3 px-4 sm:px-6">

            <a href="<?= URL_BASE ?>/" id="logo-header" class="flex items-center shrink-0 gap-2" aria-label="CãoNectados — página inicial">
                <img src="<?= e(URL_BASE) ?>/assets/img/logo.png"
                    alt="CãoNectados"
                    class="h-16 w-auto sm:h-18 lg:h-18">
                <span class="hidden whitespace-nowrap font-shantell text-2xl font-bold leading-tight text-white lg:inline">
                    CãoNectados
                </span>
            </a>

            <div class="pointer-events-none absolute inset-x-0 flex items-center justify-center px-24 lg:hidden">
                <span class="w-full text-center font-shantell text-sm sm:text-base font-bold leading-tight text-white break-words line-clamp-2">
                    <?= e($titulo) ?>
                </span>
            </div>

            <div class="ml-4 hidden min-w-0 flex-1 justify-end lg:flex">
                <span class="max-w-[24rem] break-words text-right font-shantell text-xl font-bold leading-tight text-white xl:text-2xl">
                    <?= e($titulo) ?>
                </span>
            </div>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button type="button"
                    class="relative rounded-lg p-2 text-white transition hover:bg-white/20"
                    aria-label="Notificações">
                    <img src="<?= e(URL_BASE) ?>/assets/icons/navbar/notificacao.svg"
                        alt=""
                        aria-hidden="true"
                        class="h-8 w-8">
                </button>

                <button type="button" id="botao-menu-mobile"
                    class="rounded-lg p-2 text-white transition hover:bg-white/20 lg:hidden"
                    aria-expanded="false" aria-controls="menu-mobile"
                    aria-label="Abrir menu de navegação">
                    <svg id="icone-menu-hamburguer" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="icone-menu-fechar" class="hidden h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </div>

        <nav id="menu-mobile" hidden
            class="absolute inset-x-0 top-16 z-50 max-h-[calc(100vh-4rem)] origin-top overflow-y-auto bg-primary px-6 pb-10 pt-4 text-white shadow-lg transition duration-300 ease-out opacity-0 -translate-y-2 pointer-events-none lg:hidden"
            aria-label="Navegação principal (mobile)">
            <ul>
                <?php foreach ($menuItens as $item): ?>
                    <?php
                    if (!empty($item['apenas_desktop'])) continue;

                    $ehAtivo = false;
                    if ($item['url'] === URL_BASE . '/') {
                        $ehAtivo = ($uriAtual === '/' || preg_match('/public\/?(?:index\.php)?$/', $uriAtual));
                    } else {
                        $ehAtivo = (strpos($uriAtual, parse_url($item['url'], PHP_URL_PATH)) !== false);
                    }
                    $classeTexto = $ehAtivo ? 'text-rosaAlerta underline decoration-2 underline-offset-4' : 'text-white';
                    ?>
                    <li>
                        <a href="<?= e($item['url']) ?>"
                            class="nav-link-mobile border-white/20 no-underline">
                            <?= renderIconeMenu($item['icone'], $item['label']) ?>
                            <span class="<?= $classeTexto ?>"><?= e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>

                <li>
                    <button type="button"
                        data-theme-toggle
                        class="nav-link-mobile w-full appearance-none bg-transparent border-white/20 text-white no-underline"
                        aria-label="Alternar para modo escuro">
                        <svg class="block h-6 w-6 dark:hidden" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="4.5" />
                            <path stroke-linecap="round"
                                d="M12 3v2m0 14v2M4.2 4.2l1.4 1.4m12.8 12.8 1.4 1.4M3 12h2m14 0h2M4.2 19.8l1.4-1.4m12.8-12.8 1.4-1.4" />
                        </svg>

                        <svg class="hidden h-6 w-6 dark:block" fill="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.5 14.7A8.5 8.5 0 0 1 9.3 3.5a.6.6 0 0 0-.8-.8A10 10 0 1 0 21.3 15.5a.6.6 0 0 0-.8-.8Z" />
                        </svg>

                        <span>Tema</span>
                    </button>
                </li>

                <li>
                    <?php
                    $pathAuth = parse_url($itemAuth['url'], PHP_URL_PATH);
                    $authAtivo = (strpos($uriAtual, $pathAuth) !== false);
                    $classeAuthTexto = $authAtivo ? 'text-rosaAlerta underline decoration-2 underline-offset-4' : ($estaLogado ? 'text-white' : 'font-semibold text-white');
                    ?>
                    <a href="<?= e($itemAuth['url']) ?>"
                        class="nav-link-mobile border-white/20 no-underline">
                        <?= renderIconeMenu($itemAuth['icone'], $itemAuth['label']) ?>
                        <span class="<?= $classeAuthTexto ?>"><?= e($itemAuth['label']) ?></span>
                    </a>
                </li>
            </ul>
        </nav>

        <div id="backdrop-menu"
            class="fixed inset-0 z-40 hidden bg-preto/50 opacity-0 backdrop-blur-sm transition-opacity duration-300 ease-out lg:hidden"
            aria-hidden="true"></div>
    </header>

    <aside id="sidebar"
        class="fixed bottom-0 left-0 top-16 z-40 hidden w-60 flex-col bg-primary py-6 transition-transform duration-300 ease-in-out lg:flex lg:translate-x-0"
        aria-label="Navegação principal (desktop)">

        <nav class="flex-1 space-y-2 overflow-y-auto overflow-x-hidden px-3 scrollbar-hide">
            <?php foreach ($menuItens as $item): ?>
                <?php
                $ehAtivo = false;
                $itemPath = parse_url($item['url'], PHP_URL_PATH);
                if ($item['url'] === URL_BASE . '/') {
                    $ehAtivo = ($uriAtual === '/' || preg_match('/public\/?(?:index\.php)?$/', $uriAtual));
                } else {
                    $ehAtivo = (strpos($uriAtual, $itemPath) !== false);
                }
                $classeTexto = $ehAtivo ? 'text-rosaAlerta underline decoration-2 underline-offset-4' : 'text-white';
                ?>
                <a href="<?= e($item['url']) ?>" class="nav-link-desktop !h-auto min-h-[3rem] py-2">
                    <?= renderIconeMenu($item['icone'], $item['label'], 'h-6 w-6 shrink-0 text-white') ?>
                    <span class="rotulo-link whitespace-normal leading-tight <?= $classeTexto ?>"><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="space-y-2 px-3 pb-3">
            <button type="button"
                data-theme-toggle
                class="nav-link-desktop !h-auto min-h-[3rem] py-2 w-full text-left"
                aria-label="Alternar para modo escuro">
                <svg class="block h-6 w-6 shrink-0 text-white dark:hidden" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="4.5" />
                    <path stroke-linecap="round"
                        d="M12 3v2m0 14v2M4.2 4.2l1.4 1.4m12.8 12.8 1.4 1.4M3 12h2m14 0h2M4.2 19.8l1.4-1.4m12.8-12.8 1.4-1.4" />
                </svg>

                <svg class="hidden h-6 w-6 shrink-0 text-white dark:block" fill="currentColor"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.5 14.7A8.5 8.5 0 0 1 9.3 3.5a.6.6 0 0 0-.8-.8A10 10 0 1 0 21.3 15.5a.6.6 0 0 0-.8-.8Z" />
                </svg>

                <span class="rotulo-link whitespace-normal leading-tight text-white">Tema</span>
            </button>

            <a href="<?= e($itemAuth['url']) ?>" class="nav-link-desktop !h-auto min-h-[3rem] py-2">
                <?= renderIconeMenu($itemAuth['icone'], $itemAuth['label'], 'h-6 w-6 shrink-0 text-white') ?>
                <span class="rotulo-link whitespace-normal leading-tight <?= $classeAuthTexto ?? 'text-white' ?>"><?= e($itemAuth['label']) ?></span>
            </a>
        </div>
    </aside>

    <button type="button" id="botao-sidebar"
        class="fixed top-1/2 z-50 hidden h-10 w-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-4 border-primary bg-branco text-primary shadow-lg ring-2 ring-primary/20 transition-[left,transform] duration-300 ease-in-out hover:bg-rosaClaro2 dark:bg-preto2 dark:text-branco lg:flex lg:left-60"
        aria-expanded="true" aria-controls="sidebar"
        aria-label="Recolher menu lateral">

        <svg id="icone-seta" class="h-6 w-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>

    </button>

    <script>
        (function() {
            'use strict';

            var botoesTema = document.querySelectorAll('[data-theme-toggle]');
            var html = document.documentElement;

            function atualizarAriaLabel() {
                var estaEscuro = html.classList.contains('dark');
                botoesTema.forEach(function(botao) {
                    botao.setAttribute(
                        'aria-label',
                        estaEscuro ? 'Alternar para modo claro' : 'Alternar para modo escuro'
                    );
                });
            }

            atualizarAriaLabel();

            botoesTema.forEach(function(botao) {
                botao.addEventListener('click', function(evento) {
                    evento.stopPropagation();

                    var agoraEscuro = html.classList.toggle('dark');

                    localStorage.setItem('caonectados-tema', agoraEscuro ? 'escuro' : 'claro');
                    atualizarAriaLabel();
                });
            });
        })();
    </script>

    <div id="area-conteudo" class="flex flex-1 flex-col overflow-y-auto transition-[margin] duration-300 lg:ml-60">
        <main id="conteudo-dinamico" class="mx-auto w-full max-w-figma flex-1 px-4 sm:px-6">