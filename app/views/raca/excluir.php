<?php 
require_once __DIR__ . '/../templates/header.php';
?>
    <h1>Excluir Raça</h1>
    <p>Tem certeza que deseja excluir a raça <strong><?= htmlspecialchars($raca->getNome()); ?></strong>?</p>
    <form action="<?= URL_BASE ?>/admin/raca/deletar?id=<?= $raca->getId(); ?>" method="POST">
        <button type="submit">Sim, Confirmar Exclusão</button>
    </form>
    <br><a href="<?= URL_BASE ?>/admin/raca/">Cancelar</a>
<?php 
require_once __DIR__ . '/../templates/footer.php';
?>