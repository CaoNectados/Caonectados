<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Raças</title>
</head>

<body>
    <h1>Raças Cadastradas</h1>

    <a href="/racas/cadastrar">Cadastrar Nova Raça</a> | <a href="/especies">Ir para Espécies</a>
    <br><br>

    <!-- Formulário de Filtro -->
    <form method="GET" action="/racas">
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
                <th>Nome da Raça</th>
                <th>Espécie Pertencente</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($racas)): ?>
                <?php foreach ($racas as $r): ?>
                    <tr>
                        <td><?= $r->getId(); ?></td>
                        <td><?= htmlspecialchars($r->getNome()); ?></td>
                        <td><?= htmlspecialchars($r->getEspecie()->getNome()); ?></td>

                        <!-- Nova coluna mostrando se está Ativo ou Inativo -->
                        <td><?= $r->isAtivo() ? 'Ativo' : 'Inativo'; ?></td>
                        <td>
                            <a href="/racas/editar?id=<?= $r->getId(); ?>">Editar</a> |

                            <!-- Muda a ação dependendo do status atual -->
                            <?php if ($r->isAtivo()): ?>
                                <a href="/racas/excluir?id=<?= $r->getId(); ?>" style="color: red;">Desativar</a>
                            <?php else: ?>
                                <a href="/racas/reativar?id=<?= $r->getId(); ?>" style="color: green;">Ativar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhuma raça encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>