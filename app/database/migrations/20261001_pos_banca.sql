-- Revisar e aplicar manualmente, UMA VEZ, após backup; não executado nesta tarefa.
-- MySQL/MariaDB: mantém estados e dados existentes. Sem carga de exemplos.
ALTER TABLE PROTETOR ADD COLUMN motivo_recusa TEXT NULL;
ALTER TABLE PROTETOR ADD COLUMN inadimplente BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE PROTETOR ADD COLUMN motivo_inadimplencia TEXT NULL;
CREATE TABLE HISTORICO_CLASSIFICACAO_PROTETOR (
    historico_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    inadimplente BOOLEAN NOT NULL,
    motivo TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (protetor_id) REFERENCES PROTETOR(protetor_id),
    FOREIGN KEY (admin_id) REFERENCES USUARIO(usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_solicitacao_adotante_dia ON SOLICITACAO_ADOCAO (adotante_id, data_solicitacao);
CREATE INDEX idx_solicitacao_animal_estado ON SOLICITACAO_ADOCAO (animal_id, status_solicitacao);
-- Reversão estrutural (perde apenas as novas classificações/motivos; exportar antes):
-- DROP TABLE HISTORICO_CLASSIFICACAO_PROTETOR;
-- DROP INDEX idx_solicitacao_adotante_dia ON SOLICITACAO_ADOCAO;
-- DROP INDEX idx_solicitacao_animal_estado ON SOLICITACAO_ADOCAO;
-- ALTER TABLE PROTETOR DROP COLUMN motivo_recusa, DROP COLUMN inadimplente, DROP COLUMN motivo_inadimplencia;
