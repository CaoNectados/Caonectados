<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CãoNectados - Login</title>
</head>
<body>
    <main>
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

            <button type="submit">Entrar</button>
        </form>

        <div>
            <p>Ainda não tem uma conta? <a href="<?= URL_BASE ?>/cadastro">Cadastre-se Aqui!</a></p>
        </div>
    </main>

    <!-- Estrutura HTML do Modal de Erro do Login -->
    <div id="modal-erro-login" class="hidden" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; max-width: 400px;">
            <h2 style="color: #e3342f; margin-bottom: 1rem;">Falha no Login</h2>
            <p id="texto-modal-erro-login" style="margin-bottom: 1.5rem;"></p>
            <button onclick="document.getElementById('modal-erro-login').style.display = 'none'" style="padding: 0.5rem 1rem; cursor: pointer;">
                Tentar Novamente
            </button>
        </div>
    </div>

    <!-- Script que dispara o modal caso exista um erro na sessão de login -->
    <?php if (isset($_SESSION['erro_login'])): ?>
    <script>
        const mensagemErro = <?= json_encode($_SESSION['erro_login']) ?>;
        
        document.getElementById('texto-modal-erro-login').innerText = mensagemErro;
        
        const modal = document.getElementById('modal-erro-login');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    </script>
    <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

</body>
</html>