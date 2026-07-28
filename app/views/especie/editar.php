<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Espécie</title>
</head>

<body>
    <h1>Editar Espécie</h1>
    <form action="/especies/atualizar?id=<?= $especie->getId(); ?>" method="POST">
        <label>ID:</label> <input type="text" value="<?= $especie->getId(); ?>" disabled><br><br>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($especie->getNome()); ?>" required>
        <br><br>
        <button type="submit">Atualizar</button>
    </form>
    <br><a href="/especies">Voltar</a>
</body>

</html>