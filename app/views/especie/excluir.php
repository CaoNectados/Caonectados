<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Excluir Espécie</h1>
    <p>Tem certeza que deseja excluir a espécie <strong><?= htmlspecialchars($especie->getNome()); ?></strong>?</p>
    <p><em>Atenção: Todas as raças vinculadas a esta espécie também serão excluídas!</em></p>
    <form action="<?= URL_BASE ?>/admin/especie/deletar?id=<?= $especie->getId(); ?>" method="POST">
        <button type="submit">Sim, Confirmar Exclusão</button>
    </form>
    <br><a href="<?= URL_BASE ?>/admin/especie">⬅️ Cancelar</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>