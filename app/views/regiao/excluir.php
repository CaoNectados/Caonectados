<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Excluir Bairro</h1>
    <!-- Aqui vai dar erro no $regiao pq ele n'ao foi criado aqui, mas foi criado na Controller que cria, entao ta tudo certo -->
    <p>Tem certeza que deseja excluir o bairro <strong><?= htmlspecialchars($regiao->getNomeRegiao()); ?></strong> (ID: <?= $regiao->getRegiaoId(); ?>)?</p>

    <form action="<?= URL_BASE ?>/admin/regiao/deletar?id=<?= $regiao->getRegiaoId(); ?>" method="POST">
        <button type="submit">Sim, Confirmar Exclusão</button>
    </form>

    <br>
    <a href="<?= URL_BASE ?>/admin/regiao">Cancelar e Voltar</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>