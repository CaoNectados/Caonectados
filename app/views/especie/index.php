<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Espécies</title>
</head>

<body>
    <h1>Espécies Cadastradas</h1>

    <a href="/especies/cadastrar">Cadastrar Nova Espécie</a> | <a href="/racas">Ir para Raças</a>
    <br><br>

    <!-- Formulário de Filtro -->
    <form method="GET" action="/especies">
        <label for="status">Filtrar por Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="todos" <?= ($_GET['status'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>Todos</option>
            <option value="ativos" <?= ($_GET['status'] ?? '') === 'ativos' ? 'selected' : '' ?>>Ativos</option>
            <option value="inativos" <?= ($_GET['status'] ?? '') === 'inativos' ? 'selected' : '' ?>>Inativos</option>
        </select>
    </form>
    <br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($especies)): ?>
                <?php foreach ($especies as $e): ?>
                    <tr>
                        <td><?= $e->getId(); ?></td>
                        <td><?= htmlspecialchars($e->getNome()); ?></td>
                        <!-- Nova coluna mostrando se está Ativo ou Inativo -->
                        <td><?= $e->isAtivo() ? 'Ativo' : 'Inativo'; ?></td>
                        <td>
                            <a href="/especies/editar?id=<?= $e->getId(); ?>">Editar</a> |

                            <!-- Muda a ação dependendo do status atual -->
                            <?php if ($e->isAtivo()): ?>
                                <a href="/especies/excluir?id=<?= $e->getId(); ?>" style="color: red;">Desativar</a>
                            <?php else: ?>
                                <a href="/especies/reativar?id=<?= $e->getId(); ?>" style="color: green;">Ativar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nenhuma espécie encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>