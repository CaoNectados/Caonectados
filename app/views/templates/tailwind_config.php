<!-- ============================================================
     FONTES (Google Fonts)
============================================================= -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Shantell+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- ============================================================
    CARREGA O CDN DO TAILWIND
============================================================= -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- ============================================================
    CONFIGURAÇÃO DO TAILWIND
============================================================= -->
<script>
    window.tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    // Aliases semânticos (usados no dia a dia)
                    primary:      'var(--color-primary)',
                    secondary:    'var(--color-secondary)',
                    accent:       'var(--color-accent)',
                    background:   'var(--color-background)',
                    surface:      'var(--color-surface)',
                    'text-dark':  'var(--color-text-dark)',
                    'text-muted': 'var(--color-text-muted)',

                    // Cores destaque - Rosa (1 a 7)
                    rosa: {
                        1: 'var(--color-rosa-1)',
                        2: 'var(--color-rosa-2)',
                        3: 'var(--color-rosa-3)',
                        4: 'var(--color-rosa-4)',
                        5: 'var(--color-rosa-5)',
                        6: 'var(--color-rosa-6)',
                        7: 'var(--color-rosa-7)',
                    },

                    // Cores destaque - Laranja (1 a 3)
                    laranja: {
                        1: 'var(--color-laranja-1)',
                        2: 'var(--color-laranja-2)',
                        3: 'var(--color-laranja-3)',
                    },

                    // Outras cores de destaque
                    salmao:       'var(--color-salmao)',
                    rosaAlerta:   'var(--color-rosaAlerta)',

                    corFundo: 'var(--color-corFundo)',
                    fundoChat: 'var(--color-fundoChat)',
                    notificacao: 'var(--color-notificacao)',
                    msgEnvia:  'var(--color-msgEnvia)',
                    msgRespondida: 'var(--color-msgRespondida)',
                    perfilChats: 'var(--color-perfilChats)',

                    // Cores claras
                    branco:       'var(--color-branco)',
                    brancoRosado: 'var(--color-brancoRosado)',
                    rosaClaro:    'var(--color-rosaClaro)',
                    rosaClaro2:   'var(--color-rosaClaro2)',
                    roxinhoFofo:  'var(--color-roxinhoFofo)',

                    // Cores suaves (Atenção: aqui estão as que estavam faltando!)
                    roxo:         'var(--color-roxo)',
                    cinzaMarrom:  'var(--color-cinzaMarrom)',
                    roxo1:        'var(--color-roxo1)',
                    roxo2:        'var(--color-roxo2)',

                    // Cores escuras
                    preto:        'var(--color-preto)',
                    preto1:       'var(--color-preto1)',
                    preto2:       'var(--color-preto2)',
                    preto3:       'var(--color-preto3)',
                    marrom:       'var(--color-marrom)',
                    marrom1:      'var(--color-marrom1)',
                    roxoApagado:  'var(--color-roxoApagado)',
                    rosaEscura:   'var(--color-rosaEscura)',

                    // Cores inversas
                    verdeEscuro:  'var(--color-verdeEscuro)',
                    azul:         'var(--color-azul)',
                    amarelo:      'var(--color-amarelo)',
                    verdeMusgo:   'var(--color-verdeMusgo)',
                    azulEscuro:   'var(--color-azulEscuro)',
                    laranjaEscuro:'var(--color-laranjaEscuro)',

                    // Modais de Feedback
                    erro:         'var(--color-erro)',
                    aviso:        'var(--color-aviso)',
                    sucesso:      'var(--color-sucesso)',
                    informativo:  'var(--color-informativo)',

                    // Primitives (cores base)
                    corBarra:     'var(--color-corBarra)',
                    cinza2:       'var(--color-cinza2)',
                    cinza3:       'var(--color-cinza3)',
                },

                // Famílias de fonte
                fontFamily: {
                    shantell: ['"Shantell Sans"', 'cursive'],
                    poppins:  ['Poppins', 'sans-serif'],
                },

                // Largura máxima do Figma
                maxWidth: {
                    figma: '1112px',
                },
            },
        },
    };
    console.log('✅ Tailwind configurado com sucesso!');
</script>

<!-- ============================================================
    CLASSES CUSTOMIZADAS
