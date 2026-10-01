<?php
require __DIR__.'/../vendor/autoload.php';
date_default_timezone_set('America/Sao_Paulo');

// Banco exclusivamente em memória. Não conecta ao banco configurado da aplicação.
class BancoTeste extends PDO
{
    public function __construct() { parent::__construct('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }
    public function prepare(string $query,array $options=[]): PDOStatement|false
    {
        $query=str_replace([' FOR UPDATE','CURDATE()'],['',"DATE('now','localtime')"],$query);
        return parent::prepare($query,$options);
    }
}
$db=new BancoTeste();
$db->exec("CREATE TABLE USUARIO(usuario_id INTEGER PRIMARY KEY,nome TEXT,email TEXT,tipo_atual TEXT,perfis_ativos TEXT,status_conta TEXT,dt_nasc TEXT,deletado_em TEXT);
CREATE TABLE ADOTANTE(adotante_id INTEGER PRIMARY KEY,usuario_id INTEGER,tipo_moradia TEXT,tamanho_interno_moradia TEXT,detalhes TEXT);
CREATE TABLE PROTETOR(protetor_id INTEGER PRIMARY KEY,usuario_id INTEGER,nome_fantasia TEXT,validado INTEGER,deletado_em TEXT);
CREATE TABLE ANIMAL(animal_id INTEGER PRIMARY KEY,protetor_id INTEGER,nome TEXT,status TEXT,deletado_em TEXT);
CREATE TABLE SOLICITACAO_ADOCAO(solicitacao_id INTEGER PRIMARY KEY AUTOINCREMENT,adotante_id INTEGER,animal_id INTEGER,status_solicitacao TEXT,data_solicitacao TEXT DEFAULT CURRENT_TIMESTAMP,justificativa_recusa TEXT,data_finalizacao TEXT);
CREATE TABLE HISTORICO_SOLICITACAO(historico_id INTEGER PRIMARY KEY,solicitacao_id INTEGER,usuario_responsavel_id INTEGER,status_antigo TEXT,status_novo TEXT);
CREATE TABLE HISTORICO_STATUS_ANIMAL(historico_id INTEGER PRIMARY KEY,animal_id INTEGER,status_antigo TEXT,status_novo TEXT);
CREATE TABLE REDE(rede_id INTEGER PRIMARY KEY,protetor_id INTEGER,link_rede TEXT,tipo_rede TEXT);");
$db->exec("INSERT INTO USUARIO VALUES(1,'Adotante','a@example.test','adotante','adotante','ativo','1990-01-01',NULL),(2,'Responsável','p@example.test','protetor','protetor','ativo','1990-01-01',NULL),(3,'Outro','b@example.test','adotante','adotante','ativo','1990-01-01',NULL),(4,'Outro responsável','x@example.test','protetor','protetor','ativo','1990-01-01',NULL);
INSERT INTO ADOTANTE VALUES(1,1,'casa','medio','{}'),(2,3,'casa','medio','{}');
INSERT INTO PROTETOR VALUES(1,2,'Protetor',1,NULL),(2,4,'Outro',1,NULL);");
for($i=1;$i<=15;$i++) $db->exec("INSERT INTO ANIMAL VALUES($i,1,'Animal $i','disponivel',NULL)");
$eventos=[];
$service=new app\services\SolicitacaoAdocaoService($db,function($id,$estado)use(&$eventos,$db){if($db->inTransaction())throw new Exception('Evento antes do commit');$eventos[]=[$id,$estado];});
$total=0;
function conferir(bool $ok,string $nome):void {global $total;if(!$ok)throw new Exception($nome);$total++;echo "OK $nome\n";}
function falha(callable $acao,string $nome):void {try{$acao();}catch(Throwable $e){conferir(true,$nome);return;}conferir(false,$nome);}
$id=$service->solicitarAdocao(1,1,1);
conferir($id>0 && count($eventos)===1,'criação e evento após commit');
conferir($service->solicitarAdocao(1,1,1)===$id && count($eventos)===1,'repetição idempotente sem novo e-mail');
falha(fn()=>$service->cancelarSolicitacao($id,3),'cancelamento alheio rejeitado');
falha(fn()=>$service->colocarEmAnalise($id,2,4),'triagem por outro responsável rejeitada');
falha(fn()=>$service->recusar($id,1,2,''),'recusa exige motivo');
$service->colocarEmAnalise($id,1,2);
$outro=$service->solicitarAdocao(2,3,1);
$service->aprovar($id,1,2);
conferir($db->query("SELECT status FROM ANIMAL WHERE animal_id=1")->fetchColumn()==='adotado','aprovação atualiza animal');
conferir($db->query("SELECT status_solicitacao FROM SOLICITACAO_ADOCAO WHERE solicitacao_id=$outro")->fetchColumn()==='cancelada','aprovação cancela concorrente enviado');
falha(fn()=>$service->aprovar($outro,1,2),'segunda aprovação rejeitada');
falha(fn()=>$service->cancelarSolicitacao($id,1),'pedido aprovado não pode ser cancelado');
$db->exec("UPDATE ANIMAL SET status='disponivel' WHERE animal_id=1");
$novo=$service->solicitarAdocao(1,1,1);
conferir($novo!==$id,'adoção antiga não impede petisco após reativação');
$service->cancelarSolicitacao($novo,1);
conferir($db->query("SELECT status FROM ANIMAL WHERE animal_id=1")->fetchColumn()==='disponivel','cancelamento preserva disponibilidade');
$db->exec("UPDATE USUARIO SET dt_nasc='2100-01-01' WHERE usuario_id=1");
falha(fn()=>$service->solicitarAdocao(1,1,2),'nascimento futuro rejeitado');
$db->exec("UPDATE USUARIO SET dt_nasc='2015-01-01' WHERE usuario_id=1");
falha(fn()=>$service->solicitarAdocao(1,1,2),'menor rejeitado');
$db->exec("UPDATE USUARIO SET dt_nasc='1990-01-01',status_conta='bloqueado' WHERE usuario_id=1");
falha(fn()=>$service->solicitarAdocao(1,1,2),'conta bloqueada rejeitada');
$db->exec("UPDATE USUARIO SET status_conta='ativo' WHERE usuario_id=1;UPDATE PROTETOR SET validado=0 WHERE protetor_id=1");
falha(fn()=>$service->solicitarAdocao(1,1,2),'responsável não aprovado rejeitado');
$db->exec("UPDATE PROTETOR SET validado=1 WHERE protetor_id=1");
// O rollback inclui a solicitação quando o histórico falha.
$antes=(int)$db->query('SELECT COUNT(*) FROM SOLICITACAO_ADOCAO')->fetchColumn();
$db->exec("CREATE TRIGGER falhar_historico BEFORE INSERT ON HISTORICO_SOLICITACAO BEGIN SELECT RAISE(ABORT,'falha simulada'); END");
falha(fn()=>$service->solicitarAdocao(1,1,2),'falha de histórico rejeita gravação');
conferir((int)$db->query('SELECT COUNT(*) FROM SOLICITACAO_ADOCAO')->fetchColumn()===$antes,'rollback não deixa pedido parcial');
$db->exec('DROP TRIGGER falhar_historico');
$serviceSemSmtp=new app\services\SolicitacaoAdocaoService($db,fn()=>throw new Exception('SMTP indisponível'));
$smtpId=$serviceSemSmtp->solicitarAdocao(1,1,2);
conferir($smtpId>0,'falha SMTP não desfaz pedido');
$service->recusar($smtpId,1,2,'Motivo de teste');
conferir($db->query("SELECT justificativa_recusa FROM SOLICITACAO_ADOCAO WHERE solicitacao_id=$smtpId")->fetchColumn()==='Motivo de teste','recusa persiste justificativa');
$competidor=$service->solicitarAdocao(2,3,12);
$primeiro=$service->solicitarAdocao(1,1,12);
$service->colocarEmAnalise($competidor,1,2);
falha(fn()=>$service->aprovar($primeiro,1,2),'concorrente em análise impede aprovação sem decisão de negócio');
conferir($db->query('SELECT status FROM ANIMAL WHERE animal_id=12')->fetchColumn()==='disponivel','aprovação impedida não muda animal');
$service->recusar($competidor,1,2,'Análise encerrada');
$db->exec("CREATE TRIGGER falhar_animal BEFORE INSERT ON HISTORICO_STATUS_ANIMAL BEGIN SELECT RAISE(ABORT,'falha simulada'); END");
falha(fn()=>$service->aprovar($primeiro,1,2),'falha intermediária de aprovação rejeitada');
conferir($db->query('SELECT status FROM ANIMAL WHERE animal_id=12')->fetchColumn()==='disponivel','rollback reverte atualização do animal');
conferir($db->query("SELECT status_solicitacao FROM SOLICITACAO_ADOCAO WHERE solicitacao_id=$primeiro")->fetchColumn()==='pendente','rollback preserva pedido');
$db->exec('DROP TRIGGER falhar_animal');
for($i=3;(int)$db->query("SELECT COUNT(*) FROM SOLICITACAO_ADOCAO WHERE adotante_id=1")->fetchColumn()<10;$i++)$service->solicitarAdocao(1,1,$i);
falha(fn()=>$service->solicitarAdocao(1,1,10),'11º petisco rejeitado, inclusive cancelados');
// Sincronização das redes só afeta os tipos enviados pelo formulário.
$db->exec("INSERT INTO REDE VALUES(1,1,'https://wa.me/123','whatsapp'),(2,1,'https://instagram.com/old','instagram')");
(new app\repositories\RedeRepository($db))->sincronizarRedes(1,'https://instagram.com/new',null);
conferir((int)$db->query("SELECT COUNT(*) FROM REDE WHERE tipo_rede='whatsapp'")->fetchColumn()===1,'editar Instagram preserva outro contato');
echo "$total verificações passaram. SQLite não valida locks/concorrência do MySQL.\n";
