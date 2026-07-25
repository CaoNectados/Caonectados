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
            <h1>Login</h1>
        </header>

        <?php if (isset($erro)): ?>
            <div style="color: red;">
                <p><?= htmlspecialchars($erro) ?></p>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <div>
                <label for="email" style="display:none;">E-mail</label>
                <input type="email" name="email" id="email" required placeholder="E-mail">
            </div>

            <div>
                <label for="senha" style="display:none;">Senha</label>
                <input type="password" name="senha" id="senha" required placeholder="Senha">
                <a href="/esqueci-senha">Esqueci a senha</a>
            </div>

            <button type="submit">Entrar</button>
        </form>

        <div>
            <a href="/cadastro">Criar Conta</a>
        </div>
    </main>
</body>
</html>