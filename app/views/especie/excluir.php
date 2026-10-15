<?php
$confirmacao=['titulo'=>'Desativar espécie','nome'=>$especie->getNome(),'id'=>$especie->getId(),'voltar'=>'/admin/gerenciar-especies-racas','acao'=>'/admin/especie/deletar?id='.(int)$especie->getId(),'mensagem'=>'A espécie e suas raças deixam de aparecer em novos cadastros. Os animais e históricos existentes são preservados.','botao'=>'Desativar espécie'];
require __DIR__.'/../templates/confirmar_acao.php';
