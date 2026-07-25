<?php

$etapaAtual=2;
$totalEtapas=3;

require_once __DIR__ . '/../componentes/progresso.php';


?>

<form action="<?= URL_BASE ?>/onboarding/adotante/etapa2" method="POST">

<fieldset>

<legend>

Moradia

</legend>

<select name="tipo_moradia">

<option value="CASA">Casa</option>

<option value="APARTAMENTO">Apartamento</option>

<option value="SITIO">Sítio</option>

</select>

<select name="tamanho_interno">

<option value="PEQUENO">Pequeno</option>

<option value="MEDIO">Médio</option>

<option value="GRANDE">Grande</option>

</select>

</fieldset>

<?php

$voltar = URL_BASE . '/onboarding/adotante/etapa1';
$textoBotao = 'Próximo';

require_once __DIR__ . '/../componentes/botoes.php';

?>

</form>