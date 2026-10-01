<?php
require __DIR__.'/../vendor/autoload.php';
$db=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE ANIMAL(animal_id INTEGER,protetor_id INTEGER,raca_id INTEGER,nome TEXT,dt_nasc TEXT,sexo TEXT,porte TEXT,status TEXT,descricao TEXT,vacinado INTEGER,castrado INTEGER,comportamento TEXT,historico_saude TEXT,criado_em TEXT,deletado_em TEXT,atualizado_em TEXT);
CREATE TABLE RACA(raca_id INTEGER,nome TEXT);
CREATE TABLE FOTO_ANIMAL(animal_id INTEGER,caminho_foto TEXT,foto_principal INTEGER);
INSERT INTO RACA VALUES(1,'SRD');
INSERT INTO ANIMAL VALUES(1,1,1,'Disponível','2020-01-01','macho','medio','disponivel',NULL,0,0,'docil',NULL,'2026-10-01',NULL,'2026-10-01'),(2,1,1,'Removido','2020-01-01','macho','medio','disponivel',NULL,0,0,'docil',NULL,'2026-10-01','2026-10-01','2026-10-01'),(3,2,1,'Outro dono','2020-01-01','macho','medio','disponivel',NULL,0,0,'docil',NULL,'2026-10-01',NULL,'2026-10-01'),(4,1,1,'Adotado','2020-01-01','macho','medio','adotado',NULL,0,0,'docil',NULL,'2026-10-01',NULL,'2026-10-01');");
$repo=new app\repositories\AnimalRepository($db);
$disponiveis=$repo->listarComFiltros('publico',1,'disponivel');
if(count($disponiveis)!==1 || $disponiveis[0]->getAnimalId()!==1)throw new Exception('Catálogo não respeitou estado/dono/remoção');
$adotados=$repo->listarComFiltros('publico',1,'adotado');
if(count($adotados)!==1 || $adotados[0]->getAnimalId()!==4)throw new Exception('Filtro do catálogo inválido');
echo "2 verificações de catálogo com Models reais passaram.\n";
