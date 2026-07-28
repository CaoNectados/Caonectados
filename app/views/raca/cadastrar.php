<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Raça</title>
</head>

<body>
    <h1>Cadastrar Nova Raça</h1>
    <form action="/racas/salvar" method="POST">
        <label for="especie_id">Espécie Pertencente:</label>
        <select id="especie_id" name="especie_id" required>
            <option value="">-- Selecione uma Espécie --</option>
            <?php foreach ($especies as $e): ?>
                <option value="<?= $e->getId(); ?>"><?= htmlspecialchars($e->getNome()); ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="nome">Nome da Raça:</label>
        <input type="text" id="nome" name="nome" placeholder="Ex: Poodle, Siamês..." required>
        <br><br>
        <button type="submit">Salvar Raça</button>
    </form>
    <br><a href="/racas">Voltar</a>
</body>

</html>