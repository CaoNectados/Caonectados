/**
 * Controle unificado de menus.
 * Gerencia a sidebar desktop, o menu mobile, o backdrop e os ícones do botão.
 */
(function () {
    'use strict';

    var sidebar = document.getElementById('sidebar');
    var botaoSidebar = document.getElementById('botao-sidebar');
    var areaConteudo = document.getElementById('area-conteudo');
    var iconeSeta = document.getElementById('icone-seta');

    var botaoMobile = document.getElementById('botao-menu-mobile');
    var menuMobile = document.getElementById('menu-mobile');
    var backdropMenu = document.getElementById('backdrop-menu');
    var iconeHamburguer = document.getElementById('icone-menu-hamburguer');
    var iconeFechar = document.getElementById('icone-menu-fechar');
    var logoHeader = document.getElementById('logo-header');
    var mobileFecharTimer = null;

    function alternarClasse(elemento, classe, adicionar) {
        if (!elemento) {
            return;
        }

        elemento.classList.toggle(classe, adicionar);
    }

function definirSidebarAberta(estaAberta) {
        if (!sidebar || !botaoSidebar || !areaConteudo) {
            return;
        }

        alternarClasse(sidebar, 'lg:-translate-x-full', !estaAberta);
        alternarClasse(sidebar, 'lg:translate-x-0', estaAberta);

        alternarClasse(areaConteudo, 'lg:ml-60', estaAberta);
        alternarClasse(areaConteudo, 'lg:ml-0', !estaAberta);

        // O botão acompanha a sidebar
        alternarClasse(botaoSidebar, 'lg:left-60', estaAberta);
        
        // MUDE AQUI: De 'lg:left-0' para 'lg:left-6'
        alternarClasse(botaoSidebar, 'lg:left-6', !estaAberta);

        botaoSidebar.setAttribute('aria-expanded', String(estaAberta));
        botaoSidebar.setAttribute(
            'aria-label',
            estaAberta ? 'Recolher menu lateral' : 'Abrir menu lateral'
        );

        if (iconeSeta) {
            iconeSeta.classList.toggle('rotate-180', estaAberta);
        }
    }

    function mostrarMenuMobile() {
        if (!menuMobile || !backdropMenu || !botaoMobile) {
            return;
        }

        window.clearTimeout(mobileFecharTimer);

        menuMobile.hidden = false;
        backdropMenu.hidden = false;

        requestAnimationFrame(function () {
            menuMobile.classList.remove('opacity-0', '-translate-y-2', 'pointer-events-none');
            menuMobile.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');

            backdropMenu.classList.remove('opacity-0');
            backdropMenu.classList.add('opacity-100');
        });

        botaoMobile.setAttribute('aria-expanded', 'true');
        botaoMobile.setAttribute('aria-label', 'Fechar menu de navegação');

        if (iconeHamburguer) {
            iconeHamburguer.classList.add('hidden');
        }

        if (iconeFechar) {
            iconeFechar.classList.remove('hidden');
        }

        document.body.classList.add('overflow-hidden');
    }

    function esconderMenuMobile() {
        if (!menuMobile || !backdropMenu || !botaoMobile) {
            return;
        }

        window.clearTimeout(mobileFecharTimer);

        menuMobile.classList.add('opacity-0', '-translate-y-2', 'pointer-events-none');
        menuMobile.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');

        backdropMenu.classList.add('opacity-0');
        backdropMenu.classList.remove('opacity-100');

        botaoMobile.setAttribute('aria-expanded', 'false');
        botaoMobile.setAttribute('aria-label', 'Abrir menu de navegação');

        if (iconeHamburguer) {
            iconeHamburguer.classList.remove('hidden');
        }

        if (iconeFechar) {
            iconeFechar.classList.add('hidden');
        }

        document.body.classList.remove('overflow-hidden');

        mobileFecharTimer = window.setTimeout(function () {
            menuMobile.hidden = true;
            backdropMenu.hidden = true;
        }, 300);
    }

    function alternarMenuMobile() {
        if (!menuMobile) {
            return;
        }

        if (menuMobile.hidden) {
            mostrarMenuMobile();
        } else {
            esconderMenuMobile();
        }
    }

    if (botaoSidebar && sidebar && areaConteudo) {
        var sidebarAberta = !sidebar.classList.contains('lg:-translate-x-full');

        definirSidebarAberta(sidebarAberta);

        botaoSidebar.addEventListener('click', function () {
            sidebarAberta = !sidebar.classList.contains('lg:-translate-x-full');
            definirSidebarAberta(!sidebarAberta);
        });

        if (logoHeader) {
            logoHeader.addEventListener('mouseenter', function () {
                var estaFechada = sidebar.classList.contains('lg:-translate-x-full');
                
                // Se o menu estiver fechado, nós abrimos ele
                if (estaFechada) {
                    definirSidebarAberta(true);
                }
            });
        }
    }

    if (botaoMobile && menuMobile) {
        botaoMobile.addEventListener('click', alternarMenuMobile);

        menuMobile.addEventListener('click', function (evento) {
            var elementoClicado = evento.target.closest('a');

            if (elementoClicado) {
                esconderMenuMobile();
            }
        });

        if (backdropMenu) {
            backdropMenu.addEventListener('click', esconderMenuMobile);
        }

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && !menuMobile.hidden) {
                esconderMenuMobile();
                botaoMobile.focus();
            }
        });
    }
    
    
})();