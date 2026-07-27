<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Bairro</title>
</head>

<body>
    <h1>Cadastrar Novo Bairro</h1>

    <form action="/regioes/salvar" method="POST">
        <label for="nome_regiao">Nome do Bairro:</label>
        <input type="text" id="nome_regiao" name="nome_regiao" required>
        <br><br>
        <button type="submit">Salvar Bairro</button>
    </form>

    <br>
    <a href="/regioes">Voltar para a lista</a>
</body>

</html>