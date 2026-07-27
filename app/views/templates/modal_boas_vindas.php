<?php
if (isset($_SESSION['boas_vindas_tipo']) && isset($_SESSION['boas_vindas_nome'])):
    $tipo = $_SESSION['boas_vindas_tipo'];
    $nome = $_SESSION['boas_vindas_nome'];
    
    if ($tipo === 'adotante') {
        $saudacao = "Oba, " . e($nome) . "! 🎉";
        $mensagemCurta = "Seu cadastro foi realizado com sucesso! Você tem <strong class='text-pink-500'>10 petiscos por dia</strong> para demonstrar interesse nos animais.";
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

<!-- Modal de Boas Vindas e Instruções -->
<div id="modal-boas-vindas" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60">
    <div class="bg-white rounded-2xl p-6 md:p-8 text-center max-w-lg w-full shadow-2xl transform transition-all relative">
        
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
            <span class="text-green-500 text-3xl font-bold">✓</span>
        </div>

        <h2 class="text-2xl font-bold mb-2 font-shantell text-gray-800">
            <?= $saudacao ?>
        </h2>
        
        <p class="text-gray-600 mb-4 font-poppins text-sm md:text-base">
            <?= $mensagemCurta ?>
        </p>

        <!-- Caixa de Dicas Rápidas -->
        <div class="bg-gray-50 rounded-xl p-4 text-left text-xs md:text-sm text-gray-600 mb-6 border border-gray-200 shadow-inner">
            <h3 class="font-bold text-gray-800 mb-2 font-shantell">Dicas de uso:</h3>
            <ul class="list-disc pl-4 space-y-2 font-poppins">
                <?php foreach ($dicas as $dica): ?>
                    <li><?= $dica ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <button onclick="fecharModalBoasVindas()" class="w-full bg-green-500 text-white font-bold py-3 px-4 rounded-xl transition duration-300 hover:bg-green-600 hover:shadow-md font-poppins">
            Entendido e Continuar
        </button>
    </div>
</div>

<script>
    function fecharModalBoasVindas() {
        const modal = document.getElementById('modal-boas-vindas');
        if (modal) {
            modal.remove();
        }
    }
</script>

<?php 
    // Limpa a sessão para exibir apenas uma vez após o login/cadastro
    unset($_SESSION['boas_vindas_tipo']); 
    unset($_SESSION['boas_vindas_nome']); 
endif; 
?>