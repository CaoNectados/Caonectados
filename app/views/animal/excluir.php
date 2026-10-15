<?php
$animal=$_SESSION['animal'] ?? ($animal ?? null);
if (!$animal) { http_response_code(404); require __DIR__.'/../errors/404.php'; return; }
$confirmacao=['titulo'=>'Desativar animal','nome'=>$animal->getNome(),'id'=>$animal->getAnimalId(),'voltar'=>'/animal','acao'=>'/animal/excluir','mensagem'=>'O animal ficará oculto do catálogo. Você poderá reativá-lo no gerenciamento de animais.','botao'=>'Desativar animal'];
require __DIR__.'/../templates/confirmar_acao.php';
