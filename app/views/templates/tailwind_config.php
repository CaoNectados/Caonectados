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
                    primary:      'rgb(var(--color-primary) / <alpha-value>)',
                    secondary:    'rgb(var(--color-secondary) / <alpha-value>)',
                    accent:       'rgb(var(--color-accent) / <alpha-value>)',
                    background:   'rgb(var(--color-background) / <alpha-value>)',
                    surface:      'rgb(var(--color-surface) / <alpha-value>)',
                    'text-dark':  'rgb(var(--color-text-dark) / <alpha-value>)',
                    'text-muted': 'rgb(var(--color-text-muted) / <alpha-value>)',

                    // Cores destaque - Rosa (1 a 7)
                    rosa: {
                        1: 'rgb(var(--color-rosa-1) / <alpha-value>)',
                        2: 'rgb(var(--color-rosa-2) / <alpha-value>)',
                        3: 'rgb(var(--color-rosa-3) / <alpha-value>)',
                        4: 'rgb(var(--color-rosa-4) / <alpha-value>)',
                        5: 'rgb(var(--color-rosa-5) / <alpha-value>)',
                        6: 'rgb(var(--color-rosa-6) / <alpha-value>)',
                        7: 'rgb(var(--color-rosa-7) / <alpha-value>)',
                    },

                    // Cores destaque - Laranja (1 a 3)
                    laranja: {
                        1: 'rgb(var(--color-laranja-1) / <alpha-value>)',
                        2: 'rgb(var(--color-laranja-2) / <alpha-value>)',
                        3: 'rgb(var(--color-laranja-3) / <alpha-value>)',
                    },

                    // Outras cores de destaque
                    salmao:       'rgb(var(--color-salmao) / <alpha-value>)',
                    rosaAlerta:   'rgb(var(--color-rosaAlerta) / <alpha-value>)',

                    corFundo: 'rgb(var(--color-corFundo) / <alpha-value>)',
                    fundoChat: 'rgb(var(--color-fundoChat) / <alpha-value>)',
                    notificacao: 'rgb(var(--color-notificacao) / <alpha-value>)',
                    msgEnvia:  'rgb(var(--color-msgEnvia) / <alpha-value>)',
                    msgRespondida: 'rgb(var(--color-msgRespondida) / <alpha-value>)',
                    perfilChats: 'rgb(var(--color-perfilChats) / <alpha-value>)',

                    // Cores claras
                    branco:       'rgb(var(--color-branco) / <alpha-value>)',
                    brancoRosado: 'rgb(var(--color-brancoRosado) / <alpha-value>)',
                    rosaClaro:    'rgb(var(--color-rosaClaro) / <alpha-value>)',
                    rosaClaro2:   'rgb(var(--color-rosaClaro2) / <alpha-value>)',
                    roxinhoFofo:  'rgb(var(--color-roxinhoFofo) / <alpha-value>)',

                    // Cores suaves
                    roxo:         'rgb(var(--color-roxo) / <alpha-value>)',
                    cinzaMarrom:  'rgb(var(--color-cinzaMarrom) / <alpha-value>)',
                    roxo1:        'rgb(var(--color-roxo1) / <alpha-value>)',
                    roxo2:        'rgb(var(--color-roxo2) / <alpha-value>)',

                    // Cores escuras
                    preto:        'rgb(var(--color-preto) / <alpha-value>)',
                    preto1:       'rgb(var(--color-preto1) / <alpha-value>)',
                    preto2:       'rgb(var(--color-preto2) / <alpha-value>)',
                    preto3:       'rgb(var(--color-preto3) / <alpha-value>)',
                    marrom:       'rgb(var(--color-marrom) / <alpha-value>)',
                    marrom1:      'rgb(var(--color-marrom1) / <alpha-value>)',
                    roxoApagado:  'rgb(var(--color-roxoApagado) / <alpha-value>)',
                    rosaEscura:   'rgb(var(--color-rosaEscura) / <alpha-value>)',

                    // Cores inversas
                    verdeEscuro:  'rgb(var(--color-verdeEscuro) / <alpha-value>)',
                    azul:         'rgb(var(--color-azul) / <alpha-value>)',
                    amarelo:      'rgb(var(--color-amarelo) / <alpha-value>)',
                    verdeMusgo:   'rgb(var(--color-verdeMusgo) / <alpha-value>)',
                    azulEscuro:   'rgb(var(--color-azulEscuro) / <alpha-value>)',
                    laranjaEscuro:'rgb(var(--color-laranjaEscuro) / <alpha-value>)',

                    // Modais de Feedback
                    erro:         'rgb(var(--color-erro) / <alpha-value>)',
                    aviso:        'rgb(var(--color-aviso) / <alpha-value>)',
                    sucesso:      'rgb(var(--color-sucesso) / <alpha-value>)',
                    informativo:  'rgb(var(--color-informativo) / <alpha-value>)',

                    // Primitives (cores base)
                    corBarra:     'rgb(var(--color-corBarra) / <alpha-value>)',
                    cinza2:       'rgb(var(--color-cinza2) / <alpha-value>)',
                    cinza3:       'rgb(var(--color-cinza3) / <alpha-value>)',
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
    console.log('Tailwind configurado com sucesso!');
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

        /* Seta customizada para TODO <select> do site — some a seta nativa do navegador
           (que muda de estilo por SO/browser e não pode ser recolorida via CSS) e desenha
           um chevron próprio, consistente em Light e Dark Mode.
           padding-right usa !important de propósito: os selects do projeto têm classes de
           padding (px-4, p-3, etc.) definidas individualmente em cada view — sem isso, o
           texto de algumas opções ficaria por baixo do ícone. */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-repeat: no-repeat;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23716C93' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-position: right 0.75rem center;
            background-size: 1.1rem;
            padding-right: 2.75rem !important;
            cursor: pointer;
        }
        select::-ms-expand {
            display: none;
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

        /* Botões estilo "cartão 3D" das telas de autenticação (Login/Cadastro) */
        .btn-auth-primario {
            @apply w-full rounded-2xl bg-primary px-6 py-3.5 font-shantell text-lg
                   font-bold text-text-dark shadow-[4px_4px_0px_rgba(44,44,44,0.25)]
                   dark:shadow-[4px_4px_0px_rgba(0,0,0,0.5)]
                   transition hover:-translate-y-0.5 active:translate-y-0 active:shadow-none
                   disabled:cursor-not-allowed disabled:opacity-50;
        }
        .btn-auth-secundario {
            @apply w-full rounded-2xl bg-rosa-1 px-6 py-3.5 font-shantell text-lg
                   font-bold text-text-dark shadow-[4px_4px_0px_rgba(44,44,44,0.25)]
                   dark:shadow-[4px_4px_0px_rgba(0,0,0,0.5)]
                   transition hover:-translate-y-0.5 active:translate-y-0 active:shadow-none
                   block text-center;
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
        /* Campos de bairro/região: chevron do estado fechado do combobox customizado
           (regiao-combobox.js). Fica como fallback visual antes do JS rodar; depois de
           inicializado, o JS assume o próprio background-image do input (o popup de
           sugestões nativo do <datalist> foi substituído por um painel próprio, já que
           aquele popup não pode ser restilizado em nenhum navegador). */
        .input-com-seta {
            @apply border-2 border-preto1 font-bold dark:border-white;
            background-repeat: no-repeat;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23716C93' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-position: right 0.75rem center;
            background-size: 1.1rem;
            padding-right: 2.75rem !important;
        }
        /* Painel do combobox customizado de bairro/região (substitui o popup do <datalist>). */
        .regiao-dropdown-panel {
            @apply absolute left-0 right-0 z-30 mt-1 max-h-56 overflow-y-auto rounded-2xl
                   border-2 border-preto1 dark:border-white bg-branco dark:bg-preto1
                   shadow-lg;
        }
        .regiao-dropdown-item {
            @apply cursor-pointer border-b border-cinzaMarrom/15 px-4 py-3 font-poppins text-sm
                   font-bold text-text-dark transition-colors last:border-b-0 hover:bg-rosa-1/30
                   dark:border-preto3 dark:text-white dark:hover:bg-preto2;
        }
        .regiao-dropdown-vazio {
            @apply px-4 py-3 font-poppins text-sm italic text-text-muted;
        }
        .regiao-dropdown-panel::-webkit-scrollbar {
            width: 8px;
        }
        .regiao-dropdown-panel::-webkit-scrollbar-track {
            background: transparent;
        }
        .regiao-dropdown-panel::-webkit-scrollbar-thumb {
            background-color: #2C2C2C;
            border-radius: 999px;
        }
        .msg-erro {
            @apply mt-1 text-xs font-medium text-erro;
        }

        /* Badge "fita" do perfil (Adotante/Protetor/ONG/Admin) — pontas em bico, como no protótipo. */
        .ribbon-perfil {
            clip-path: polygon(6% 0, 94% 0, 100% 50%, 94% 100%, 6% 100%, 0% 50%);
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
    }
</style>

<!-- ============================================================
    VARIÁVEIS CSS
============================================================= -->
<style>
    :root {
        --color-primary: 79 72 115;
        --color-secondary: 113 108 147;
        --color-accent: 250 86 114;
        --color-background: 249 249 249;
        --color-surface: 254 248 251;
        --color-text-dark: 44 44 44;
        --color-text-muted: 158 158 158;

        --color-rosa-1: 251 219 235;
        --color-rosa-2: 243 172 187;
        --color-rosa-3: 246 191 206;
        --color-rosa-4: 245 181 197;
        --color-rosa-5: 239 168 165;
        --color-rosa-6: 248 200 216;
        --color-rosa-7: 249 210 225;
        --color-laranja-1: 221 153 76;
        --color-laranja-2: 225 157 98;
        --color-laranja-3: 230 161 120;
        --color-salmao: 234 164 143;
        --color-rosaAlerta: 250 86 114;

        --color-corFundo: 249 249 249;
        --color-fundoChat: 254 248 251;
        --color-notificacao: 44 44 44;
        --color-msgEnvia: 17 16 66;
        --color-msgRespondida: 233 235 238;
        --color-perfilChats: 33 38 46;

        --color-branco: 255 255 255;
        --color-brancoRosado: 254 248 251;
        --color-rosaClaro: 251 222 237;
        --color-rosaClaro2: 253 235 244;
        --color-roxinhoFofo: 224 209 255;

        --color-roxo: 168 132 155;
        --color-cinzaMarrom: 180 164 164;
        --color-roxo1: 108 100 148;
        --color-roxo2: 113 108 147;

        --color-preto: 0 0 0;
        --color-preto1: 23 20 21;
        --color-preto2: 46 40 43;
        --color-preto3: 68 60 64;
        --color-marrom: 137 119 128;
        --color-marrom1: 183 159 171;
        --color-roxoApagado: 205 179 192;
        --color-rosaEscura: 228 199 214;

        --color-verdeEscuro: 4 36 20;
        --color-azul: 77 163 186;
        --color-amarelo: 238 239 189;
        --color-verdeMusgo: 76 89 66;
        --color-azulEscuro: 17 16 66;
        --color-laranjaEscuro: 178 92 69;

        --color-erro: 116 7 4;
        --color-aviso: 248 174 0;
        --color-sucesso: 67 160 71;
        --color-informativo: 15 98 206;

        --color-corBarra: 80 89 101;
        --color-cinza2: 45 45 45;
        --color-cinza3: 61 61 61;
    }

    .dark {
        --color-primary: 79 72 115;
        --color-secondary: 79 72 115;
        --color-background: 25 24 62;
        --color-surface: 21 13 55;
        --color-text-dark: 255 255 255;
        --color-text-muted: 212 212 212;

        --color-rosa-1: 209 140 174;
        --color-rosa-2: 206 104 126;
        --color-rosa-3: 211 131 153;
        --color-rosa-4: 187 105 126;
        --color-rosa-5: 199 110 107;
        --color-rosa-6: 194 125 148;
        --color-rosa-7: 208 141 167;
        --color-laranja-1: 185 118 41;
        --color-laranja-2: 189 119 57;
        --color-laranja-3: 191 117 75;
        --color-salmao: 234 164 143;
        --color-rosaAlerta: 210 50 77;

        --color-corFundo: 12 11 40;
        --color-fundoChat: 21 13 55;
        --color-notificacao: 71 0 146;
        --color-msgEnvia: 87 86 121;
        --color-msgRespondida: 29 29 29;
        --color-perfilChats: 255 255 255;

        --color-branco: 108 108 108;
        --color-brancoRosado: 253 167 210;
        --color-rosaClaro: 235 126 180;
        --color-rosaClaro2: 236 95 166;
        --color-roxinhoFofo: 162 130 225;

        --color-roxo: 189 141 172;
        --color-cinzaMarrom: 176 132 132;
        --color-roxo1: 139 130 184;
        --color-roxo2: 141 135 184;

        --color-preto: 0 0 0;
        --color-preto1: 23 20 21;
        --color-preto2: 46 40 43;
        --color-preto3: 68 60 64;
        --color-marrom: 137 119 128;
        --color-marrom1: 183 159 171;
        --color-roxoApagado: 205 179 192;
        --color-rosaEscura: 228 199 214;

        --color-verdeEscuro: 4 36 20;
        --color-azul: 77 163 186;
        --color-amarelo: 238 239 189;
        --color-verdeMusgo: 76 89 66;
        --color-azulEscuro: 17 16 66;
        --color-laranjaEscuro: 178 92 69;

        --color-erro: 255 107 107;
        --color-sucesso: 105 219 124;
        --color-aviso: 252 196 25;
        --color-informativo: 116 192 252;
    }
</style>