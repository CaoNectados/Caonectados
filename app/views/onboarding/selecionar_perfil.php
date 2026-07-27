<?php 
require_once __DIR__ . '/../templates/header.php'; 
?>

    <!-- Precisa estilizar aqui, fiz somente o BACKEND (apagar esse comentário depois de estilizar) -->

        <header>
            <h1>Bem vindo(a) ao CãoNectados</h1>
            <h2>Como você vai usar o CãoNectados?</h2>
            <p>Escolha o perfil que melhor descreve você para personalizarmos sua experiência.</p>
        </header>
        <section>
            <a href="<?= URL_BASE ?>/onboarding/tutor">
                <button type="button">Quero Adotar</button>
            </a>
            <a href="<?= URL_BASE ?>/onboarding/protetor">
                <button type="button">Protetor Independente</button>
            </a>
            <a href="<?= URL_BASE ?>/onboarding/ong">
                <button type="button">Sou uma ONG</button>
            </a>
        </section>

    
<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>


