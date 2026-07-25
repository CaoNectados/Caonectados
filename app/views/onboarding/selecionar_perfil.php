<?php

$conteudo = __FILE__;

if (!defined('ONBOARDING_LAYOUT')) {

    define('ONBOARDING_LAYOUT', true);

    ob_start();

?>

        <header>
            <h1>Bem vindo(a) ao CãoNectados</h1>
            <h2>Como você vai usar o CãoNectados?</h2>
            <p>Escolha o perfil que melhor descreve você para personalizarmos sua experiência.</p>
        </header>

        <section>
            <a href="<?= URL_BASE ?>/onboarding/adotante">
                <button type="button">Quero Adotar</button>
            </a>
            <a href="<?= URL_BASE ?>/onboarding/ong?tipo=PROTETOR">
                <button type="button">Protetor Independente</button>
            </a>
            <a href="<?= URL_BASE ?>/onboarding/ong?tipo=ONG">
                <button type="button">Sou uma ONG</button>
            </a>
        </section>

    <?php

$conteudo = ob_get_clean();

require_once __DIR__ . '/../templates/onboarding_layout.php';

return;

}

echo $conteudo;