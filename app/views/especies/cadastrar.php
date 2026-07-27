<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Espécie</title>
</head>

<body>
    <h1>Cadastrar Nova Espécie</h1>
    <form action="/especies/salvar" method="POST">
        <label for="nome">Nome da Espécie:</label>
        <input type="text" id="nome" name="nome" placeholder="Ex: Cão, Gato..." required>
        <br><br>
        <button type="submit">Salvar</button>
    </form>
    <br><a href="/especies">Voltar</a>
</body>

</html>