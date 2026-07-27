<?php 
require_once __DIR__ . '/../templates/header.php'; 
?>
    <!-- Precisa estilizar aqui, fiz somente o BACKEND (apagar esse comentário depois de estilizar) -->

        <header>
            <h1>Cadastre-se</h1>
        </header>

        <form action="<?= URL_BASE ?>/cadastro" method="POST">

            <div>
                <label for="email" style="display:none;">E-mail</label>
                <input type="email" name="email" id="email" required placeholder="E-mail">
            </div>

            <div>
                <label for="senha" style="display:none;">Crie uma senha</label>
                <input type="password" name="senha" id="senha" required placeholder="Crie uma senha">
            </div>

            <div>
                <label for="senha_confirmacao" style="display:none;">Confirme sua senha</label>
                <input type="password" name="senha_confirmacao" id="senha_confirmacao" required placeholder="Confirme sua senha">
            </div>

            <button type="submit">Cadastrar</button>
        </form>

        <div>
            <a href="<?= URL_BASE ?>/login">Faça Login Aqui!</a>
        </div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>