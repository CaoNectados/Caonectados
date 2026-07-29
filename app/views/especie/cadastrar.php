<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Cadastrar Nova Espécie</h1>
    <form action="<?= URL_BASE ?>/admin/especie/salvar" method="POST">
        <label for="nome">Nome da Espécie:</label>
        <input type="text" id="nome" name="nome" placeholder="Ex: Cão, Gato..." required>
        <br><br>
        <button type="submit">Salvar</button>
    </form>
    <br><a href="<?= URL_BASE ?>/admin/especie">Voltar</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>