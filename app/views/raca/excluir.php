<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Excluir Raça</title>
</head>

<body>
    <h1>Excluir Raça</h1>
    <p>Tem certeza que deseja excluir a raça <strong><?= htmlspecialchars($raca->getNome()); ?></strong>?</p>
    <form action="/racas/deletar?id=<?= $raca->getId(); ?>" method="POST">
        <button type="submit">Sim, Confirmar Exclusão</button>
    </form>
    <br><a href="/racas">Cancelar</a>
</body>

</html>