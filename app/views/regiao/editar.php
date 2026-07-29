<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Editar Bairro</h1>

    <!-- Aqui vai dar erro no $regiao pq ele n'ao foi criado aqui, mas foi criado na Controller que cria, entao ta tudo certo -->
    <form action="<?= URL_BASE ?>/admin/regiao/atualizar?id=<?= $regiao->getRegiaoId(); ?>" method="POST">
        <label>ID do Bairro:</label>
        <input type="text" value="<?= $regiao->getRegiaoId(); ?>" disabled>
        <br><br>
        <label for="nome_regiao">Nome do Bairro:</label>
        <input type="text" id="nome_regiao" name="nome_regiao" value="<?= htmlspecialchars($regiao->getNomeRegiao()); ?>" required>
        <br><br>
        <button type="submit">Atualizar Bairro</button>
    </form>

    <br>
    <a href="<?= URL_BASE ?>/admin/regiao">Voltar para a lista</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>