============================================================= -->
<style type="text/tailwindcss">
    @layer base {
        body {
            @apply bg-background font-poppins text-base text-text-dark antialiased;
        }
        h1 {
            @apply font-shantell text-[32px] leading-tight font-normal;
        }
        h2 {
            @apply font-shantell text-2xl leading-tight font-normal;
        }
        h3 {
            @apply font-poppins text-xl font-semibold;
        }
        h4 {
            @apply font-poppins text-lg font-medium;
        }
        small {
            @apply font-poppins text-xs text-text-muted;
        }
        :focus-visible {
            @apply outline outline-[3px] outline-rosaAlerta outline-offset-2 rounded;
        }
    }

    @layer components {
        /* Botões */
        .btn-primario {
            @apply inline-flex items-center justify-center gap-2 rounded-full
                   bg-primary px-6 py-2.5 font-poppins font-semibold text-white
                   transition hover:bg-roxo1 active:scale-95
                   disabled:cursor-not-allowed disabled:opacity-50;
        }
        .btn-secundario {
            @apply inline-flex items-center justify-center gap-2 rounded-full
                   border-2 border-primary bg-transparent px-6 py-2 font-poppins
                   font-semibold text-primary transition
                   hover:bg-primary hover:text-white active:scale-95;
        }
        .btn-acao {
            @apply inline-flex items-center justify-center gap-2 rounded-full
                   bg-rosaAlerta px-6 py-2.5 font-poppins font-semibold text-white
                   transition hover:bg-rosa-2 hover:text-text-dark active:scale-95;
        }

        /* Formulários */
        .label-padrao {
            @apply mb-1 block font-poppins text-sm font-medium text-text-dark;
        }
        .input-padrao {
            @apply w-full rounded-xl border border-cinzaMarrom bg-branco px-4 py-2.5
                   font-poppins text-base text-text-dark placeholder:text-text-muted
                   transition focus:border-primary focus:ring-2 focus:ring-roxinhoFofo
                   focus:outline-none;
        }
        .input-erro {
            @apply border-erro focus:border-erro focus:ring-rosa-2;
        }
        .msg-erro {
            @apply mt-1 text-xs font-medium text-erro;
        }

        /* Cards */
        .card-padrao {
            @apply rounded-2xl bg-surface p-6 shadow-md;
        }
        .card-destaque {
            @apply rounded-xl bg-rosa-1 p-6 sm:p-8 border border-rosa-2
                   shadow-[6px_6px_0px_rgba(44,44,44,0.15)]
                   dark:shadow-[6px_6px_0px_rgba(255,255,255,0.05)];
        }

        /* Navegação */
        .nav-link-mobile {
            @apply mx-auto flex w-full max-w-xs items-center justify-center gap-4
                   border-b border-rosa-2 py-4 font-poppins text-lg
                   font-medium text-text-dark transition;
        }
        .nav-link-desktop {
            @apply flex h-12 items-center gap-4 rounded-xl px-3 text-white
                   transition duration-300 hover:bg-white/20;
        }
        .rotulo-link {
            @apply whitespace-nowrap font-poppins font-medium transition-opacity duration-200;
        }

        /* Scrollbars */
        #area-conteudo::-webkit-scrollbar {
            width: 34px;
        }
        #area-conteudo::-webkit-scrollbar-track {
            @apply bg-rosa-6 dark:bg-preto2;
            border-left: 12px solid transparent;
            border-right: 12px solid transparent;
            background-clip: padding-box;
        }
        #area-conteudo::-webkit-scrollbar-thumb {
            @apply bg-roxo1 dark:bg-roxo1;
            border-radius: 20px;
            border: 12px solid transparent;
            background-clip: padding-box;
        }
        #area-conteudo::-webkit-scrollbar-thumb:hover {
            @apply bg-primary dark:bg-primary;
        }
        #area-conteudo::-webkit-scrollbar-button:single-button {
            display: block;
            height: 34px;
            background-color: transparent;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }
        #area-conteudo::-webkit-scrollbar-button:single-button:vertical:decrement {
            background-image: url("<?= e(URL_BASE) ?>/assets/img/patinha-cima.png");
        }
        #area-conteudo::-webkit-scrollbar-button:single-button:vertical:increment {
            background-image: url("<?= e(URL_BASE) ?>/assets/img/patinha-baixo.png");
        }
        #sidebar nav::-webkit-scrollbar {
            width: 10px;
        }
        #sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar nav::-webkit-scrollbar-thumb {
            @apply bg-white/20 dark:bg-white/10;
            border-radius: 10px;
        }
        #sidebar nav::-webkit-scrollbar-thumb:hover {
            @apply bg-white/40 dark:bg-roxo2;
        }
    }
</style>

<!-- ============================================================
    VARIÁVEIS CSS
