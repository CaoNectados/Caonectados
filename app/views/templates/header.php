<?php

/**
 * Template: Cabeçalho global (Header)
 * app/views/templates/header.php
 *
 * Responsabilidade: montar a navegação (mobile + desktop) a partir do
 * perfil da sessão, com suporte a Dark Mode via classe "dark" na <html>.
 * Toda a decisão de "quem vê o quê" fica concentrada no bloco PHP
 * abaixo — o HTML só sabe iterar um array. (Princípio DRY)
 */

if (!function_exists('e')) {
    function e(?string $valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Renderiza o ícone de um item de menu.
 *
 * REGRA 1 — "O segredo do SVG": em vez de <img>, lemos o arquivo .svg
 * fisicamente e devolvemos a marcação inline. Isso permite que o SVG
 * herde `currentColor` e reaja a `dark:text-white` normalmente, coisa
 * que uma tag <img> jamais consegue fazer (a cor fica presa dentro do
 * arquivo). Para PNG/JPG (ou se o SVG não existir/for ilegível), caímos
 * de volta para <img> automaticamente.
 *
 * @param string $nomeArquivo   Nome do arquivo dentro de /public/assets/icons/navbar/
 * @param string $descricao     Texto do item (reservado para title/tooltip futuro)
 * @param string $classesCor    Classes Tailwind de cor/tamanho aplicadas ao <svg>/<img>
 */
function renderIconeMenu(
    string $nomeArquivo,
    string $descricao,
    string $classesCor = 'h-6 w-6 shrink-0 text-white'
): string {
    // Caminho físico no disco (para leitura) x caminho público (para <img> de fallback)
    $caminhoFisico  = __DIR__ . '/../../../public/assets/icons/navbar/' . $nomeArquivo;
    $caminhoPublico = '/assets/icons/navbar/' . $nomeArquivo;
    $extensao       = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

    $ehSvgValido = $extensao === 'svg' && is_file($caminhoFisico) && is_readable($caminhoFisico);

    if ($ehSvgValido) {
        $conteudoSvg = file_get_contents($caminhoFisico);

        // 1. Remove a declaração XML do topo do arquivo
        $conteudoSvg = preg_replace('/<\?xml.*?\?>/i', '', $conteudoSvg);

        // 2. Faz o SVG herdar a cor do contexto atual, mantendo o menu branco.
        $conteudoSvg = preg_replace('/fill="(?!none)[^"]*"/i', 'fill="currentColor"', $conteudoSvg);
        $conteudoSvg = preg_replace('/stroke="(?!none)[^"]*"/i', 'stroke="currentColor"', $conteudoSvg);

        // 3. Injeta nossas classes na tag <svg> principal sem alterar
        // as cores originais do arquivo SVG.
        if (preg_match('/<svg\b([^>]*)>/i', $conteudoSvg, $m)) {
            $atributosOriginais = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $m[1]);

            // Retirei o 'fill-current' fixo daqui. As classes do Tailwind (text-primary)
            // vão ditar a cor, e o 'currentColor' nas tags internas vai herdar isso.
            $novaAbertura = sprintf(
                '<svg%s class="%s" aria-hidden="true" focusable="false">',
                $atributosOriginais,
                e($classesCor)
            );

            $conteudoSvg = preg_replace('/<svg\b[^>]*>/i', $novaAbertura, $conteudoSvg, 1);
        }

        return $conteudoSvg;
    }

    // Fallback: PNG/JPG, ou SVG ausente/ilegível
    return sprintf(
        '<img src="%s" alt="" aria-hidden="true" class="%s">',
        e($caminhoPublico),
        e($classesCor)
    );
}

// ------------------------------------------------------------------
// 1) LÓGICA DE AUTENTICAÇÃO E PERFIL
// ------------------------------------------------------------------

$titulo = $titulo ?? 'CãoNectados';

// Enum da tabela USUARIOS: 'usuario' | 'adotante' | 'protetor' | 'ong' | 'administrador'
$tipoPerfil = $_SESSION['tipo_perfil'] ?? null;

/**
 * $menuItens: array de itens comuns ao Mobile e ao Desktop.
 * Cada item: ['url' => string, 'label' => string, 'icone' => 'arquivo.svg']
 */
$menuItens = [];

$menuItens[] = ['url' => '/',        'label' => 'Home',              'icone' => 'home.svg'];

if ($tipoPerfil === null) {
    $menuItens[] = ['url' => '/cadastro', 'label' => 'Cadastre-se', 'icone' => 'cadastro.svg'];
}
if ($tipoPerfil === 'administrador') {

    $menuItens[] = ['url' => '/admin/dashboard', 'label' => 'Dashboard', 'icone' => 'dashboard.svg'];
    $menuItens[] = ['url' => '/admin/solicitacoes', 'label' => 'Solicitações Ongs e Protetores', 'icone' => 'patinha.svg'];
    $menuItens[] = ['url' => '/admin/gerenciar-usuarios', 'label' => 'Gerenciar Usuários', 'icone' => 'usuarios.svg'];
    $menuItens[] = ['url' => '/admin/gerenciar-bairros', 'label' => 'Gerenciar Bairros', 'icone' => 'bairros.svg'];
    $menuItens[] = ['url' => '/admin/gerenciar-especies-racas', 'label' => 'Gerenciar Espécies e Raças', 'icone' => 'especies.svg'];
    $menuItens[] = ['url' => '/admin/denuncias', 'label' => 'Denúncias', 'icone' => 'denuncia.svg'];
    $menuItens[] = ['url' => '/admin/auditoria-logs', 'label' => 'Auditoria e Logs', 'icone' => 'auditoria.svg'];
} elseif ($tipoPerfil === 'protetor' || $tipoPerfil === 'ong') {

    $menuItens[] = ['url' => '/animais/cadastrar', 'label' => 'Cadastrar Animal',         'icone' => 'cadastrar.svg'];
    $menuItens[] = ['url' => '/solicitacoes',      'label' => 'Solicitações Recebidas',   'icone' => 'solicitacao.svg'];
    $menuItens[] = ['url' => '/perfil',            'label' => 'Meu Perfil', 'icone' => 'login.svg'];
    $menuItens[] = ['url' => '/pagina-protetor',   'label' => 'Página', 'icone' => 'pagina.svg'];
} elseif ($tipoPerfil === 'adotante') {

    $menuItens[] = ['url' => '/perfil',        'label' => 'Meu Perfil',    'icone' => 'login.svg'];
} elseif ($tipoPerfil === 'usuario') {

    // Cadastro básico feito, ainda sem perfil específico escolhido.
    $menuItens[] = ['url' => '/perfil/completar', 'label' => 'Completar Perfil', 'icone' => 'perfil.svg'];
}
// Se $tipoPerfil === null (visitante), o menu fica só com os itens comuns.

/**
 * Item de autenticação: fora do array principal — sua aparência
 * (rota, rótulo, destaque) é sempre binária, nunca depende do perfil.
 */
$estaLogado = $tipoPerfil !== null;
$itemAuth   = $estaLogado
    ? ['url' => '/logout', 'label' => 'Sair',              'icone' => 'logout.svg']
    : ['url' => '/login',  'label' => 'Entrar / Registar', 'icone' => 'login2.svg'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>

    <!-- ==============================================================
         REGRA 4a — ANTI-FLASH DO DARK MODE
         Este script PRECISA rodar aqui (síncrono, sem defer/async),
         antes de qualquer pixel ser pintado. Se ele ficasse só no fim
         do arquivo, o usuário veria a tela branca por uma fração de
         segundo antes de escurecer — exatamente o "flash" que queremos
         evitar. A lógica de CLIQUE do botão fica no fim do arquivo,
         perto de onde o botão é renderizado.
    =============================================================== -->
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

    <?php include __DIR__ . '/tailwind_config.php'; ?>
</head>

<body class="flex min-h-screen flex-col bg-background dark:bg-corFundo-escuro transition-colors">

    <header class="sticky top-0 z-50 h-16 bg-primary shadow-md">
        <div class="relative flex h-full items-center gap-3 px-4 sm:px-6">

            <a href="/" id="logo-header" class="flex items-center shrink-0 gap-2" aria-label="CãoNectados — página inicial">
                <img src="<?= e(BASE_URL) ?>/assets/img/logo.png"
                    alt="CãoNectados"
                    class="h-16 w-auto sm:h-18 lg:h-18">
                <span class="hidden whitespace-nowrap font-shantell text-2xl font-bold leading-tight text-white lg:inline">
                    CãoNectados
                </span>
            </a>

            <div class="pointer-events-none absolute inset-x-0 flex items-center justify-center lg:hidden">
                <span class="max-w-[12rem] px-16 text-center font-shantell text-2xl font-bold leading-tight text-white sm:text-xl">
                    <?= e($titulo) ?>
                </span>
            </div>

            <div class="ml-4 hidden min-w-0 flex-1 justify-end lg:flex">
                <span class="max-w-[18rem] truncate text-right font-shantell text-xl font-bold leading-tight text-white xl:text-2xl">
                    <?= e($titulo) ?>
                </span>
            </div>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button type="button"
                    class="relative rounded-lg p-2 text-white transition hover:bg-white/20"
                    aria-label="Notificações">
                    <img src="<?= e(BASE_URL) ?>/assets/icons/navbar/notificacao.svg"
                        alt=""
                        aria-hidden="true"
                        class="h-8 w-8">
                </button>

                <!-- Botão do Menu Mobile (some a partir de lg, a sidebar assume) -->
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

        <!-- ==============================================
             MENU MOBILE — apenas itera $menuItens
        =============================================== -->
        <nav id="menu-mobile" hidden
            class="absolute inset-x-0 top-16 z-50 max-h-[calc(100vh-4rem)] origin-top overflow-y-auto bg-primary px-6 pb-10 pt-4 text-white shadow-lg transition duration-300 ease-out opacity-0 -translate-y-2 pointer-events-none lg:hidden"
            aria-label="Navegação principal (mobile)">
            <ul>
                <?php foreach ($menuItens as $item): ?>
                    <li>
                        <a href="<?= e($item['url']) ?>"
                            class="nav-link-mobile border-white/20 text-white no-underline">
                            <?= renderIconeMenu($item['icone'], $item['label']) ?>
                            <span><?= e($item['label']) ?></span>
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

                <!-- Item de autenticação: sempre por último, com destaque -->
                <li>
                    <a href="<?= e($itemAuth['url']) ?>"
                        class="nav-link-mobile border-white/20 no-underline <?= $estaLogado ? 'text-white' : 'font-semibold text-white' ?>">
                        <?= renderIconeMenu($itemAuth['icone'], $itemAuth['label']) ?>
                        <span><?= e($itemAuth['label']) ?></span>
                    </a>
                </li>
            </ul>
        </nav>

        <div id="backdrop-menu"
            class="fixed inset-0 z-40 hidden bg-preto/50 opacity-0 backdrop-blur-sm transition-opacity duration-300 ease-out lg:hidden"
            aria-hidden="true"></div>
    </header>

    <!-- ==============================================
         SIDEBAR DESKTOP — mesmo array $menuItens, outro foreach
    =============================================== -->
    <aside id="sidebar"
        class="fixed bottom-0 left-0 top-16 z-40 hidden w-60 flex-col bg-primary py-6 transition-transform duration-300 ease-in-out lg:flex lg:translate-x-0"
        aria-label="Navegação principal (desktop)">

        <nav class="flex-1 space-y-2 overflow-y-auto px-3">
            <?php foreach ($menuItens as $item): ?>
                <a href="<?= e($item['url']) ?>" class="nav-link-desktop">
                    <?= renderIconeMenu($item['icone'], $item['label'], 'h-6 w-6 shrink-0 text-white') ?>
                    <span class="rotulo-link"><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="space-y-2 px-3 pb-3">
            <button type="button"
                data-theme-toggle
                class="nav-link-desktop w-full"
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

                <span class="rotulo-link">Tema</span>
            </button>

            <a href="<?= e($itemAuth['url']) ?>" class="nav-link-desktop">
                <?= renderIconeMenu($itemAuth['icone'], $itemAuth['label'], 'h-6 w-6 shrink-0 text-white') ?>
                <span class="rotulo-link"><?= e($itemAuth['label']) ?></span>
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

    <!-- ==============================================================
         LÓGICA DE CLIQUE DO TOGGLE (Sol/Lua)
    =============================================================== -->
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

            // Garante que o aria-label reflita o estado já aplicado pelo anti-flash
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

    <!-- Abertura do wrapper de conteúdo (fechado no footer.php) -->
    <div id="area-conteudo" class="flex flex-1 flex-col transition-[margin] duration-300 lg:ml-60">
        <main id="conteudo-dinamico" class="mx-auto w-full max-w-figma flex-1 px-4 py-8 sm:px-6">