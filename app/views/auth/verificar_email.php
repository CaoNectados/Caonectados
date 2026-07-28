<?php
require_once __DIR__ . '/../templates/header.php';
$emailUsuario = $_SESSION['email_pendente_verificacao'] ?? 'seu e-mail';
?>

<div class="max-w-md mx-auto p-6 bg-surface rounded-2xl shadow-md my-10 text-center">
    <div class="flex items-center justify-center gap-3 mb-6">
        <img src="<?= URL_BASE ?>/assets/img/logo.png" alt="CãoNectados" class="h-20 w-auto">
        <h1 class="font-shantell text-3xl font-bold text-primary">CãoNectados</h1>
    </div>

    <h2 class="text-xl font-bold mb-2 text-text-dark">Verifique seu e-mail</h2>
    
    <p class="text-sm text-text-muted mb-4">
        Enviamos um código de confirmação para o endereço: <br>
        <strong class="text-text-dark"><?= htmlspecialchars($emailUsuario) ?></strong>
    </p>

    <p class="text-xs text-text-muted mb-6">
        E-mail enviado por: <span class="font-medium text-primary">caonectados2026@gmail.com</span>
    </p>

    <form action="<?= URL_BASE ?>/verificar-email/validar" method="POST" class="space-y-4">
        <div>
            <input type="text" name="codigo" placeholder="Digite o código de 6 dígitos" maxlength="6" required
                   class="w-full text-center text-2xl tracking-widest p-3 border border-cinzaMarrom rounded-xl bg-branco font-poppins text-text-dark focus:border-primary focus:ring-2 focus:ring-roxinhoFofo focus:outline-none">
        </div>

        <button type="submit" class="btn-primario w-full">
            Confirmar Código
        </button>
    </form>

    <div class="mt-6 text-sm">
        <p id="texto-reenvio" class="text-text-muted">
            Não recebeu o código? 
            <a href="<?= URL_BASE ?>/reenviar-codigo" id="link-reenviar" class="text-primary font-bold hover:underline hidden">Reenviar código</a>
            <span id="contador-tempo">Reenviar em <span id="tempo">60</span>s</span>
        </p>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-200">
        <a href="<?= URL_BASE ?>/cadastro" class="text-xs text-rosaAlerta hover:underline font-medium inline-flex items-center gap-1">
            &#9998; Digitou o e-mail errado? Clique aqui para editar
        </a>
    </div>
</div>

<script>
    let tempoRestante = 60;
    const elementoTempo = document.getElementById('tempo');
    const contadorTempo = document.getElementById('contador-tempo');
    const linkReenviar = document.getElementById('link-reenviar');

    const timer = setInterval(() => {
        tempoRestante--;
        elementoTempo.textContent = tempoRestante;

        if (tempoRestante <= 0) {
            clearInterval(timer);
            contadorTempo.classList.add('hidden');
            linkReenviar.classList.remove('hidden');
        }
    }, 1000);
</script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>