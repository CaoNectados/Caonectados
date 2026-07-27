<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Excluir Espécie</title>
</head>

<body>
    <h1>Excluir Espécie</h1>
    <p>Tem certeza que deseja excluir a espécie <strong><?= htmlspecialchars($especie->getNome()); ?></strong>?</p>
    <p><em>Atenção: Todas as raças vinculadas a esta espécie também serão excluídas!</em></p>
    <form action="/especies/deletar?id=<?= $especie->getId(); ?>" method="POST">
        <button type="submit">Sim, Confirmar Exclusão</button>
    </form>
    <br><a href="/especies">⬅️ Cancelar</a>
</body>

</html>