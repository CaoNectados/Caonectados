<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Editar Espécie</h1>
    <form action="<?= URL_BASE ?>/admin/especie/atualizar?id=<?= $especie->getId(); ?>" method="POST">
        <label>ID:</label> <input type="text" value="<?= $especie->getId(); ?>" disabled><br><br>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($especie->getNome()); ?>" required>
        <br><br>
        <button type="submit">Atualizar</button>
    </form>
    <br><a href="<?= URL_BASE ?>/admin/especie">Voltar</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>