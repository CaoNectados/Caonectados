<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Cadastrar Novo Bairro</h1>

    <form action="<?= URL_BASE ?>/admin/regiao/salvar" method="POST">
        <label for="nome_regiao">Nome do Bairro:</label>
        <input type="text" id="nome_regiao" name="nome_regiao" required>
        <br><br>
        <button type="submit">Salvar Bairro</button>
    </form>

    <br>
    <a href="<?= URL_BASE ?>/admin/regiao">Voltar para a lista</a>

<?php 
require_once __DIR__ . '/../templates/footer.php';
?>