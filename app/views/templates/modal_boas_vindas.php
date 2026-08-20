<?php
if (isset($_SESSION['boas_vindas_tipo']) && isset($_SESSION['boas_vindas_nome'])):
    $tipo = $_SESSION['boas_vindas_tipo'];
    $nome = $_SESSION['boas_vindas_nome'];
    
    if ($tipo === 'adotante') {
        $saudacao = "Oba, " . e($nome) . "! 🎉";
        $mensagemCurta = "Seu cadastro foi realizado com sucesso! Você tem <strong class='text-rosaAlerta'>10 petiscos por dia</strong> para demonstrar interesse nos animais.";
        $dicas = [
            "Use o catálogo para encontrar seu novo melhor amigo.",
            "Ao dar um 'Petisco', o responsável pelo animal será notificado.",
            "Lembre-se: a adoção é um ato de amor e responsabilidade para a vida toda!"
        ];
    } else {
        $saudacao = "Parabéns, " . e($nome) . "! 🐾";
        $mensagemCurta = "Sua conta foi <strong>validada e aprovada</strong> pelo nosso painel administrativo!";
        $dicas = [
            "Acesse seu Dashboard para cadastrar animais.",
            "Mantenha o status dos pets sempre atualizados.",
            "Fique de olho nas notificações para responder aos 'Petiscos' dos adotantes."
        ];
    }
?>

<div id="modal-boas-vindas" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-preto/60">
    <div class="bg-surface rounded-2xl p-6 md:p-8 text-center max-w-lg w-full shadow-2xl transform transition-all">
        
        <!-- Caminho reservado para a imagem/gif de "aprovado" — troque o arquivo em
             public/assets/img/solicitacao-aprovada.gif quando tiver um pronto. Enquanto
             não existir, o <img> some e o selo verde de check (abaixo) serve de fallback. -->
        <img src="<?= URL_BASE ?>/assets/img/solicitacao-aprovada.gif" alt="" class="mx-auto mb-6 h-24 w-24 object-contain" onerror="this.style.display='none'; document.getElementById('selo-check-fallback').classList.remove('hidden');">

        <div id="selo-check-fallback" class="hidden mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-sucesso/20 mb-6">
            <span class="text-sucesso text-3xl font-bold">✓</span>
        </div>

        <h2 class="text-2xl font-bold mb-2 font-shantell text-text-dark">
            <?= $saudacao ?>
        </h2>
        
        <p class="text-text-muted mb-4 font-poppins text-sm md:text-base">
            <?= $mensagemCurta ?>
        </p>

        <div class="bg-rosa-1/30 rounded-xl p-4 text-left text-xs md:text-sm text-text-dark/80 mb-6 border border-rosa-2 shadow-inner">
            <h3 class="font-bold text-text-dark mb-2 font-shantell">Dicas de uso:</h3>
            <ul class="list-disc pl-4 space-y-2 font-poppins">
                <?php foreach ($dicas as $dica): ?>
                    <li><?= $dica ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <button onclick="fecharModalBoasVindas()" class="w-full bg-sucesso text-white font-bold py-3 px-4 rounded-xl transition duration-300 hover:opacity-90 hover:shadow-md font-poppins">
            Entendido e Continuar
        </button>
    </div>
</div>

<script>
    function fecharModalBoasVindas() {
        const modal = document.getElementById('modal-boas-vindas');
        if (modal) modal.remove();
    }
</script>

<?php 
    unset($_SESSION['boas_vindas_tipo']); 
    unset($_SESSION['boas_vindas_nome']); 
endif; 
?>