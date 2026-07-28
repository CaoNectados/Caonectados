<?php 
require_once __DIR__ . '/../templates/header.php'; 
?>
    <!-- Precisa estilizar aqui, fiz somente o BACKEND (apagar esse comentário depois de estilizar) -->

        <header>
            <h1>Bem-vindo de volta!</h1>
            <p>Faça login para acessar o CãoNectados</p>
        </header>

        <!-- O formulário aponta para a rota POST /login usando a URL_BASE para não quebrar o caminho -->
        <form action="<?= URL_BASE ?>/login" method="POST">
            <div>
                <label for="email" style="display:none;">E-mail</label>
                <input type="email" name="email" id="email" required placeholder="Digite seu e-mail">
            </div>

            <div>
                <label for="senha" style="display:none;">Senha</label>
                <input type="password" name="senha" id="senha" required placeholder="Digite sua senha">
            </div>
            <a href="<?= URL_BASE ?>/esqueci-senha">Esqueceu sua senha?</a>

            <button type="submit">Entrar</button>
        </form>

        <div>
            <p>Ainda não tem uma conta? <a href="<?= URL_BASE ?>/cadastro">Cadastre-se Aqui!</a></p>
        </div>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>