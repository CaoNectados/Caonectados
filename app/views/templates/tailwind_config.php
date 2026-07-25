 <!-- ============================================================
         FONTES (Google Fonts)
         Figma: Títulos = Shantell Sans | Texto = Poppins
    ============================================================= -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Shantell+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
 
    <!-- Tailwind CSS  -->
    <script src="https://cdn.tailwindcss.com"></script>
 
    <!-- ============================================================
         PASSO 1 — CONFIGURAÇÃO DEFINITIVA DO TAILWIND
         Todas as variáveis de cor do Figma, com os MESMOS nomes
         das coleçõesmas 
    ============================================================= -->
    <script>
        tailwind.config = {
            darkMode: 'class', // pares Claro/Escuro do Figma prontos para uso futuro
            theme: {
                extend: {
                    colors: {
                        /* ---------- ALIASES SEMÂNTICOS (use estes no dia a dia) ---------- */
                        primary:      '#4F4873', // Cor_BarraNavegação / Cor_Fundo_NAV
                        secondary:    '#716C93', // Roxo2 (hover / apoio do primary)
                        accent:       '#FA5672', // rosaAlerta (CTAs, notificações, "petisco")
                        background:   '#F9F9F9', // Cor_Fundo (claro)  | escuro: #0C0B28
                        surface:      '#FEF8FB', // BrancoRosado / FundoChat (cards, painéis)
                        'text-dark':  '#2C2C2C', // CinzaEscuro (texto padrão)
                        'text-muted': '#9E9E9E', // CinzaNeutro (legendas)
 
                        /* ---------- CORES_DESTAQUE ---------- */
                        rosa: {
                            1: '#FBDBEB', 2: '#F3ACBB', 3: '#F6BFCE', 4: '#F5B5C5',
                            5: '#EFA8A5', 6: '#F8C8D8', 7: '#F9D2E1',
                        },
                        laranja:      { 1: '#DD994C', 2: '#E19D62', 3: '#E6A178' },
                        salmao:       '#EAA48F',
                        rosaAlerta:   '#FA5672',
                        corFundo:     { claro: '#F9F9F9', escuro: '#0C0B28' },
                        fundoChat:    { claro: '#FEF8FB', escuro: '#150D37' },
                        notificacao:  { claro: '#2C2C2C', escuro: '#470092' },
                        msgEnvia:     { claro: '#111042', escuro: '#575679' },
                        msgRespondida:{ claro: '#E9EBEE', escuro: '#1D1D1D' },
                        perfilChats:  { claro: '#21262E', escuro: '#FFFFFF' },
 
                        /* ---------- CORES_CLARAS ---------- */
                        branco:       '#FFFFFF',
                        brancoRosado: '#FEF8FB',
                        rosaClaro:    '#FBDEED', // valor da tabela de variáveis (o poster tem um typo)
                        rosaClaro2:   '#FDEBF4',
                        roxinhoFofo:  '#E0D1FF',
 
                        /* ---------- CORES_SUAVES ---------- */
                        roxo:         '#A8849B',
                        cinzaMarrom:  '#B4A4A4',
                        roxo1:        '#6C6494',
                        roxo2:        '#716C93',
 
                        /* ---------- CORES_ESCURAS ---------- */
                        preto:        '#000000',
                        preto1:       '#171415',
                        preto2:       '#2E282B',
                        preto3:       '#443C40',
                        marrom:       '#897780',
                        marrom1:      '#B79FAB',
                        roxoApagado:  '#CDB3C0',
                        rosaEscura:   '#E4C7D6', // "Rosa" da coleção Cores_Escuras
 
                        /* ---------- CORES_INVERSAS ---------- */
                        verdeEscuro:  '#042414',
                        azul:         '#4DA3BA',
                        amarelo:      '#EEEFBD',
                        verdeMusgo:   '#4C5942',
                        azulEscuro:   '#111042',
                        laranjaEscuro:'#B25C45',
 
                        /* ---------- CORES_MODAIS (feedback) ---------- */
                        erro:         '#740704', // tabela de variáveis (poster: #E53935 — typo)
                        aviso:        '#F8AE00', // tabela de variáveis (poster: #4DA3BA — typo)
                        sucesso:      '#43A047',
                        informativo:  '#0F62CE',
 
                        /* ---------- _PRIMITIVES ---------- */
                        corBarra:     '#505965',
                        cinza2:       '#2D2D2D', // "Cor 2"
                        cinza3:       '#3D3D3D', // "Cor 3"
                    },
                    fontFamily: {
                        shantell: ['"Shantell Sans"', 'cursive'],   // títulos
                        poppins:  ['Poppins', 'sans-serif'],        // texto
                    },
                    maxWidth: {
                        figma: '1112px', // _Primitives → Max Width
                    },
                },
            },
        };
    </script>
 
    <!-- ============================================================
         PASSO 2 — TIPOGRAFIA GLOBAL E COMPONENTES BASE
    ============================================================= -->
    <style type="text/tailwindcss">
        /* ---------------- TIPOGRAFIA (tabela "Tipografia" do Figma) ---------------- */
        @layer base {
            body   { @apply bg-background font-poppins text-base text-text-dark antialiased; } /* Principal 16px */
 
            h1     { @apply font-shantell text-[32px] leading-tight font-normal; }  /* H1 32px Regular   */
            h2     { @apply font-shantell text-2xl  leading-tight font-normal; }    /* H2 24px Regular   */
            h3     { @apply font-poppins  text-xl   font-semibold; }                /* H3 20px SemiBold  */
            h4     { @apply font-poppins  text-lg   font-medium; }                  /* H4 18px Medium    */
            small  { @apply font-poppins  text-xs   text-text-muted; }              /* Legenda 12px      */
 
            /* Variações Bold dos títulos: adicione a classe .bold no elemento */
            h1.bold, h2.bold { @apply font-bold; }        /* H1_Bold / H2_Bold */
            strong           { @apply font-bold; }        /* Principal_Bold    */
 
            a      { @apply transition-colors; }
 
            /* Foco visível global (WCAG 2.4.7) */
            :focus-visible { @apply outline outline-[3px] outline-rosaAlerta outline-offset-2 rounded; }
        }
 
        /* ---------------- COMPONENTES PADRONIZADOS ---------------- */
        @layer components {
 
            /* ===== Botões ===== */
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
            .btn-acao { /* CTA rosa: "Dar Petisco", "Dar a Patinha" */
                @apply inline-flex items-center justify-center gap-2 rounded-full
                       bg-rosaAlerta px-6 py-2.5 font-poppins font-semibold text-white
                       transition hover:bg-rosa-2 hover:text-text-dark active:scale-95;
            }
 
            /* ===== Formulários ===== */
            .label-padrao {
                @apply mb-1 block font-poppins text-sm font-medium text-text-dark;
            }
            .input-padrao {
                @apply w-full rounded-xl border border-cinzaMarrom bg-branco px-4 py-2.5
                       font-poppins text-base text-text-dark placeholder:text-text-muted
                       transition focus:border-primary focus:ring-2 focus:ring-roxinhoFofo
                       focus:outline-none;
            }
            .input-erro   { @apply border-erro focus:border-erro focus:ring-rosa-2; }
            .msg-erro     { @apply mt-1 text-xs font-medium text-erro; }
 
            /* ===== Cards ===== */
            .card-padrao {
                @apply rounded-2xl bg-surface p-6 shadow-md;
            }
            .card-destaque { /* card rosa da Home ("Sobre Nós") */
                @apply rounded-lg bg-rosa-1 p-6 shadow-md sm:p-8;
            }
 
            /* ===== Navegação: MOBILE (menu hambúrguer aberto) ===== */
            .nav-link-mobile {
                @apply mx-auto flex w-full max-w-xs items-center justify-center gap-4
                       border-b border-text-dark/60 py-4 font-poppins text-lg
                       font-medium text-text-dark transition;
            }
 
            /* ===== Navegação: DESKTOP (sidebar) ===== */
            .nav-link-desktop {
                @apply flex h-12 items-center gap-4 rounded-xl px-3 text-white
                       transition duration-300 hover:bg-white/20;
            }
            .nav-link-desktop svg { @apply h-6 w-6 shrink-0; }
 
            /* Rótulo do link: some quando a sidebar está colapsada */
            .rotulo-link { @apply whitespace-nowrap font-poppins font-medium transition-opacity duration-200; }
            #sidebar.colapsada .rotulo-link { @apply pointer-events-none opacity-0; }
            #sidebar.colapsada .nav-link-desktop { @apply justify-center px-0; }
        
       

       /* ============================================================
           2. SCROLLBAR PRINCIPAL (Aplicada apenas no Conteúdo)
           A barra agora nasce abaixo do Header!
        ============================================================= */
        #area-conteudo::-webkit-scrollbar {
            width: 34px; 
        }

        /* 2. O trilho é "espremido" no meio por bordas transparentes */
        #area-conteudo::-webkit-scrollbar-track {
            @apply bg-rosa-6 dark:bg-preto2; 
            border-left: 12px solid transparent; 
            border-right: 12px solid transparent; 
            background-clip: padding-box; 
        }

        /* 3. O pegador também é "espremido" no meio */
        #area-conteudo::-webkit-scrollbar-thumb {
            @apply bg-roxo1 dark:bg-roxo1; 
            border-radius: 20px;
            border: 12px solid transparent; 
            background-clip: padding-box;
        }

        #area-conteudo::-webkit-scrollbar-thumb:hover {
            @apply bg-primary dark:bg-primary;
        }

        /* 4. Os botões das patinhas */
        #area-conteudo::-webkit-scrollbar-button:single-button {
            display: block;
            height: 34px; 
            background-color: transparent; 
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain; 
        }

        /* SETA DE CIMA */
        #area-conteudo::-webkit-scrollbar-button:single-button:vertical:decrement {
            background-image: url("<?= e(URL_BASE) ?>/assets/img/patinha-cima.png");
        }

        /* SETA DE BAIXO */
        #area-conteudo::-webkit-scrollbar-button:single-button:vertical:increment {
            background-image: url("<?= e(URL_BASE) ?>/assets/img/patinha-baixo.png");
        }

        /* ============================================================
           SCROLLBAR DO MENU LATERAL (Sidebar)
        ============================================================= */
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


        /* ===== Cards ===== */
            .card-padrao {
                @apply rounded-2xl bg-surface p-6 shadow-md;
            }
            .card-destaque { /* card rosa da Home com a sombra */
                @apply rounded-xl bg-rosa-1 p-6 sm:p-8 border border-rosa-2/50
                       shadow-[6px_6px_0px_rgba(44,44,44,0.15)] dark:shadow-[6px_6px_0px_rgba(255,255,255,0.05)];
            }
        }
    </style>