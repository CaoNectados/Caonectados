<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title><?= isset($assunto_email) ? $assunto_email : 'CãoNectados' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Shantell+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos padrão (Modo Claro) */
        .email-bg {
            background-color: #F9F9F9 !important;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .email-container {
            background-color: #FEF8FB !important;
            color: #2C2C2C !important;
        }

        .text-main {
            color: #2C2C2C !important;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .text-muted {
            color: #9E9E9E !important;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .text-footer {
            color: #A0AEC0 !important;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .code-box {
            background-color: #FBDBEB !important;
            border: 2px dashed #FA5672 !important;
        }

        .code-text {
            color: #FA5672 !important;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .header-bg {
            background-color: #4F4873 !important;
        }

        .footer-bg {
            background-color: #2D2444 !important;
        }

        .btn-link {
            background-color: #FA5672 !important;
            color: #FFFFFF !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .title-font {
            font-family: 'Shantell Sans', cursive, Arial !important;
        }

        /* Modo Escuro Automático */
        @media (prefers-color-scheme: dark) {
            .email-bg {
                background-color: #0C0B28 !important;
            }

            .email-container {
                background-color: #150D37 !important;
                color: #FFFFFF !important;
            }

            .text-main {
                color: #FFFFFF !important;
            }

            .text-muted {
                color: #CDB3C0 !important;
            }

            .text-footer {
                color: #9E9E9E !important;
            }

            .code-box {
                background-color: #2E282B !important;
                border: 2px dashed #FA5672 !important;
            }

            .code-text {
                color: #FA5672 !important;
            }

            .header-bg {
                background-color: #111042 !important;
            }

            .footer-bg {
                background-color: #171415 !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0;" class="email-bg">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; padding: 40px 0;" class="email-bg">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);" class="email-container">

                    <!-- HEADER DO EMAIL -->
                    <tr>
                        <td align="center" style="padding: 30px 0;" class="header-bg">
                            <img src="https://raw.githubusercontent.com/CaoNectados/ModeloProjetoIntegrador/refs/heads/main/public/assets/img/logo.png" alt="CãoNectados" width="130" style="display: block; border: 0; margin: 0 auto 10px auto;" />
                            <h1 class="title-font" style="color: #ffffff; font-size: 22px; margin: 0; font-weight: normal;">CãoNectados 🐾</h1>
                        </td>
                    </tr>

                    <!-- CONTEÚDO PRINCIPAL (DINÂMICO) -->
                    <tr>
                        <td style="padding: 40px 30px; text-align: left;">

                            <?php if (!empty($nome_usuario)): ?>
                                <h2 class="title-font text-main" style="font-size: 20px; margin-top: 0; font-weight: normal;">
                                    Olá, <?= htmlspecialchars($nome_usuario) ?>!
                                </h2>
                            <?php endif; ?>

                            <?php if (!empty($titulo_mensagem)): ?>
                                <h3 class="text-main" style="font-size: 18px; margin-top: 0; margin-bottom: 15px;">
                                    <?= $titulo_mensagem ?>
                                </h3>
                            <?php endif; ?>

                            <p style="font-size: 15px; line-height: 1.6; margin-bottom: 25px;" class="text-muted">
                                <?= $mensagem_corpo ?? '' ?> </p>

                            <!-- CAIXA DE CÓDIGO (Só renderiza se o Controller mandar um código) -->
                            <?php if (!empty($codigo_destaque)): ?>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="center" style="padding: 20px 0;">
                                            <div class="code-box" style="border-radius: 12px; display: inline-block; padding: 15px 40px;">
                                                <span class="code-text" style="font-size: 32px; font-weight: bold; letter-spacing: 6px;">
                                                    <?= $codigo_destaque ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <!-- BOTÃO DE AÇÃO (Só renderiza se o Controller mandar um link) -->
                            <?php if (!empty($link_botao) && !empty($texto_botao)): ?>
                                <div style="text-align: center; margin: 30px 0;">
                                    <a href="<?= $link_botao ?>" class="btn-link">
                                        <?= $texto_botao ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($mensagem_rodape)): ?>
                                <p style="font-size: 13px; line-height: 1.5; margin-top: 25px; text-align: center;" class="text-muted">
                                    <?= $mensagem_rodape ?>
                                </p>
                            <?php endif; ?>

                        </td>
                    </tr>

                    <!-- FOOTER PADRÃO -->
                    <tr>
                        <td align="center" style="padding: 20px 30px;" class="footer-bg">
                            <p class="text-footer" style="font-size: 12px; margin: 0; line-height: 1.4;">
                                &copy; <?= date('Y') ?> CãoNectados. Todos os direitos reservados.<br>
                                Foz do Iguaçu - PR | Intermediação de Adoção Responsável.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>