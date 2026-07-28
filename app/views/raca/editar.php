<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Raça</title>
</head>

<body>
    <h1>Editar Raça</h1>
    <form action="/racas/atualizar?id=<?= $raca->getId(); ?>" method="POST">
        <label for="especie_id">Espécie Pertencente:</label>
        <select id="especie_id" name="especie_id" required>
            <?php foreach ($especies as $e): ?>
                <option value="<?= $e->getId(); ?>" <?= $e->getId() === $raca->getEspecieId() ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($e->getNome()); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="nome">Nome da Raça:</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($raca->getNome()); ?>" required>
        <br><br>
        <button type="submit">Atualizar Raça</button>
    </form>
    <br><a href="/racas">Voltar</a>
</body>

</html>