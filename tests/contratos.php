<?php
require __DIR__.'/../vendor/autoload.php';
$index=file_get_contents(__DIR__.'/../public/index.php');
preg_match_all('/\$router->(?:get|post)\([^,]+,\s*\x27([^\x27]+)\x27\)/',$index,$matches);
$total=0;
foreach($matches[1] as $acao){
    [$classe,$metodo]=explode('@',$acao);
    $classe='app\\controllers\\'.str_replace('/','\\',$classe);
    // O texto PHP contém barras escapadas nos literais de algumas rotas.
    $classe=str_replace('\\\\','\\',$classe);
    if(!class_exists($classe)||!method_exists($classe,$metodo))throw new Exception('Rota inválida: '.$acao);
    if(preg_match('/Chat|Denuncia|Relatorio|Contestacao|Notificacao/',$classe))throw new Exception('Módulo excluído acessível');
    $total++;
}
echo "$total contratos de rotas válidos; nenhum módulo excluído registrado.\n";
