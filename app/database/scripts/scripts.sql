-- ================================================================
-- CãoNectados – Script completo de criação e seed de dados
-- ================================================================
-- Este script:
--   1. Remove o banco se existir
--   2. Cria o banco com charset utf8mb4
--   3. Cria todas as tabelas (ordem respeita FKs)
--   4. Insere dados fictícios para testes (inclui administrador)
-- ================================================================

DROP DATABASE IF EXISTS caonectados;
CREATE DATABASE IF NOT EXISTS caonectados
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE caonectados;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = "+00:00";

-- ================================================================
-- ESTRUTURA DAS TABELAS (baseada em scripts.sql)
-- ================================================================

CREATE TABLE IF NOT EXISTS TRACO (
    traco_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    traco VARCHAR(45) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS REGIAO (
    regiao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_regiao VARCHAR(100) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ESPECIE (
    especie_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS USUARIO (
    usuario_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    regiao_id INT UNSIGNED NULL,
    logradouro TEXT NULL,
    numero VARCHAR(20) NULL,
    telefone VARCHAR(20) NULL DEFAULT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_atual ENUM(
        'usuario','adotante','protetor','ong','administrador'
    ) NOT NULL DEFAULT 'usuario',
    perfis_ativos SET(
        'usuario','adotante','protetor','ong','administrador'
    ) NOT NULL DEFAULT 'usuario',
    status_conta ENUM(
        'pendente','ativo','bloqueado','rejeitado','inativo'
    ) NOT NULL DEFAULT 'pendente',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(150) NOT NULL UNIQUE,
    nome VARCHAR(150) NULL DEFAULT NULL,
    dt_nasc DATE NULL DEFAULT NULL,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_usuario_regiao FOREIGN KEY (regiao_id) REFERENCES REGIAO (regiao_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ADOTANTE (
    adotante_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_moradia ENUM('casa','apartamento','sitio','outro') NOT NULL,
    foto_perfil VARCHAR(255) NULL,
    descricao TEXT NULL,
    tamanho_interno_moradia ENUM('pequeno','medio','grande') NULL,
    detalhes TEXT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_adotante_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PROTETOR (
    protetor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    validado BOOLEAN NOT NULL DEFAULT FALSE,
    data_validacao TIMESTAMP NULL DEFAULT NULL,
    codigo_documento VARCHAR(20) NOT NULL,
    tipo_documento ENUM('cpf','cnpj') NOT NULL,
    nome_fantasia VARCHAR(45) NOT NULL,
    data_abertura_cnpj DATE NULL,
    comprovante_documento VARCHAR(255) NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_protetor_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS RACA (
    raca_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    especie_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_raca_especie FOREIGN KEY (especie_id) REFERENCES ESPECIE (especie_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ANIMAL (
    animal_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    raca_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    dt_nasc DATE NULL,
    sexo ENUM('macho','femea','indefinido') NOT NULL,
    porte ENUM('pequeno','medio','grande') NOT NULL,
    status ENUM('disponivel','em_analise','adotado','desativado') NOT NULL DEFAULT 'disponivel',
    descricao TEXT NULL,
    vacinado BOOLEAN NOT NULL DEFAULT FALSE,
    castrado BOOLEAN NOT NULL DEFAULT FALSE,
    comportamento ENUM('calmo','ativo','docil','arisco','indefinido') NULL,
    historico_saude TEXT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    atualizado_em TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_animal_protetor FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id) ON UPDATE CASCADE,
    CONSTRAINT fk_animal_raca FOREIGN KEY (raca_id) REFERENCES RACA (raca_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS FOTO_ANIMAL (
    foto_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_id INT UNSIGNED NOT NULL,
    caminho_foto VARCHAR(255) NOT NULL,
    foto_principal BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_foto_animal_animal FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PAGINA (
    pagina_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    descricao TEXT NULL,
    foto_fundo VARCHAR(255) NULL,
    foto_perfil VARCHAR(255) NULL,
    chave_pix VARCHAR(255) NULL,
    CONSTRAINT fk_pagina_protetor FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS REDE (
    rede_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    link_rede VARCHAR(255) NOT NULL,
    tipo_rede ENUM('instagram','facebook','whatsapp','outro') NOT NULL,
    CONSTRAINT fk_rede_protetor FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS SOLICITACAO_ADOCAO (
    solicitacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    adotante_id INT UNSIGNED NOT NULL,
    animal_id INT UNSIGNED NOT NULL,
    status_solicitacao ENUM('pendente','em_analise','aprovada','reprovada','cancelada') NOT NULL DEFAULT 'pendente',
    data_solicitacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    justificativa_recusa TEXT NULL,
    data_finalizacao TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_solicitacao_adotante FOREIGN KEY (adotante_id) REFERENCES ADOTANTE (adotante_id) ON UPDATE CASCADE,
    CONSTRAINT fk_solicitacao_animal FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS HISTORICO_SOLICITACAO (
    historico_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT UNSIGNED NOT NULL,
    usuario_responsavel_id INT UNSIGNED NOT NULL,
    status_antigo ENUM('pendente','em_analise','aprovada','reprovada','cancelada') NULL,
    status_novo ENUM('pendente','em_analise','aprovada','reprovada','cancelada') NOT NULL,
    data_alteracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACAO_ADOCAO (solicitacao_id) ON UPDATE CASCADE,
    CONSTRAINT fk_hist_usuario_resp FOREIGN KEY (usuario_responsavel_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CHAT (
    chat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT UNSIGNED NOT NULL,
    status ENUM('ativo','encerrado','arquivado') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACAO_ADOCAO (solicitacao_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS MENSAGEM (
    mensagem_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id INT UNSIGNED NOT NULL,
    remetente_id INT UNSIGNED NOT NULL,
    texto TEXT NOT NULL,
    lida BOOLEAN NOT NULL DEFAULT FALSE,
    data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensagem_chat FOREIGN KEY (chat_id) REFERENCES CHAT (chat_id) ON UPDATE CASCADE,
    CONSTRAINT fk_mensagem_usuario FOREIGN KEY (remetente_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS NOTIFICACAO (
    notificacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referencia_id INT UNSIGNED NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_notificacao ENUM(
        'solicitacao','mensagem','denuncia','contestacao','advertencia','sistema'
    ) NOT NULL,
    lida BOOLEAN NOT NULL DEFAULT FALSE,
    txt_notificacao TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacao_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS DENUNCIA (
    denuncia_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    denunciante_id INT UNSIGNED NOT NULL,
    denunciado_id INT UNSIGNED NOT NULL,
    perfil_denunciado ENUM('usuario','adotante','protetor','ong') NOT NULL,
    solicitacao_id INT UNSIGNED NULL,
    chat_id INT UNSIGNED NULL,
    motivo ENUM('maus_tratos','abandono','fraude','assedio','outro') NOT NULL,
    descricao TEXT NOT NULL,
    status_denuncia ENUM('aberta','em_analise','aprovada','reprovada','arquivada') NOT NULL DEFAULT 'aberta',
    decisao_admin ENUM('aprovar','reprovar','colocar_em_analise') NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_denuncia_denunciante FOREIGN KEY (denunciante_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE,
    CONSTRAINT fk_denuncia_denunciado FOREIGN KEY (denunciado_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE,
    CONSTRAINT fk_denuncia_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACAO_ADOCAO (solicitacao_id) ON UPDATE CASCADE,
    CONSTRAINT fk_denuncia_chat FOREIGN KEY (chat_id) REFERENCES CHAT (chat_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ADVERTENCIA (
    advertencia_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    denuncia_id INT UNSIGNED NOT NULL,
    perfil_afetado ENUM('usuario','adotante','protetor','ong') NOT NULL,
    data_fim DATE NULL,
    status ENUM('ativa','suspensa','encerrada') NOT NULL DEFAULT 'ativa',
    peso_status ENUM('leve','media','grave') NOT NULL DEFAULT 'leve',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_advertencia_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE,
    CONSTRAINT fk_advertencia_denuncia FOREIGN KEY (denuncia_id) REFERENCES DENUNCIA (denuncia_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CONTESTACAO (
    contestacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advertencia_id INT UNSIGNED NOT NULL,
    justificativa TEXT NOT NULL,
    parecer_admin TEXT NULL,
    data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contestacao_advertencia FOREIGN KEY (advertencia_id) REFERENCES ADVERTENCIA (advertencia_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ANIMAL_TRACO (
    animal_traco_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_id INT UNSIGNED NOT NULL,
    traco_id INT UNSIGNED NOT NULL,
    opcao_id VARCHAR(200) NOT NULL,
    CONSTRAINT fk_animal_traco_animal FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id) ON UPDATE CASCADE,
    CONSTRAINT fk_animal_traco_traco FOREIGN KEY (traco_id) REFERENCES TRACO (traco_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS HISTORICO_STATUS_ANIMAL (
    historico_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_id INT UNSIGNED NOT NULL,
    status_antigo ENUM('disponivel','em_analise','adotado','desativado') NULL,
    status_novo ENUM('disponivel','em_analise','adotado','desativado') NOT NULL,
    data_alteracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_animal FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CODIGO_VERIFICACAO (
    codigo_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    expira_em DATETIME NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_codigo_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS LOG_SISTEMA (
    log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    acao VARCHAR(255) NOT NULL,
    classe_afetada VARCHAR(100) NOT NULL,
    registro_id INT UNSIGNED NOT NULL,
    ip_usuario VARCHAR(45) NOT NULL,
    CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ================================================================
-- DADOS DE TESTE (seed) – ordem respeita as chaves estrangeiras
-- ================================================================

-- 1. REGIÃO
INSERT INTO REGIAO (regiao_id, nome_regiao) VALUES
(1,  'Alvorada'),
(2,  'Náutica'),
(3,  'Três Lagoas'),
(4,  'Cidade Nova'),
(5,  'Itaipu Binacional'),
(6,  'Itaipu C'),
(7,  'Pólo Universitário'),
(8,  'Porto Belo'),
(9,  'Morumbi'),
(10, 'Portal'),
(11, 'Bourbon'),
(12, 'Porto Meira'),
(13, 'Três Fronteiras'),
(14, 'Panorama'),
(15, 'São Roque'),
(16, 'América'),
(17, 'Monjolo'),
(18, 'Portes'),
(19, 'Centro'),
(20, 'Maracanã');

-- 2. ESPÉCIE
INSERT INTO ESPECIE (especie_id, nome, ativo) VALUES
(1, 'Cão', 1),
(2, 'Gato', 1);

-- 3. TRAÇO
INSERT INTO TRACO (traco_id, traco) VALUES
(1, 'Brincalhão'),
(2, 'Calmo'),
(3, 'Protetor'),
(4, 'Sociável'),
(5, 'Carente'),
(6, 'Independente'),
(7, 'Teimoso'),
(8, 'Inteligente'),
(9, 'Leal'),
(10,'Energético'),
(11,'Curioso'),
(12,'Dócil'),
(13,'Arisco'),
(14,'Tímido'),
(15,'Corajoso');

-- 4. RAÇA
INSERT INTO RACA (raca_id, especie_id, nome, ativo) VALUES
(1, 1, 'Sem Raça Definida (SRD)', 1),
(2, 1, 'Labrador Retriever', 1),
(3, 1, 'Poodle', 1),
(4, 1, 'Bulldog Francês', 1),
(5, 2, 'Sem Raça Definida (SRD)', 1),
(6, 2, 'Siamês', 1),
(7, 2, 'Persa', 1),
(8, 2, 'Maine Coon', 1);

-- 5. USUÁRIO (inclui administrador com regiao_id NULL)
SET @hash = '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi';

INSERT INTO USUARIO (usuario_id, regiao_id, logradouro, numero, telefone, senha, tipo_atual, perfis_ativos, status_conta, criado_em, email, nome, dt_nasc, deletado_em) VALUES
(1, NULL, 'Av. Brasil', '1000', '45900000000', @hash, 'administrador', 'administrador', 'ativo', '2026-08-15 10:00:00', 'caonectados2026@gmail.com', 'Admin CãoNectados', NULL, NULL),
(2, 5, 'Rua dos Testes', '200', '45911111111', @hash, 'administrador', 'administrador', 'ativo', '2026-08-16 09:30:00', 'admin2@test.com', 'Admin Auxiliar', '1990-02-28', NULL),
(3, 1, 'Rua A', '123', '45922222222', @hash, 'protetor', 'protetor', 'ativo', '2026-08-10 08:00:00', 'protetor1@test.com', 'João Silva', '1985-03-12', NULL),
(4, 2, 'Rua B', '456', '45933333333', @hash, 'protetor', 'protetor', 'ativo', '2026-08-11 08:00:00', 'protetor2@test.com', 'Maria Oliveira', '1990-07-25', NULL),
(5, 3, 'Rua C', '789', '45944444444', @hash, 'protetor', 'protetor', 'pendente', '2026-08-12 08:00:00', 'protetor3@test.com', 'Roberto Santos', '1993-12-01', NULL),
(6, 4, 'Rua D', '101', '45955555555', @hash, 'protetor', 'protetor', 'ativo', '2026-08-13 08:00:00', 'protetor4@test.com', 'Fernanda Lima', '1987-06-14', NULL),
(7, 5, 'Rua E', '202', '45966666666', @hash, 'protetor', 'protetor', 'bloqueado', '2026-08-14 08:00:00', 'protetor5@test.com', 'Bruno Fernandes', '1991-01-20', NULL),
(8, 6, 'Rua F', '303', '45977777777', @hash, 'protetor', 'protetor,adotante', 'ativo', '2026-08-15 08:00:00', 'protetor_adotante@test.com', 'Juliana Menezes', '1991-04-18', NULL),
(9, 7, 'Rua G', '404', '45988888888', @hash, 'adotante', 'adotante', 'ativo', '2026-08-10 09:00:00', 'adotante1@test.com', 'Carlos Souza', '1988-11-05', NULL),
(10,8, 'Rua H', '505', '45999999999', @hash, 'adotante', 'adotante', 'ativo', '2026-08-11 09:00:00', 'adotante2@test.com', 'Ana Pereira', '1992-09-17', NULL),
(11,9, 'Rua I', '606', '45910101010', @hash, 'adotante', 'adotante', 'ativo', '2026-08-12 09:00:00', 'adotante3@test.com', 'Camila Rocha', '1994-05-03', NULL),
(12,10,'Rua J', '707', '45911111112', @hash, 'adotante', 'adotante', 'ativo', '2026-08-13 09:00:00', 'adotante4@test.com', 'Patrícia Gomes', '1989-08-22', NULL),
(13,11,'Rua K', '808', '45912121212', @hash, 'adotante', 'adotante', 'ativo', '2026-08-14 09:00:00', 'adotante5@test.com', 'Rafael Barbosa', '1996-03-15', NULL),
(14,12,'Rua L', '909', '45913131313', @hash, 'adotante', 'adotante', 'ativo', '2026-08-15 09:00:00', 'adotante6@test.com', 'Aline Correia', '1993-10-30', NULL),
(15,13,'Rua M', '111', '45914141414', @hash, 'usuario', 'usuario', 'ativo', '2026-08-10 10:00:00', 'usuario1@test.com', 'Pedro Costa', '1995-01-22', NULL),
(16,14,'Rua N', '222', '45915151515', @hash, 'usuario', 'usuario', 'bloqueado', '2026-08-11 10:00:00', 'usuario2@test.com', 'Lucas Alves', '1989-08-30', NULL),
(17,15,'Rua O', '333', '45916161616', @hash, 'usuario', 'usuario', 'ativo', '2026-08-12 10:00:00', 'usuario3@test.com', 'Diego Martins', '1997-02-11', NULL),
(18,16,'Rua P', '444', '45917171717', @hash, 'usuario', 'usuario', 'inativo', '2026-08-13 10:00:00', 'usuario4@test.com', 'Gustavo Teixeira', '1990-12-05', NULL),
(19,17,'Rua Q', '555', '45918181818', @hash, 'usuario', 'usuario', 'rejeitado', '2026-08-14 10:00:00', 'usuario5@test.com', 'Thiago Nogueira', '1994-07-19', NULL);

-- 6. PROTETOR
INSERT INTO PROTETOR (protetor_id, usuario_id, validado, data_validacao, codigo_documento, tipo_documento, nome_fantasia, comprovante_documento, criado_em, deletado_em) VALUES
(1, 3, 1, '2026-08-12 10:00:00', '12345678901', 'cpf', 'Protetor João', 'comprovante_protetor1.jpg', '2026-08-10 08:00:00', NULL),
(2, 4, 1, '2026-08-13 10:00:00', '98765432100', 'cpf', 'Protetora Maria', 'comprovante_protetor2.jpg', '2026-08-11 08:00:00', NULL),
(3, 5, 0, NULL, '22334455667', 'cpf', 'Protetor Roberto', NULL, '2026-08-12 08:00:00', NULL),
(4, 6, 1, '2026-08-14 10:00:00', '11223344556', 'cpf', 'Protetora Fernanda', 'comprovante_protetor4.jpg', '2026-08-13 08:00:00', NULL),
(5, 7, 1, '2026-08-15 10:00:00', '33445566778', 'cnpj', 'Protetor Bruno', 'comprovante_protetor5.jpg', '2026-08-14 08:00:00', NULL),
(6, 8, 1, '2026-08-16 10:00:00', '44556677889', 'cpf', 'Protetora Juliana', 'comprovante_protetor6.jpg', '2026-08-15 08:00:00', NULL);

-- 7. ADOTANTE
INSERT INTO ADOTANTE (adotante_id, usuario_id, tipo_moradia, foto_perfil, descricao, tamanho_interno_moradia, detalhes, criado_em, deletado_em) VALUES
(1, 8, 'casa', 'adotante1_perfil.jpg', 'Protetora que também deseja adotar um companheiro.', 'grande', 'Detalhes adicionais sobre a moradia da adotante 1.', '2026-08-15 08:00:00', NULL),
(2, 9, 'apartamento', NULL, 'Moro em apartamento mas tenho experiência com pets.', 'medio', 'Detalhes adicionais sobre a moradia do adotante 2.', '2026-08-10 09:00:00', NULL),
(3, 10, 'casa', 'adotante3_perfil.jpg', 'Família grande, queremos um novo amigo.', 'grande', 'Detalhes adicionais sobre a moradia da adotante 3.', '2026-08-11 09:00:00', NULL),
(4, 11, 'sitio', NULL, 'Sítio tranquilo, ótimo para animais de porte grande.', 'grande', 'Detalhes adicionais sobre a moradia da adotante 4.', '2026-08-12 09:00:00', NULL),
(5, 12, 'apartamento', 'adotante5_perfil.jpg', 'Primeira adoção, muito animada com a ideia.', 'pequeno', 'Detalhes adicionais sobre a moradia do adotante 5.', '2026-08-13 09:00:00', NULL),
(6, 13, 'outro', NULL, 'Já tive cães e gatos, sei cuidar bem.', 'medio', 'Detalhes adicionais sobre a moradia do adotante 6.', '2026-08-14 09:00:00', NULL),
(7, 14, 'casa', 'adotante7_perfil.jpg', 'Adoro animais, tenho espaço amplo.', 'grande', 'Detalhes adicionais sobre a moradia da adotante 7.', '2026-08-15 09:00:00', NULL);

-- 8. ANIMAL
INSERT INTO ANIMAL (animal_id, protetor_id, raca_id, nome, dt_nasc, sexo, porte, status, descricao, vacinado, castrado, comportamento, historico_saude, criado_em, deletado_em, atualizado_em) VALUES
(1, 1, 3, 'Bolinha', '2024-01-15', 'macho', 'pequeno', 'em_analise', 'Poodle muito esperto.', 1, 0, 'ativo', 'Precisa de castração.', '2026-08-01 10:00:00', NULL, NULL),
(2, 2, 2, 'Luna', '2023-10-20', 'femea', 'grande', 'disponivel', 'Labradora muito dócil, adora crianças.', 1, 1, 'calmo', 'Vacinas em dia.', '2026-08-02 10:00:00', NULL, '2026-08-18 14:00:00'),
(3, 1, 1, 'Rex', '2024-05-05', 'macho', 'medio', 'disponivel', 'Cão muito brincalhão e sociável.', 1, 1, 'ativo', 'Saúde em dia.', '2026-08-03 10:00:00', NULL, NULL),
(4, 4, 6, 'Fred', '2024-02-10', 'macho', 'medio', 'disponivel', 'Gato siamês curioso.', 1, 1, 'ativo', 'Ótimo estado.', '2026-08-04 10:00:00', NULL, NULL),
(5, 5, 5, 'Mimi', '2024-03-18', 'femea', 'pequeno', 'adotado', 'Gata SRD muito carinhosa.', 1, 1, 'docil', 'Perfeita saúde.', '2026-08-05 10:00:00', NULL, NULL),
(6, 1, 3, 'Max', '2021-07-12', 'macho', 'grande', 'desativado', 'Cão idoso, muito calmo.', 1, 1, 'calmo', 'Faleceu por idade.', '2026-08-06 10:00:00', NULL, NULL),
(7, 6, 7, 'Mia', '2024-06-01', 'femea', 'pequeno', 'disponivel', 'Gata persa, muito calma.', 0, 0, 'calmo', 'Sem histórico de doenças.', '2026-08-07 10:00:00', NULL, '2026-08-20 09:00:00'),
(8, 2, 4, 'Amora', '2024-04-25', 'femea', 'medio', 'disponivel', 'Cão jovem, muito energético.', 1, 0, 'ativo', 'Castração agendada.', '2026-08-08 10:00:00', NULL, NULL);

-- 9. ANIMAL_TRACO
INSERT INTO ANIMAL_TRACO (animal_traco_id, animal_id, traco_id, opcao_id) VALUES
(1, 1, 1, 'sim'), (2, 1, 4, 'sim'), (3, 1, 8, 'sim'),
(4, 2, 2, 'sim'), (5, 2, 3, 'sim'), (6, 2, 9, 'sim'),
(7, 3, 1, 'sim'), (8, 3, 6, 'sim'), (9, 3, 10, 'sim'),
(10, 4, 4, 'sim'), (11, 4, 11, 'sim'),
(12, 5, 12, 'sim'), (13, 5, 14, 'sim'),
(14, 6, 2, 'sim'), (15, 6, 9, 'sim'),
(16, 7, 2, 'sim'), (17, 7, 12, 'sim'),
(18, 8, 1, 'sim'), (19, 8, 10, 'sim'), (20, 8, 11, 'sim');

-- 10. FOTO_ANIMAL
INSERT INTO FOTO_ANIMAL (foto_id, animal_id, caminho_foto, foto_principal) VALUES
(1, 1, 'uploads/animais/bolinha_1.jpg', 1),
(2, 1, 'uploads/animais/bolinha_2.jpg', 0),
(3, 2, 'uploads/animais/luna_1.jpg', 1),
(4, 3, 'uploads/animais/rex_1.jpg', 1),
(5, 4, 'uploads/animais/fred_1.jpg', 1),
(6, 5, 'uploads/animais/mimi_1.jpg', 1),
(7, 6, 'uploads/animais/max_1.jpg', 1),
(8, 7, 'uploads/animais/mia_1.jpg', 1),
(9, 8, 'uploads/animais/amora_1.jpg', 1),
(10, 8, 'uploads/animais/amora_2.jpg', 0);

-- 11. PÁGINA
INSERT INTO PAGINA (pagina_id, protetor_id, descricao, foto_fundo, foto_perfil, chave_pix) VALUES
(1, 1, 'Protetor João - Resgatando vidas', 'fundo_protetor1.jpg', 'perfil_protetor1.jpg', 'protetor1.pix@email.com'),
(2, 2, 'Protetora Maria - Amor em quatro patas', 'fundo_protetor2.jpg', 'perfil_protetor2.jpg', 'protetor2.pix@email.com'),
(3, 4, 'Protetora Fernanda - Sítio dos Animais', 'fundo_protetor4.jpg', 'perfil_protetor4.jpg', 'protetor4.pix@email.com'),
(4, 5, 'Protetor Bruno - Adoção responsável', 'fundo_protetor5.jpg', 'perfil_protetor5.jpg', 'protetor5.pix@email.com'),
(5, 6, 'Protetora Juliana - Segunda chance animal', 'fundo_protetor6.jpg', 'perfil_protetor6.jpg', 'protetor6.pix@email.com');

-- 12. REDE SOCIAL
INSERT INTO REDE (rede_id, protetor_id, link_rede, tipo_rede) VALUES
(1, 1, 'https://instagram.com/protetor1', 'instagram'),
(2, 1, 'https://facebook.com/protetor1', 'facebook'),
(3, 2, 'https://instagram.com/protetor2', 'instagram'),
(4, 2, 'https://whatsapp.com/protetor2', 'whatsapp'),
(5, 4, 'https://instagram.com/protetor4', 'instagram'),
(6, 5, 'https://facebook.com/protetor5', 'facebook'),
(7, 5, 'https://whatsapp.com/protetor5', 'whatsapp'),
(8, 6, 'https://instagram.com/protetor6', 'instagram');

-- 13. SOLICITAÇÃO DE ADOÇÃO
INSERT INTO SOLICITACAO_ADOCAO (solicitacao_id, adotante_id, animal_id, status_solicitacao, data_solicitacao, justificativa_recusa, data_finalizacao) VALUES
(1, 1, 2, 'aprovada', '2026-08-10 14:00:00', NULL, '2026-08-15 10:00:00'),
(2, 2, 3, 'pendente', '2026-08-11 09:00:00', NULL, NULL),
(3, 3, 4, 'em_analise', '2026-08-12 11:00:00', NULL, NULL),
(4, 4, 5, 'reprovada', '2026-08-13 10:30:00', 'Adotante não atende aos critérios de espaço.', '2026-08-16 16:00:00'),
(5, 5, 1, 'cancelada', '2026-08-14 08:00:00', NULL, '2026-08-14 12:00:00'),
(6, 6, 7, 'pendente', '2026-08-15 15:00:00', NULL, NULL),
(7, 7, 8, 'em_analise', '2026-08-16 09:00:00', NULL, NULL),
(8, 1, 4, 'aprovada', '2026-08-17 13:00:00', NULL, '2026-08-19 11:00:00');

-- 14. CHAT
INSERT INTO CHAT (chat_id, solicitacao_id, status, criado_em) VALUES
(1, 1, 'encerrado', '2026-08-10 14:30:00'),
(2, 3, 'ativo', '2026-08-12 11:30:00'),
(3, 4, 'encerrado', '2026-08-13 10:45:00'),
(4, 7, 'ativo', '2026-08-16 09:30:00'),
(5, 8, 'encerrado', '2026-08-17 13:15:00');

-- 15. MENSAGEM
INSERT INTO MENSAGEM (mensagem_id, chat_id, remetente_id, texto, lida, data_hora) VALUES
(1, 1, 3, 'Olá! Tenho muito interesse em adotar.', 1, '2026-08-10 14:35:00'),
(2, 1, 9, 'Claro! Podemos agendar uma visita?', 1, '2026-08-10 14:40:00'),
(3, 1, 3, 'Sim, amanhã à tarde está bom pra mim.', 1, '2026-08-10 14:45:00'),
(4, 2, 4, 'Gostaria de mais informações sobre o animal.', 0, '2026-08-12 11:35:00'),
(5, 2, 10, 'Ele é muito dócil e saudável.', 0, '2026-08-12 11:40:00'),
(6, 3, 5, 'Obrigado pelas informações, vou pensar.', 1, '2026-08-13 10:50:00'),
(7, 4, 8, 'Qualquer dúvida estou à disposição.', 0, '2026-08-16 09:35:00'),
(8, 4, 13, 'Perfeito, te aguardo então.', 0, '2026-08-16 09:40:00'),
(9, 5, 3, 'Olá! Já tenho os documentos.', 1, '2026-08-17 13:20:00'),
(10,5, 9, 'Ótimo, finalizamos a adoção.', 1, '2026-08-17 13:25:00');

-- 16. HISTÓRICO DE SOLICITAÇÃO
INSERT INTO HISTORICO_SOLICITACAO (historico_id, solicitacao_id, usuario_responsavel_id, status_antigo, status_novo, data_alteracao) VALUES
(1, 1, 1, 'pendente', 'em_analise', '2026-08-11 08:00:00'),
(2, 1, 1, 'em_analise', 'aprovada', '2026-08-12 10:00:00'),
(3, 2, 2, 'pendente', 'em_analise', '2026-08-12 09:00:00'),
(4, 3, 3, 'pendente', 'em_analise', '2026-08-13 09:00:00'),
(5, 4, 4, 'pendente', 'em_analise', '2026-08-14 10:00:00'),
(6, 4, 4, 'em_analise', 'reprovada', '2026-08-15 16:00:00'),
(7, 5, 5, 'pendente', 'cancelada', '2026-08-14 08:00:00'),
(8, 6, 6, 'pendente', 'em_analise', '2026-08-16 15:00:00'),
(9, 7, 1, 'pendente', 'em_analise', '2026-08-17 09:00:00'),
(10,8, 2, 'pendente', 'em_analise', '2026-08-18 13:00:00'),
(11,8, 2, 'em_analise', 'aprovada', '2026-08-19 11:00:00');

-- 17. HISTÓRICO DE STATUS DO ANIMAL
INSERT INTO HISTORICO_STATUS_ANIMAL (historico_id, animal_id, status_antigo, status_novo, data_alteracao) VALUES
(1, 1, NULL, 'disponivel', '2026-08-01 10:00:00'),
(2, 1, 'disponivel', 'em_analise', '2026-08-10 14:00:00'),
(3, 2, NULL, 'disponivel', '2026-08-02 10:00:00'),
(4, 3, NULL, 'disponivel', '2026-08-03 10:00:00'),
(5, 4, NULL, 'disponivel', '2026-08-04 10:00:00'),
(6, 5, NULL, 'disponivel', '2026-08-05 10:00:00'),
(7, 5, 'disponivel', 'adotado', '2026-08-15 10:00:00'),
(8, 6, NULL, 'desativado', '2026-08-06 10:00:00'),
(9, 7, NULL, 'disponivel', '2026-08-07 10:00:00'),
(10,8, NULL, 'disponivel', '2026-08-08 10:00:00');

-- 18. DENÚNCIA
INSERT INTO DENUNCIA (denuncia_id, denunciante_id, denunciado_id, perfil_denunciado, solicitacao_id, chat_id, motivo, descricao, status_denuncia, decisao_admin, criado_em) VALUES
(1, 9, 7, 'protetor', NULL, NULL, 'maus_tratos', 'Relato de maus-tratos a animais.', 'em_analise', 'colocar_em_analise', '2026-08-18 10:00:00'),
(2, 15, 3, 'usuario', NULL, NULL, 'assedio', 'Assédio no chat.', 'aberta', NULL, '2026-08-19 09:00:00'),
(3, 10, 5, 'ong', 4, 3, 'fraude', 'Suspeita de fraude na adoção.', 'aprovada', 'aprovar', '2026-08-17 14:00:00'),
(4, 11, 2, 'adotante', NULL, NULL, 'outro', 'Outra situação relatada.', 'reprovada', 'reprovar', '2026-08-16 11:00:00');

-- 19. ADVERTÊNCIA
INSERT INTO ADVERTENCIA (advertencia_id, usuario_id, denuncia_id, perfil_afetado, data_fim, status, peso_status, criado_em) VALUES
(1, 7, 1, 'protetor', '2026-09-18', 'ativa', 'media', '2026-08-18 11:00:00'),
(2, 5, 3, 'ong', '2026-09-20', 'suspensa', 'grave', '2026-08-17 15:00:00');

-- 20. CONTESTAÇÃO
INSERT INTO CONTESTACAO (contestacao_id, advertencia_id, justificativa, parecer_admin, data_hora) VALUES
(1, 1, 'Não concordo com a advertência, considero que houve um mal-entendido.', 'Advertência mantida após análise do comitê.', '2026-08-19 09:30:00'),
(2, 2, 'A denúncia é falsa, não houve fraude.', 'Aguardando novas evidências.', '2026-08-18 16:00:00');

-- 21. NOTIFICAÇÃO
INSERT INTO NOTIFICACAO (notificacao_id, referencia_id, usuario_id, tipo_notificacao, lida, txt_notificacao, criado_em) VALUES
(1, 1, 3, 'solicitacao', 1, 'Nova solicitação de adoção recebida.', '2026-08-10 14:00:00'),
(2, 2, 9, 'solicitacao', 0, 'Nova solicitação de adoção recebida.', '2026-08-11 09:00:00'),
(3, 1, 9, 'mensagem', 1, 'Você recebeu uma nova mensagem no chat.', '2026-08-10 14:35:00'),
(4, 4, 10, 'denuncia', 0, 'Uma denúncia foi registrada envolvendo seu perfil.', '2026-08-17 14:00:00'),
(5, 1, 7, 'advertencia', 0, 'Você recebeu uma advertência em sua conta.', '2026-08-18 11:00:00'),
(6, 1, 7, 'contestacao', 0, 'Sua contestação foi analisada pela administração.', '2026-08-19 09:30:00'),
(7, NULL, 1, 'sistema', 0, 'Atualização importante do sistema CãoNectados.', '2026-08-20 08:00:00');

-- 22. CÓDIGO DE VERIFICAÇÃO
INSERT INTO CODIGO_VERIFICACAO (codigo_id, usuario_id, codigo, expira_em, usado, criado_em) VALUES
(1, 1, '123456', '2026-09-01 23:59:59', 1, '2026-08-15 10:00:00'),
(2, 2, '654321', '2026-09-02 23:59:59', 0, '2026-08-16 09:30:00'),
(3, 3, '111111', '2026-08-30 23:59:59', 0, '2026-08-10 08:00:00'),
(4, 4, '222222', '2026-08-31 23:59:59', 0, '2026-08-11 08:00:00'),
(5, 9, '333333', '2026-09-01 23:59:59', 1, '2026-08-10 09:00:00');

-- 23. LOG DO SISTEMA
INSERT INTO LOG_SISTEMA (log_id, usuario_id, data_hora, acao, classe_afetada, registro_id, ip_usuario) VALUES
(1, 1, '2026-08-15 10:00:00', 'Cadastrou animal', 'ANIMAL', 1, '192.168.1.1'),
(2, 2, '2026-08-16 09:30:00', 'Aprovou solicitação', 'SOLICITACAO_ADOCAO', 1, '192.168.1.2'),
(3, 3, '2026-08-10 08:00:00', 'Atualizou perfil', 'USUARIO', 3, '192.168.1.3'),
(4, 4, '2026-08-13 10:00:00', 'Registrou denúncia', 'DENUNCIA', 1, '192.168.1.4'),
(5, 1, '2026-08-18 11:00:00', 'Aplicou advertência', 'ADVERTENCIA', 1, '192.168.1.5'),
(6, 2, '2026-08-19 09:30:00', 'Validou protetor', 'PROTETOR', 1, '192.168.1.6'),
(7, 5, '2026-08-14 08:00:00', 'Cancelou solicitação', 'SOLICITACAO_ADOCAO', 5, '192.168.1.7');

-- ================================================================
-- Ajuste dos AUTO_INCREMENT
-- ================================================================
ALTER TABLE REGIAO AUTO_INCREMENT = 21;
ALTER TABLE ESPECIE AUTO_INCREMENT = 3;
ALTER TABLE TRACO AUTO_INCREMENT = 16;
ALTER TABLE RACA AUTO_INCREMENT = 9;
ALTER TABLE USUARIO AUTO_INCREMENT = 20;
ALTER TABLE PROTETOR AUTO_INCREMENT = 7;
ALTER TABLE ADOTANTE AUTO_INCREMENT = 8;
ALTER TABLE ANIMAL AUTO_INCREMENT = 9;
ALTER TABLE ANIMAL_TRACO AUTO_INCREMENT = 21;
ALTER TABLE FOTO_ANIMAL AUTO_INCREMENT = 11;
ALTER TABLE PAGINA AUTO_INCREMENT = 6;
ALTER TABLE REDE AUTO_INCREMENT = 9;
ALTER TABLE SOLICITACAO_ADOCAO AUTO_INCREMENT = 9;
ALTER TABLE CHAT AUTO_INCREMENT = 6;
ALTER TABLE MENSAGEM AUTO_INCREMENT = 11;
ALTER TABLE HISTORICO_SOLICITACAO AUTO_INCREMENT = 12;
ALTER TABLE HISTORICO_STATUS_ANIMAL AUTO_INCREMENT = 11;
ALTER TABLE DENUNCIA AUTO_INCREMENT = 5;
ALTER TABLE ADVERTENCIA AUTO_INCREMENT = 3;
ALTER TABLE CONTESTACAO AUTO_INCREMENT = 3;
ALTER TABLE NOTIFICACAO AUTO_INCREMENT = 8;
ALTER TABLE CODIGO_VERIFICACAO AUTO_INCREMENT = 6;
ALTER TABLE LOG_SISTEMA AUTO_INCREMENT = 8;

-- ================================================================
-- Finalização
-- ================================================================
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
