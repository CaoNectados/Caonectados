<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CãoNectados - Cadastre-se</title>
</head>
<body>
    <main>
        <header>
            <h1>Cadastre-se</h1>
        </header>

        <form action="<?= URL_BASE ?>/cadastro" method="POST">
            <div>
                <label for="nome" style="display:none;">Nome completo</label>
                <input type="text" name="nome" id="nome" required placeholder="Nome completo">
            </div>

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
    </main>

    <!-- NOVO: Estrutura HTML do Modal de Erro (Estilize com Tailwind no seu front-end) -->
    <div id="modal-erro" class="hidden" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; max-width: 400px;">
            <h2 style="color: #e3342f; margin-bottom: 1rem;">Ops! Algo deu errado</h2>
            <p id="texto-modal-erro" style="margin-bottom: 1.5rem;"></p>
            <button onclick="document.getElementById('modal-erro').style.display = 'none'" style="padding: 0.5rem 1rem; cursor: pointer;">
                Tentar Novamente
            </button>
        </div>
    </div>

    <!-- Script que dispara o modal caso exista um erro na sessão -->
    <?php if (isset($_SESSION['erro_cadastro'])): ?>
    <script>
        const mensagemErro = <?= json_encode($_SESSION['erro_cadastro']) ?>;
        
        document.getElementById('texto-modal-erro').innerText = mensagemErro;
        
        const modal = document.getElementById('modal-erro');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    </script>
    <?php unset($_SESSION['erro_cadastro']); ?>
    <?php endif; ?>

</body>
</html>