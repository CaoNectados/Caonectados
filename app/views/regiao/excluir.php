<?php
$confirmacao=['titulo'=>'Excluir região','nome'=>$regiao->getNomeRegiao(),'id'=>$regiao->getRegiaoId(),'voltar'=>'/admin/regiao','acao'=>'/admin/regiao/deletar?id='.(int)$regiao->getRegiaoId(),'mensagem'=>'Regiões usadas em cadastros não podem ser excluídas.','botao'=>'Excluir região'];
require __DIR__.'/../templates/confirmar_acao.php';
