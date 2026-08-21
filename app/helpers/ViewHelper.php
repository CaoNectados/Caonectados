<?php
// app/helpers/ViewHelper.php

// Usado por: dezenas de trechos em app/views/templates/header.php, footer.php, modal_boas_vindas.php, home/index.php e onboarding/adotante_onboarding.php (escape de saída HTML)
if (!function_exists('e')) {
    function e(?string $valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Usado por: templates/footer.php e views com <script>/<link> próprios — versiona arquivos
// estáticos (JS/CSS) via query string a partir do horário de modificação do arquivo, para
// que o navegador busque a versão nova assim que o arquivo é editado em vez de servir uma
// cópia em cache (ex.: o bug de "CaonectadosValidator.validarEmail is not a function" após
// validacoes.js ganhar funções novas, mas o browser continuar com o arquivo antigo em cache).
if (!function_exists('asset')) {
    function asset(string $caminhoRelativo): string
    {
        $baseUrl = defined('URL_BASE') ? URL_BASE : '';
        $caminhoRelativo = ltrim($caminhoRelativo, '/');
        $caminhoFisico = __DIR__ . '/../../public/' . $caminhoRelativo;

        $versao = is_file($caminhoFisico) ? filemtime($caminhoFisico) : time();

        return $baseUrl . '/' . $caminhoRelativo . '?v=' . $versao;
    }
}

// Usado por: app/views/templates/header.php (itens do menu de navegação)
function renderIconeMenu(
    string $nomeArquivo,
    string $descricao,
    string $classesCor = 'h-6 w-6 shrink-0 text-white'
): string {
    $caminhoFisico  = __DIR__ . '/../../public/assets/icons/navbar/' . $nomeArquivo;

    // Precisa do prefixo URL_BASE para o navegador resolver o caminho público do ícone
    $baseUrl = defined('URL_BASE') ? URL_BASE : '';
    $caminhoPublico = $baseUrl . '/assets/icons/navbar/' . $nomeArquivo;
    
    $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

    $ehSvgValido = $extensao === 'svg' && is_file($caminhoFisico) && is_readable($caminhoFisico);

    if ($ehSvgValido) {
        $conteudoSvg = file_get_contents($caminhoFisico);
        $conteudoSvg = preg_replace('/<\?xml.*?\?>/i', '', $conteudoSvg);
        $conteudoSvg = preg_replace('/fill="(?!none)[^"]*"/i', 'fill="currentColor"', $conteudoSvg);
        $conteudoSvg = preg_replace('/stroke="(?!none)[^"]*"/i', 'stroke="currentColor"', $conteudoSvg);

        if (preg_match('/<svg\b([^>]*)>/i', $conteudoSvg, $m)) {
            $atributosOriginais = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $m[1]);
            $novaAbertura = sprintf(
                '<svg%s class="%s" aria-hidden="true" focusable="false">',
                $atributosOriginais,
                e($classesCor)
            );
            $conteudoSvg = preg_replace('/<svg\b[^>]*>/i', $novaAbertura, $conteudoSvg, 1);
        }

        return $conteudoSvg;
    }

   // PNG: soma a classe 'transform' às classes de cor recebidas (necessária para o CSS de ajuste do ícone)
   $classesFinais = $classesCor;
   if ($extensao === 'png') {
       $classesFinais .= '  transform'; 
   }

   return sprintf(
       '<img src="%s" alt="" aria-hidden="true" class="%s">',
       e($caminhoPublico),
       e($classesFinais)
   );
}