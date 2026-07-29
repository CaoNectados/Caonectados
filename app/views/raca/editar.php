<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Editar Raça</h1>
    <form action="<?= URL_BASE ?>/admin/raca/atualizar?id=<?= $raca->getId(); ?>" method="POST" autocomplete="off">
        <label for="especie_id">Espécie Pertencente:</label>
        <select id="especie_id" name="especie_id" required>
            <?php foreach ($especies as $e): ?>
                <option autocomplete="nope" value="<?= $e->getId(); ?>" <?= $e->getId() === $raca->getEspecieId() ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($e->getNome()); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="nome">Nome da Raça:</label>
        <input autocomplete="nope" type="text" id="nome" name="nome" value="<?= htmlspecialchars($raca->getNome()); ?>" required>
        <br><br>
        <button type="submit">Atualizar Raça</button>
    </form>
    <br><a href="<?= URL_BASE ?>/admin/raca">Voltar</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>