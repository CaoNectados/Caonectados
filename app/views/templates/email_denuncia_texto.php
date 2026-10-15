<?php
// Texto simples: enviado pelo cliente de email do usuário, sem gravação de denúncia.
?>
Olá, equipe CãoNectados.

Gostaria de denunciar o anúncio abaixo para análise.

DADOS DO ANÚNCIO
<?php if ($post): ?>
ID do pet: #<?= (int)$post['animal_id'] ?>

Nome do pet: <?= $limparLinha($post['nome']) ?>

Espécie: <?= $limparLinha($post['especie_nome']) ?>

Raça: <?= $limparLinha($post['raca_nome']) ?>

Responsável pelo anúncio: <?= $limparLinha($post['nome_fantasia'] ?: 'Protetor independente') ?> (perfil #<?= (int)$post['protetor_id'] ?>)

Link do anúncio: <?= $linkAnimal ?>

Página do responsável: <?= URL_BASE.'/pagina?id='.(int)$post['protetor_id'] ?>

<?php else: ?>
Link do anúncio ou perfil: [cole o link aqui]
<?php endif; ?>

RELATO
Motivo da denúncia: [informe o motivo]
O que aconteceu: [descreva a situação]
Quando aconteceu: [informe a data ou período]

COMPROVANTES
[Anexe capturas de tela ou outros comprovantes disponíveis antes de enviar.]

Solicito a análise da equipe sobre esta situação.