============================================================= -->
<style>
    :root {
        /* Aliases semânticos */
        --color-primary: #4F4873;
        --color-secondary: #716C93;
        --color-accent: #FA5672;
        --color-background: #F9F9F9;
        --color-surface: #FEF8FB;
        --color-text-dark: #2C2C2C;
        --color-text-muted: #9E9E9E;

        /* Cores destaque */
        --color-rosa-1: #FBDBEB;
        --color-rosa-2: #F3ACBB;
        --color-rosa-3: #F6BFCE;
        --color-rosa-4: #F5B5C5;
        --color-rosa-5: #EFA8A5;
        --color-rosa-6: #F8C8D8;
        --color-rosa-7: #F9D2E1;
        --color-laranja-1: #DD994C;
        --color-laranja-2: #E19D62;
        --color-laranja-3: #E6A178;
        --color-salmao: #EAA48F;
        --color-rosaAlerta: #FA5672;

        --color-corFundo: #F9F9F9;
        --color-fundoChat: #FEF8FB;
        --color-notificacao: #2C2C2C;
        --color-msgEnvia: #111042;
        --color-msgRespondida: #E9EBEE;
        --color-perfilChats: #21262E;

        /* Cores claras */
        --color-branco: #FFFFFF;
        --color-brancoRosado: #FEF8FB;
        --color-rosaClaro: #FBDEED;
        --color-rosaClaro2: #FDEBF4;
        --color-roxinhoFofo: #E0D1FF;

        /* Cores suaves */
        --color-roxo: #A8849B;
        --color-cinzaMarrom: #B4A4A4;
        --color-roxo1: #6C6494;
        --color-roxo2: #716C93;

        /* Cores escuras */
        --color-preto: #000000;
        --color-preto1: #171415;
        --color-preto2: #2E282B;
        --color-preto3: #443C40;
        --color-marrom: #897780;
        --color-marrom1: #B79FAB;
        --color-roxoApagado: #CDB3C0;
        --color-rosaEscura: #E4C7D6;

        /* Cores inversas */
        --color-verdeEscuro: #042414;
        --color-azul: #4DA3BA;
        --color-amarelo: #EEEFBD;
        --color-verdeMusgo: #4C5942;
        --color-azulEscuro: #111042;
        --color-laranjaEscuro: #B25C45;

        /* Modais (feedback) */
        --color-erro: #740704;
        --color-aviso: #F8AE00;
        --color-sucesso: #43A047;
        --color-informativo: #0F62CE;

        /* Primitives */
        --color-corBarra: #505965;
        --color-cinza2: #2D2D2D;
        --color-cinza3: #3D3D3D;
    }

    .dark {
        --color-primary: #4F4873;
        --color-secondary: #4F4873;
        --color-background: #19183e;
        --color-surface: #150D37;
        --color-text-dark: #FFFFFF;
        --color-text-muted: #d4d4d4;

        /* Cores destaque */
        --color-rosa-1: #d18cae;
        --color-rosa-2: #ce687e;
        --color-rosa-3: #d38399;
        --color-rosa-4: #bb697e;
        --color-rosa-5: #c76e6b;
        --color-rosa-6: #c27d94;
        --color-rosa-7: #d08da7;
        --color-laranja-1: #b97629;
        --color-laranja-2: #bd7739;
        --color-laranja-3: #bf754b;
        --color-salmao: #EAA48F;
        --color-rosaAlerta: #d2324d;

        --color-corFundo: #0C0B28;
        --color-fundoChat: #150D37;
        --color-notificacao: #470092;
        --color-msgEnvia: #575679;
        --color-msgRespondida: #1D1D1D;
        --color-perfilChats: #FFFFFF;

        /* Cores claras */
        --color-branco: #6c6c6c;
        --color-brancoRosado: #fda7d2;
        --color-rosaClaro: #eb7eb4;
        --color-rosaClaro2: #ec5fa6;
        --color-roxinhoFofo: #a282e1;

        /* Cores suaves */
        --color-roxo: #bd8dac;
        --color-cinzaMarrom: #b08484;
        --color-roxo1: #8b82b8;
        --color-roxo2: #8d87b8;

        /* Cores escuras */
        --color-preto: #000000;
        --color-preto1: #171415;
        --color-preto2: #2E282B;
        --color-preto3: #443C40;
        --color-marrom: #897780;
        --color-marrom1: #B79FAB;
        --color-roxoApagado: #CDB3C0;
        --color-rosaEscura: #E4C7D6;

        /* Cores inversas */
        --color-verdeEscuro: #042414;
        --color-azul: #4DA3BA;
        --color-amarelo: #EEEFBD;
        --color-verdeMusgo: #4C5942;
        --color-azulEscuro: #111042;
        --color-laranjaEscuro: #B25C45;

        --color-erro: #ff6b6b;
        --color-sucesso: #69db7c;
        --color-aviso: #fcc419;
        --color-informativo: #74c0fc;
    }
</style>


