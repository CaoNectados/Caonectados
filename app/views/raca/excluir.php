<?php
$confirmacao=['titulo'=>'Desativar raça','nome'=>$raca->getNome(),'id'=>$raca->getId(),'voltar'=>'/admin/gerenciar-especies-racas','acao'=>'/admin/raca/deletar?id='.(int)$raca->getId(),'mensagem'=>'A raça deixa de aparecer em novos cadastros. Os animais e históricos existentes são preservados.','botao'=>'Desativar raça'];
require __DIR__.'/../templates/confirmar_acao.php';
