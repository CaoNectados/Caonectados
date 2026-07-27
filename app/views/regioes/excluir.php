<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Excluir Bairro</title>
</head>

<body>
    <h1>Excluir Bairro</h1>
    <!-- Aqui vai dar erro no $regiao pq ele n'ao foi criado aqui, mas foi criado na Controller que cria, entao ta tudo certo -->
    <p>Tem certeza que deseja excluir o bairro <strong><?= htmlspecialchars($regiao->getNomeRegiao()); ?></strong> (ID: <?= $regiao->getRegiaoId(); ?>)?</p>

    <form action="/regioes/deletar?id=<?= $regiao->getRegiaoId(); ?>" method="POST">
        <button type="submit">Sim, Confirmar Exclusão</button>
    </form>

    <br>
    <a href="/regioes">Cancelar e Voltar</a>
</body>

</html>