<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Listar Bairros</title>
</head>

<body>
    <h1>Bairros Cadastrados</h1>

    <a href="/regioes/cadastrar">Cadastrar Novo Bairro</a>
    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($regioes)): ?>
                <?php foreach ($regioes as $r): ?>
                    <tr>
                        <td><?= $r->getRegiaoId(); ?></td>
                        <td><?= htmlspecialchars($r->getNomeRegiao()); ?></td>
                        <td>
                            <a href="/regioes/editar?id=<?= $r->getRegiaoId(); ?>">Editar</a> |
                            <a href="/regioes/excluir?id=<?= $r->getRegiaoId(); ?>">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nenhum bairro cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>