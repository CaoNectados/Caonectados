<?php
// app/helpers/ViewHelper.php

if (!function_exists('e')) {
    function e(?string $valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function renderIconeMenu(
    string $nomeArquivo,
    string $descricao,
    string $classesCor = 'h-6 w-6 shrink-0 text-white'
): string {
    $caminhoFisico  = __DIR__ . '/../../public/assets/icons/navbar/' . $nomeArquivo;
    
    // CORREÇÃO: Adicionamos a URL_BASE aqui para garantir que o navegador encontre o PNG!
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

   // EXCEÇÃO PARA O SEU PNG: 
   // Inverte a cor no modo escuro e dá um zoom (scale-150) para compensar a margem transparente
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