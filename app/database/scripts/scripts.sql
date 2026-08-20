CREATE DATABASE IF NOT EXISTS caonectados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE caonectados;

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

    -- TIPO ATUAL: O perfil ativo na sessão no momento (ENUM = escolhe 1 por vez)
    tipo_atual ENUM(
        'usuario',
        'adotante',
        'protetor',
        'ong',
        'administrador'
    ) NOT NULL DEFAULT 'usuario',

    -- PERFIS ATIVOS: Permite ter MULTIPLOS perfis vinculados ao mesmo usuario (SET)
    perfis_ativos SET(
        'usuario',
        'adotante',
        'protetor',
        'ong',
        'administrador'
    ) NOT NULL DEFAULT 'usuario',

    status_conta ENUM(
        'pendente',
        'ativo',
        'bloqueado',
        'rejeitado',
        'inativo'
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
    tipo_moradia ENUM(
        'casa',
        'apartamento',
        'sitio',
        'outro'
    ) NOT NULL,
    foto_perfil VARCHAR(255) NULL,
    descricao TEXT NULL,
    tamanho_interno_moradia ENUM('pequeno', 'medio', 'grande') NULL,
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
    tipo_documento ENUM('cpf', 'cnpj') NOT NULL,
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
    sexo ENUM(
        'macho',
        'femea',
        'indefinido'
    ) NOT NULL,
    porte ENUM('pequeno', 'medio', 'grande') NOT NULL,
    status ENUM(
        'disponivel',
        'em_analise',
        'adotado',
        'desativado'
    ) NOT NULL DEFAULT 'disponivel',
    descricao TEXT NULL,
    vacinado BOOLEAN NOT NULL DEFAULT FALSE,
    castrado BOOLEAN NOT NULL DEFAULT FALSE,
    comportamento ENUM(
        'calmo',
        'ativo',
        'docil',
        'arisco',
        'indefinido'
    ) NULL,
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
    tipo_rede ENUM(
        'instagram',
        'facebook',
        'whatsapp',
        'outro'
    ) NOT NULL,
    CONSTRAINT fk_rede_protetor FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS SOLICITACAO_ADOCAO (
    solicitacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    adotante_id INT UNSIGNED NOT NULL,
    animal_id INT UNSIGNED NOT NULL,
    status_solicitacao ENUM(
        'pendente',
        'em_analise',
        'aprovada',
        'reprovada',
        'cancelada'
    ) NOT NULL DEFAULT 'pendente',
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
    status_antigo ENUM(
        'pendente',
        'em_analise',
        'aprovada',
        'reprovada',
        'cancelada'
    ) NULL,
    status_novo ENUM(
        'pendente',
        'em_analise',
        'aprovada',
        'reprovada',
        'cancelada'
    ) NOT NULL,
    data_alteracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACAO_ADOCAO (solicitacao_id) ON UPDATE CASCADE,
    CONSTRAINT fk_hist_usuario_resp FOREIGN KEY (usuario_responsavel_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CHAT (
    chat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT UNSIGNED NOT NULL,
    status ENUM(
        'ativo',
        'encerrado',
        'arquivado'
    ) NOT NULL DEFAULT 'ativo',
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
        'solicitacao',
        'mensagem',
        'denuncia',
        'contestacao',
        'advertencia',
        'sistema'
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
    perfil_denunciado ENUM(
        'usuario',
        'adotante',
        'protetor',
        'ong'
    ) NOT NULL,
    solicitacao_id INT UNSIGNED NULL,
    chat_id INT UNSIGNED NULL,
    motivo ENUM(
        'maus_tratos',
        'abandono',
        'fraude',
        'assedio',
        'outro'
    ) NOT NULL,
    descricao TEXT NOT NULL,
    status_denuncia ENUM(
        'aberta',
        'em_analise',
        'aprovada',
        'reprovada',
        'arquivada'
    ) NOT NULL DEFAULT 'aberta',
    decisao_admin ENUM(
        'aprovar',
        'reprovar',
        'colocar_em_analise'
    ) NULL,
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
    perfil_afetado ENUM(
        'usuario',
        'adotante',
        'protetor',
        'ong'
    ) NOT NULL,
    data_fim DATE NULL,
    status ENUM(
        'ativa',
        'suspensa',
        'encerrada'
    ) NOT NULL DEFAULT 'ativa',
    peso_status ENUM('leve', 'media', 'grave') NOT NULL DEFAULT 'leve',
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
    status_antigo ENUM('disponivel', 'em_analise', 'adotado', 'desativado') NULL,
    status_novo ENUM('disponivel', 'em_analise', 'adotado', 'desativado') NOT NULL,
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

-- ===========================================
-- USUÁRIO ADMINISTRADOR COM MULTIPERFIS
-- ===========================================
INSERT INTO USUARIO (
    usuario_id,
    regiao_id,
    logradouro,
    numero,
    telefone,
    senha,
    tipo_atual,
    perfis_ativos,
    status_conta,
    email,
    nome
) VALUES (
    1,
    NULL, -- CORRIGIDO: Passando NULL para evitar erro de chave estrangeira
    'Av. Brasil',
    '1000',
    '45900000000',
    '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi',
    'administrador',
    'adotante,protetor,ong,administrador',
    'ativo',
    'caonectados2026@gmail.com',
    'Admin CãoNectados'
);

-- PERFIL ADOTANTE PARA O ADMIN (Monitoramento e Testes)
INSERT INTO ADOTANTE (
    adotante_id,
    usuario_id,
    tipo_moradia,
    tamanho_interno_moradia,
    descricao,
    detalhes
) VALUES (
    1,
    1,
    'casa',
    'grande',
    'Perfil Adotante do Administrador para Testes e Monitoramento',
    '{"possui_criancas":"nao","possui_outros_pets":"sim","espaco_externo":"grande"}'
);

-- PERFIL PROTETOR / ONG PARA O ADMIN (Monitoramento e Testes)
INSERT INTO PROTETOR (
    protetor_id,
    usuario_id,
    validado,
    codigo_documento,
    tipo_documento,
    nome_fantasia,
    data_validacao
) VALUES (
    1,
    1,
    TRUE,
    '00000000000191',
    'cnpj',
    'ONG Administrativa CãoNectados',
    CURRENT_TIMESTAMP
);

-- PÁGINA INSTITUCIONAL DA ONG/PROTETOR ADMIN
INSERT INTO PAGINA (
    pagina_id,
    protetor_id,
    descricao,
    chave_pix
) VALUES (
    1,
    1,
    'Página de testes e monitoramento institucional da ONG do Admin',
    'admin@caonectados.com.br'
);

INSERT INTO
    REGIAO (nome_regiao)
VALUES ('Alvorada'),
    ('Náutica'),
    ('Três Lagoas'),
    ('Cidade Nova'),
    ('Itaipu Binacional'),
    ('Itaipu C'),
    ('Pólo Universitário'),
    ('Porto Belo'),
    ('Morumbi'),
    ('Portal'),
    ('Bourbon'),
    ('Porto Meira'),
    ('Três Fronteiras'),
    ('Panorama'),
    ('São Roque'),
    ('América'),
    ('Monjolo'),
    ('Portes'),
    ('Lancaster'),
    ('Três Bandeiras'),
    ('Itaipu A'),
    ('Itaipu B'),
    ('KLP'),
    ('IPÊ'),
    ('Centro'),
    ('Maracanã'),
    ('Yolanda'),
    ('Polo Centro'),
    ('Centro Cívico'),
    ('Campos do Iguaçu'),
    ('Carimã'),
    ('Mata Verde'),
    ('Cataratas'),
    ('Cognópolis'),
    ('Lote Grande'),
    ('Remanso'),
    ('Parque Nacional');

INSERT INTO ESPECIE (nome) VALUES ('Cão'), ('Gato');

INSERT INTO
    RACA (nome, especie_id)
VALUES ('Sem Raça Definida (SRD)', 1),
    ('Labrador Retriever', 1),
    ('Poodle', 1),
    ('Sem Raça Definida (SRD)', 2),
    ('Siamês', 2),
    ('Persa', 2);

-- =====================================================
-- CaoNectados - Dummy Data Seed 
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Limpa dados existentes (mantendo a estrutura das tabelas)
TRUNCATE TABLE `CONTESTACAO`;
TRUNCATE TABLE `ADVERTENCIA`;
TRUNCATE TABLE `DENUNCIA`;
TRUNCATE TABLE `NOTIFICACAO`;
TRUNCATE TABLE `LOG_SISTEMA`;
TRUNCATE TABLE `CODIGO_VERIFICACAO`;
TRUNCATE TABLE `MENSAGEM`;
TRUNCATE TABLE `CHAT`;
TRUNCATE TABLE `HISTORICO_SOLICITACAO`;
TRUNCATE TABLE `HISTORICO_STATUS_ANIMAL`;
TRUNCATE TABLE `SOLICITACAO_ADOCAO`;
TRUNCATE TABLE `REDE`;
TRUNCATE TABLE `PAGINA`;
TRUNCATE TABLE `FOTO_ANIMAL`;
TRUNCATE TABLE `ANIMAL_TRACO`;
TRUNCATE TABLE `ANIMAL`;
TRUNCATE TABLE `ADOTANTE`;
TRUNCATE TABLE `PROTETOR`;
TRUNCATE TABLE `USUARIO`;
TRUNCATE TABLE `RACA`;
TRUNCATE TABLE `TRACO`;
TRUNCATE TABLE `ESPECIE`;
TRUNCATE TABLE `REGIAO`;

-- --------------------------------------------------------
-- Dados para a tabela `REGIAO`
-- --------------------------------------------------------
INSERT INTO `REGIAO` (`regiao_id`, `nome_regiao`) VALUES
(1, 'Alvorada'),
(2, 'Náutica'),
(3, 'Três Lagoas'),
(4, 'Cidade Nova'),
(5, 'Itaipu Binacional'),
(6, 'Itaipu C'),
(7, 'Pólo Universitário'),
(8, 'Porto Belo'),
(9, 'Morumbi'),
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

-- --------------------------------------------------------
-- Dados para a tabela `ESPECIE`
-- --------------------------------------------------------
INSERT INTO `ESPECIE` (`especie_id`, `nome`, `ativo`) VALUES
(1, 'Cão', 1),
(2, 'Gato', 1);

-- --------------------------------------------------------
-- Dados para a tabela `TRACO`
-- --------------------------------------------------------
INSERT INTO `TRACO` (`traco_id`, `traco`) VALUES
(1, 'Brincalhão'),
(2, 'Calmo'),
(3, 'Protetor'),
(4, 'Sociável'),
(5, 'Carente'),
(6, 'Independente'),
(7, 'Teimoso'),
(8, 'Inteligente'),
(9, 'Leal'),
(10, 'Energético'),
(11, 'Curioso'),
(12, 'Dócil'),
(13, 'Arisco'),
(14, 'Tímido'),
(15, 'Corajoso');

-- --------------------------------------------------------
-- Dados para a tabela `RACA`
-- --------------------------------------------------------
INSERT INTO `RACA` (`raca_id`, `especie_id`, `nome`, `ativo`) VALUES
(1, 1, 'Sem Raça Definida (SRD)', 1),
(2, 1, 'Labrador Retriever', 1),
(3, 1, 'Poodle', 1),
(4, 1, 'Bulldog Francês', 1),
(5, 2, 'Sem Raça Definida (SRD)', 1),
(6, 2, 'Siamês', 1),
(7, 2, 'Persa', 1),
(8, 2, 'Maine Coon', 1);

-- --------------------------------------------------------
-- Dados para a tabela `USUARIO`
-- --------------------------------------------------------
INSERT INTO `USUARIO` (`usuario_id`, `regiao_id`, `logradouro`, `numero`, `telefone`, `senha`, `tipo_atual`, `perfis_ativos`, `status_conta`, `criado_em`, `email`, `nome`, `dt_nasc`, `deletado_em`) VALUES
(1, NULL, 'Av. Brasil', '1000', '45900000000', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'administrador', 'administrador', 'ativo', '2026-02-21 12:00:00', 'caonectados2026@gmail.com', 'Admin CãoNectados', NULL, NULL),
(2, 5, 'Rua das Flores, 102', '102', '45991000002', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'administrador', 'administrador', 'ativo', '2026-03-03 12:00:00', 'admin2@gmail.com', 'Admin Auxiliar', '1990-02-28', NULL),
(3, 1, NULL, NULL, '45991000003', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'protetor', 'ativo', '2026-03-13 12:00:00', 'protetor1@gmail.com', 'João Silva', '1985-03-12', NULL),
(4, 2, 'Rua das Flores, 104', '104', '45991000004', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'protetor', 'ativo', '2026-03-18 12:00:00', 'protetor2@gmail.com', 'Maria Oliveira', '1990-07-25', NULL),
(5, 3, NULL, NULL, '45991000005', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'protetor', 'pendente', '2026-03-23 12:00:00', 'protetor3@gmail.com', 'Roberto Santos', '1993-12-01', NULL),
(6, 4, 'Rua das Flores, 106', '106', '45991000006', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'protetor', 'ativo', '2026-03-28 12:00:00', 'protetor4@gmail.com', 'Fernanda Lima', '1987-06-14', NULL),
(7, 5, NULL, NULL, '45991000007', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'protetor', 'ativo', '2026-04-02 12:00:00', 'protetor5@gmail.com', 'Bruno Fernandes', '1991-01-20', NULL),
(8, 6, 'Rua das Flores, 108', '108', '45991000008', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'protetor', 'bloqueado', '2026-04-07 12:00:00', 'protetor6@gmail.com', 'Larissa Cardoso', '1986-09-09', NULL),
(9, 10, NULL, NULL, '45991000009', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'protetor', 'adotante,protetor', 'ativo', '2026-04-12 12:00:00', 'protetor_adotante@gmail.com', 'Juliana Menezes', '1991-04-18', NULL),
(10, 4, 'Rua das Flores, 110', '110', '45991000010', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'adotante', 'adotante', 'ativo', '2026-04-17 12:00:00', 'adotante1@gmail.com', 'Carlos Souza', '1988-11-05', NULL),
(11, 5, 'Rua das Flores, 111', '111', '45991000011', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'adotante', 'adotante', 'ativo', '2026-04-21 12:00:00', 'adotante2@gmail.com', 'Ana Pereira', '1992-09-17', NULL),
(12, 6, NULL, NULL, '45991000012', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'adotante', 'adotante', 'ativo', '2026-04-25 12:00:00', 'adotante3@gmail.com', 'Camila Rocha', '1994-05-03', NULL),
(13, 7, 'Rua das Flores, 113', '113', '45991000013', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'adotante', 'adotante', 'ativo', '2026-04-29 12:00:00', 'adotante4@gmail.com', 'Patrícia Gomes', '1989-08-22', NULL),
(14, 8, 'Rua das Flores, 114', '114', '45991000014', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'adotante', 'adotante', 'ativo', '2026-05-03 12:00:00', 'adotante5@gmail.com', 'Rafael Barbosa', '1996-03-15', NULL),
(15, 9, NULL, NULL, '45991000015', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'adotante', 'adotante', 'ativo', '2026-05-07 12:00:00', 'adotante6@gmail.com', 'Aline Correia', '1993-10-30', NULL),
(16, 8, 'Rua das Flores, 116', '116', '45991000016', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'usuario', 'usuario', 'ativo', '2026-05-11 12:00:00', 'usuario1@gmail.com', 'Pedro Costa', '1995-01-22', NULL),
(17, 9, 'Rua das Flores, 117', '117', '45991000017', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'usuario', 'usuario', 'bloqueado', '2026-05-14 12:00:00', 'usuario2@gmail.com', 'Lucas Alves', '1989-08-30', NULL),
(18, 10, NULL, NULL, '45991000018', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'usuario', 'usuario', 'inativo', '2026-05-17 12:00:00', 'usuario3@gmail.com', 'Diego Martins', '1997-02-11', NULL),
(19, 11, 'Rua das Flores, 119', '119', '45991000019', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'usuario', 'usuario', 'rejeitado', '2026-05-20 12:00:00', 'usuario4@gmail.com', 'Gustavo Teixeira', '1990-12-05', NULL),
(20, 12, 'Rua das Flores, 120', '120', '45991000020', '$2y$10$rMVohZcvkqsHoZoXCnaMm.BU77eBGYGIFxtDMS6PX7J/r22RVGhZi', 'usuario', 'usuario', 'ativo', '2026-05-23 12:00:00', 'usuario5@gmail.com', 'Thiago Nogueira', '1994-07-19', NULL);

-- --------------------------------------------------------
-- Dados para a tabela `PROTETOR`
-- --------------------------------------------------------
INSERT INTO `PROTETOR` (`protetor_id`, `usuario_id`, `validado`, `data_validacao`, `codigo_documento`, `tipo_documento`, `nome_fantasia`, `comprovante_documento`, `criado_em`, `deletado_em`) VALUES
(1, 3, 1, '2026-03-18 12:00:00', '12345678901', 'cpf', 'Protetor João', 'comprovante_protetor1.jpg', '2026-03-13 12:00:00', NULL),
(2, 4, 1, '2026-03-23 12:00:00', '98765432100', 'cpf', 'Protetora Maria', 'comprovante_protetor2.jpg', '2026-03-18 12:00:00', NULL),
(3, 5, 0, NULL, '22334455667', 'cpf', 'Protetor Roberto', NULL, '2026-03-23 12:00:00', NULL),
(4, 6, 1, '2026-03-28 12:00:00', '11223344556', 'cpf', 'Protetora Fernanda', 'comprovante_protetor4.jpg', '2026-03-28 12:00:00', NULL),
(5, 7, 1, '2026-04-02 12:00:00', '33445566778', 'cnpj', 'Protetor Bruno', 'comprovante_protetor5.jpg', '2026-04-02 12:00:00', NULL),
(6, 9, 1, '2026-04-12 12:00:00', '44556677889', 'cpf', 'Protetora Juliana', 'comprovante_protetor6.jpg', '2026-04-12 12:00:00', NULL);

-- --------------------------------------------------------
-- Dados para a tabela `ADOTANTE`
-- --------------------------------------------------------
INSERT INTO `ADOTANTE` (`adotante_id`, `usuario_id`, `tipo_moradia`, `foto_perfil`, `descricao`, `tamanho_interno_moradia`, `detalhes`, `criado_em`, `deletado_em`) VALUES
(1, 10, 'casa', 'adotante1_perfil.jpg', 'Adoro animais, tenho espaço amplo.', 'pequeno', 'Detalhes adicionais sobre a moradia do adotante 1.', '2026-04-17 12:00:00', NULL),
(2, 11, 'apartamento', NULL, 'Moro em apartamento mas tenho experiência com pets.', 'medio', 'Detalhes adicionais sobre a moradia do adotante 2.', '2026-04-20 12:00:00', NULL),
(3, 12, 'sitio', 'adotante3_perfil.jpg', 'Família grande, queremos um novo amigo.', 'grande', 'Detalhes adicionais sobre a moradia do adotante 3.', '2026-04-23 12:00:00', NULL),
(4, 13, 'outro', NULL, 'Já tive cães e gatos, sei cuidar bem.', 'pequeno', 'Detalhes adicionais sobre a moradia do adotante 4.', '2026-04-26 12:00:00', NULL),
(5, 14, 'casa', 'adotante5_perfil.jpg', 'Sítio tranquilo, ótimo para animais de porte grande.', 'medio', 'Detalhes adicionais sobre a moradia do adotante 5.', '2026-04-29 12:00:00', NULL),
(6, 15, 'apartamento', NULL, 'Primeira adoção, muito animada com a ideia.', 'grande', 'Detalhes adicionais sobre a moradia do adotante 6.', '2026-05-02 12:00:00', NULL),
(7, 9, 'sitio', 'adotante7_perfil.jpg', 'Protetora que também deseja adotar um companheiro.', 'pequeno', 'Detalhes adicionais sobre a moradia do adotante 7.', '2026-05-05 12:00:00', NULL);

-- --------------------------------------------------------
-- Dados para a tabela `ANIMAL`
-- --------------------------------------------------------
INSERT INTO `ANIMAL` (`animal_id`, `protetor_id`, `raca_id`, `nome`, `dt_nasc`, `sexo`, `porte`, `status`, `descricao`, `vacinado`, `castrado`, `comportamento`, `historico_saude`, `criado_em`, `deletado_em`, `atualizado_em`) VALUES
(1, 3, 1, 'Bolinha', '2020-01-15', 'macho', 'pequeno', 'em_analise', 'Poodle muito esperto.', 1, 0, 'ativo', 'Precisa de castração.', '2026-04-25 12:00:00', NULL, '2026-06-21 12:00:00'),
(2, 2, 1, 'Luna', '2019-08-22', 'femea', 'grande', 'disponivel', 'Labradora muito dócil, adora crianças.', 1, 1, 'calmo', 'Vacinas em dia.', '2026-04-20 12:00:00', NULL, NULL),
(3, 1, 1, 'Rex', '2022-03-10', 'macho', 'medio', 'disponivel', 'Cão muito brincalhão e sociável.', 1, 1, 'ativo', 'Saúde em dia.', '2026-04-15 12:00:00', NULL, '2026-06-16 12:00:00'),
(4, 6, 2, 'Fred', '2021-11-05', 'macho', 'medio', 'disponivel', 'Gato siamês curioso.', 1, 1, 'ativo', 'Ótimo estado.', '2026-04-10 12:00:00', NULL, NULL),
(5, 5, 2, 'Mimi', '2023-06-20', 'femea', 'pequeno', 'adotado', 'Gata SRD muito carinhosa.', 1, 1, 'docil', 'Perfeita saúde.', '2026-04-05 12:00:00', NULL, '2026-06-06 12:00:00'),
(6, 1, 3, 'Max', '2015-09-12', 'macho', 'grande', 'desativado', 'Cão idoso, muito calmo.', 1, 1, 'calmo', 'Faleceu por idade.', '2026-03-31 12:00:00', NULL, NULL),
(7, 7, 3, 'Mia', '2022-02-28', 'femea', 'pequeno', 'disponivel', 'Gata persa, muito calma.', 0, 0, 'calmo', 'Sem histórico de doenças.', '2026-03-26 12:00:00', NULL, '2026-05-27 12:00:00'),
(8, 2, 4, 'Amora', '2023-12-01', 'femea', 'medio', 'disponivel', 'Cão jovem, muito energético.', 1, 0, 'ativo', 'Castração agendada.', '2026-03-21 12:00:00', NULL, NULL),
(9, 4, 4, 'Toby', '2020-07-18', 'macho', 'pequeno', 'disponivel', 'Bulldog francês brincalhão.', 1, 1, 'docil', 'Saúde em dia.', '2026-03-16 12:00:00', NULL, '2026-05-17 12:00:00'),
(10, 1, 5, 'Nina', '2018-04-25', 'femea', 'grande', 'disponivel', 'Cão SRD dócil e protetor.', 1, 1, 'docil', 'Vacinas completas.', '2026-03-11 12:00:00', NULL, NULL),
(11, 8, 5, 'Simba', '2021-10-30', 'macho', 'grande', 'disponivel', 'Maine Coon imponente e sociável.', 1, 0, 'ativo', 'Sem problemas de saúde.', '2026-03-06 12:00:00', NULL, '2026-05-07 12:00:00'),
(12, 2, 6, 'Bella', '2019-06-14', 'femea', 'medio', 'em_analise', 'Labradora companheira e leal.', 1, 1, 'calmo', 'Aguardando exames.', '2026-03-01 12:00:00', NULL, NULL),
(13, 1, 1, 'Thor', '2017-11-08', 'macho', 'grande', 'disponivel', 'Cão forte, ótimo para sítios.', 1, 1, 'ativo', 'Excelente condicionamento.', '2026-02-24 12:00:00', NULL, '2026-04-27 12:00:00'),
(14, 6, 2, 'Nala', '2022-08-15', 'femea', 'pequeno', 'disponivel', 'Gata siamesa muito falante.', 1, 1, 'ativo', 'Saudável.', '2026-02-19 12:00:00', NULL, NULL),
(15, 3, 3, 'Zeus', '2013-03-03', 'macho', 'medio', 'desativado', 'Poodle idoso, aposentado.', 1, 1, 'calmo', 'Problemas articulares.', '2026-02-14 12:00:00', NULL, '2026-04-17 12:00:00'),
(16, 5, 4, 'Lola', '2021-12-25', 'femea', 'pequeno', 'disponivel', 'Gata SRD tímida no início.', 1, 1, 'arisco', 'Em observação.', '2026-02-09 12:00:00', NULL, NULL),
(17, 1, 5, 'Bidu', '2020-05-19', 'macho', 'medio', 'disponivel', 'Vira-lata muito leal.', 1, 0, 'docil', 'Vermifugado recentemente.', '2026-02-04 12:00:00', NULL, '2026-04-07 12:00:00'),
(18, 2, 6, 'Duda', '2019-09-09', 'femea', 'grande', 'disponivel', 'Labradora enérgica, gosta de correr.', 1, 1, 'ativo', 'Ótima forma física.', '2026-01-30 12:00:00', NULL, NULL),
(19, 1, 1, 'Preto', '2016-07-07', 'macho', 'medio', 'adotado', 'Cão SRD resgatado das ruas.', 1, 1, 'docil', 'Recuperado totalmente.', '2026-01-25 12:00:00', NULL, '2026-03-28 12:00:00'),
(20, 7, 2, 'Branca', '2023-02-14', 'femea', 'pequeno', 'disponivel', 'Gata persa elegante.', 0, 1, 'calmo', 'Aguardando vacinação.', '2026-01-20 12:00:00', NULL, NULL);

-- --------------------------------------------------------
-- Dados para a tabela `ANIMAL_TRACO`
-- --------------------------------------------------------
INSERT INTO `ANIMAL_TRACO` (`animal_traco_id`, `animal_id`, `traco_id`, `opcao_id`) VALUES
(1, 1, 2, 'sim'),
(2, 1, 5, 'sim'),
(3, 1, 8, 'sim'),
(4, 2, 1, 'sim'),
(5, 2, 4, 'sim'),
(6, 2, 9, 'sim'),
(7, 2, 12, 'sim'),
(8, 3, 3, 'sim'),
(9, 3, 6, 'sim'),
(10, 3, 10, 'sim'),
(11, 4, 2, 'sim'),
(12, 4, 11, 'sim'),
(13, 4, 14, 'sim'),
(14, 5, 4, 'sim'),
(15, 5, 9, 'sim'),
(16, 5, 12, 'sim'),
(17, 6, 2, 'sim'),
(18, 6, 5, 'sim'),
(19, 6, 9, 'sim'),
(20, 7, 2, 'sim'),
(21, 7, 5, 'sim'),
(22, 7, 8, 'sim'),
(23, 8, 1, 'sim'),
(24, 8, 6, 'sim'),
(25, 8, 10, 'sim'),
(26, 9, 4, 'sim'),
(27, 9, 9, 'sim'),
(28, 9, 12, 'sim'),
(29, 10, 2, 'sim'),
(30, 10, 5, 'sim'),
(31, 10, 9, 'sim'),
(32, 11, 1, 'sim'),
(33, 11, 6, 'sim'),
(34, 11, 10, 'sim'),
(35, 12, 2, 'sim'),
(36, 12, 5, 'sim'),
(37, 12, 8, 'sim'),
(38, 13, 3, 'sim'),
(39, 13, 6, 'sim'),
(40, 13, 9, 'sim'),
(41, 14, 2, 'sim'),
(42, 14, 11, 'sim'),
(43, 14, 14, 'sim'),
(44, 15, 2, 'sim'),
(45, 15, 5, 'sim'),
(46, 15, 8, 'sim'),
(47, 16, 4, 'sim'),
(48, 16, 9, 'sim'),
(49, 16, 12, 'sim'),
(50, 17, 2, 'sim'),
(51, 17, 5, 'sim'),
(52, 17, 9, 'sim'),
(53, 18, 1, 'sim'),
(54, 18, 6, 'sim'),
(55, 18, 10, 'sim'),
(56, 19, 4, 'sim'),
(57, 19, 9, 'sim'),
(58, 19, 12, 'sim'),
(59, 20, 2, 'sim'),
(60, 20, 5, 'sim'),
(61, 20, 8, 'sim');

-- --------------------------------------------------------
-- Dados para a tabela `FOTO_ANIMAL`
-- --------------------------------------------------------
INSERT INTO `FOTO_ANIMAL` (`foto_id`, `animal_id`, `caminho_foto`, `foto_principal`) VALUES
(1, 1, 'uploads/animais/bolinha_1.jpg', 1),
(2, 1, 'uploads/animais/bolinha_2.jpg', 0),
(3, 2, 'uploads/animais/luna_1.jpg', 1),
(4, 2, 'uploads/animais/luna_2.jpg', 0),
(5, 3, 'uploads/animais/rex_1.jpg', 1),
(6, 4, 'uploads/animais/fred_1.jpg', 1),
(7, 5, 'uploads/animais/mimi_1.jpg', 1),
(8, 6, 'uploads/animais/max_1.jpg', 1),
(9, 7, 'uploads/animais/mia_1.jpg', 1),
(10, 7, 'uploads/animais/mia_2.jpg', 0),
(11, 8, 'uploads/animais/amora_1.jpg', 1),
(12, 9, 'uploads/animais/toby_1.jpg', 1),
(13, 10, 'uploads/animais/nina_1.jpg', 1),
(14, 11, 'uploads/animais/simba_1.jpg', 1),
(15, 12, 'uploads/animais/bella_1.jpg', 1),
(16, 13, 'uploads/animais/thor_1.jpg', 1),
(17, 14, 'uploads/animais/nala_1.jpg', 1),
(18, 15, 'uploads/animais/zeus_1.jpg', 1),
(19, 16, 'uploads/animais/lola_1.jpg', 1),
(20, 17, 'uploads/animais/bidu_1.jpg', 1),
(21, 18, 'uploads/animais/duda_1.jpg', 1),
(22, 19, 'uploads/animais/preto_1.jpg', 1),
(23, 20, 'uploads/animais/branca_1.jpg', 1);

-- --------------------------------------------------------
-- Dados para a tabela `PAGINA`
-- --------------------------------------------------------
INSERT INTO `PAGINA` (`pagina_id`, `protetor_id`, `descricao`, `foto_fundo`, `foto_perfil`, `chave_pix`) VALUES
(1, 1, 'Protetor João - Resgatando vidas', 'fundo_protetor1.jpg', 'perfil_protetor1.jpg', 'protetor1.pix@email.com'),
(2, 2, 'Protetora Maria - Amor em quatro patas', 'fundo_protetor2.jpg', 'perfil_protetor2.jpg', 'protetor2.pix@email.com'),
(3, 3, 'Protetor Roberto - Ainda em validação', NULL, NULL, NULL),
(4, 4, 'Protetora Fernanda - Sítio dos Animais', 'fundo_protetor4.jpg', 'perfil_protetor4.jpg', 'protetor4.pix@email.com'),
(5, 5, 'Protetor Bruno - Adoção responsável', 'fundo_protetor5.jpg', 'perfil_protetor5.jpg', 'protetor5.pix@email.com'),
(6, 6, 'Protetora Juliana - Segunda chance animal', 'fundo_protetor6.jpg', 'perfil_protetor6.jpg', 'protetor6.pix@email.com');

-- --------------------------------------------------------
-- Dados para a tabela `REDE`
-- --------------------------------------------------------
INSERT INTO `REDE` (`rede_id`, `protetor_id`, `link_rede`, `tipo_rede`) VALUES
(1, 1, 'https://instagram.com/protetor1', 'instagram'),
(2, 1, 'https://facebook.com/protetor1', 'facebook'),
(3, 2, 'https://instagram.com/protetor2', 'instagram'),
(4, 2, 'https://whatsapp.com/protetor2', 'whatsapp'),
(5, 3, 'https://instagram.com/protetor3', 'instagram'),
(6, 3, 'https://facebook.com/protetor3', 'facebook'),
(7, 4, 'https://instagram.com/protetor4', 'instagram'),
(8, 4, 'https://whatsapp.com/protetor4', 'whatsapp'),
(9, 5, 'https://instagram.com/protetor5', 'instagram'),
(10, 5, 'https://facebook.com/protetor5', 'facebook'),
(11, 6, 'https://instagram.com/protetor6', 'instagram'),
(12, 6, 'https://whatsapp.com/protetor6', 'whatsapp');

-- --------------------------------------------------------
-- Dados para a tabela `SOLICITACAO_ADOCAO`
-- --------------------------------------------------------
INSERT INTO `SOLICITACAO_ADOCAO` (`solicitacao_id`, `adotante_id`, `animal_id`, `status_solicitacao`, `data_solicitacao`, `justificativa_recusa`, `data_finalizacao`) VALUES
(1, 7, 3, 'pendente', '2026-08-19 12:00:00', NULL, NULL),
(2, 6, 10, 'em_analise', '2026-08-18 12:00:00', NULL, NULL),
(3, 5, 2, 'aprovada', '2026-08-17 12:00:00', NULL, '2026-08-15 12:00:00'),
(4, 4, 8, 'reprovada', '2026-08-16 12:00:00', 'Adotante não atende aos critérios necessários de espaço/tempo.', '2026-08-14 12:00:00'),
(5, 3, 4, 'cancelada', '2026-08-15 12:00:00', NULL, '2026-08-13 12:00:00'),
(6, 2, 5, 'aprovada', '2026-08-14 12:00:00', NULL, '2026-08-12 12:00:00'),
(7, 1, 1, 'em_analise', '2026-08-13 12:00:00', NULL, NULL),
(8, 7, 9, 'pendente', '2026-08-12 12:00:00', NULL, NULL),
(9, 6, 12, 'reprovada', '2026-08-11 12:00:00', 'Adotante não atende aos critérios necessários de espaço/tempo.', '2026-08-09 12:00:00'),
(10, 5, 14, 'em_analise', '2026-08-10 12:00:00', NULL, NULL),
(11, 4, 16, 'aprovada', '2026-08-09 12:00:00', NULL, '2026-08-07 12:00:00'),
(12, 3, 18, 'cancelada', '2026-08-08 12:00:00', NULL, '2026-08-06 12:00:00'),
(13, 2, 20, 'pendente', '2026-08-07 12:00:00', NULL, NULL),
(14, 1, 6, 'em_analise', '2026-08-06 12:00:00', NULL, NULL),
(15, 7, 7, 'reprovada', '2026-08-05 12:00:00', 'Adotante não atende aos critérios necessários de espaço/tempo.', '2026-08-03 12:00:00'),
(16, 6, 11, 'aprovada', '2026-08-04 12:00:00', NULL, '2026-08-02 12:00:00'),
(17, 5, 13, 'pendente', '2026-08-03 12:00:00', NULL, NULL),
(18, 4, 15, 'em_analise', '2026-08-02 12:00:00', NULL, NULL),
(19, 3, 17, 'aprovada', '2026-08-01 12:00:00', NULL, '2026-07-30 12:00:00'),
(20, 2, 19, 'reprovada', '2026-07-31 12:00:00', 'Adotante não atende aos critérios necessários de espaço/tempo.', '2026-07-29 12:00:00'),
(21, 1, 2, 'pendente', '2026-07-30 12:00:00', NULL, NULL),
(22, 7, 4, 'cancelada', '2026-07-29 12:00:00', NULL, '2026-07-27 12:00:00'),
(23, 6, 8, 'aprovada', '2026-07-28 12:00:00', NULL, '2026-07-26 12:00:00'),
(24, 5, 10, 'pendente', '2026-07-27 12:00:00', NULL, NULL);

-- --------------------------------------------------------
-- Dados para a tabela `CHAT`
-- --------------------------------------------------------
INSERT INTO `CHAT` (`chat_id`, `solicitacao_id`, `status`, `criado_em`) VALUES
(1, 2, 'ativo', '2026-08-18 12:00:00'),
(2, 3, 'encerrado', '2026-08-17 12:00:00'),
(3, 4, 'encerrado', '2026-08-16 12:00:00'),
(4, 6, 'encerrado', '2026-08-14 12:00:00'),
(5, 7, 'ativo', '2026-08-13 12:00:00'),
(6, 9, 'encerrado', '2026-08-11 12:00:00'),
(7, 10, 'ativo', '2026-08-10 12:00:00'),
(8, 11, 'encerrado', '2026-08-09 12:00:00'),
(9, 14, 'ativo', '2026-08-06 12:00:00'),
(10, 15, 'encerrado', '2026-08-05 12:00:00'),
(11, 16, 'encerrado', '2026-08-04 12:00:00'),
(12, 18, 'ativo', '2026-08-02 12:00:00'),
(13, 19, 'encerrado', '2026-08-01 12:00:00'),
(14, 20, 'encerrado', '2026-07-31 12:00:00'),
(15, 23, 'encerrado', '2026-07-28 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `MENSAGEM`
-- --------------------------------------------------------
INSERT INTO `MENSAGEM` (`mensagem_id`, `chat_id`, `remetente_id`, `texto`, `lida`, `data_hora`) VALUES
(1, 1, 3, 'Olá! Tenho muito interesse em adotar.', 1, '2026-08-17 12:00:00'),
(2, 1, 10, 'Claro! Podemos agendar uma visita?', 0, '2026-08-16 12:00:00'),
(3, 1, 3, 'Sim, amanhã à tarde está bom pra mim.', 1, '2026-08-15 12:00:00'),
(4, 2, 4, 'Gostaria de mais informações sobre o animal.', 1, '2026-08-16 12:00:00'),
(5, 2, 11, 'Ele é muito dócil e saudável, sem problemas.', 1, '2026-08-15 12:00:00'),
(6, 2, 4, 'Obrigado pelas informações, vou pensar.', 1, '2026-08-14 12:00:00'),
(7, 3, 5, 'Olá! Tenho muito interesse em adotar.', 0, '2026-08-15 12:00:00'),
(8, 3, 12, 'Qualquer dúvida estou à disposição.', 1, '2026-08-14 12:00:00'),
(9, 4, 6, 'Claro! Podemos agendar uma visita?', 1, '2026-08-13 12:00:00'),
(10, 4, 13, 'Perfeito, te aguardo então.', 1, '2026-08-12 12:00:00'),
(11, 5, 7, 'Gostaria de mais informações sobre o animal.', 0, '2026-08-12 12:00:00'),
(12, 5, 14, 'Ele é muito dócil e saudável, sem problemas.', 0, '2026-08-11 12:00:00'),
(13, 6, 8, 'Olá! Tenho muito interesse em adotar.', 0, '2026-08-10 12:00:00'),
(14, 6, 15, 'Qualquer dúvida estou à disposição.', 1, '2026-08-09 12:00:00'),
(15, 7, 9, 'Claro! Podemos agendar uma visita?', 1, '2026-08-09 12:00:00'),
(16, 7, 16, 'Sim, amanhã à tarde está bom pra mim.', 0, '2026-08-08 12:00:00'),
(17, 8, 10, 'Gostaria de mais informações sobre o animal.', 1, '2026-08-08 12:00:00'),
(18, 8, 17, 'Ele é muito dócil e saudável, sem problemas.', 0, '2026-08-07 12:00:00'),
(19, 9, 11, 'Obrigado pelas informações, vou pensar.', 1, '2026-08-05 12:00:00'),
(20, 9, 18, 'Qualquer dúvida estou à disposição.', 1, '2026-08-04 12:00:00'),
(21, 10, 12, 'Olá! Tenho muito interesse em adotar.', 0, '2026-08-04 12:00:00'),
(22, 10, 19, 'Claro! Podemos agendar uma visita?', 0, '2026-08-03 12:00:00'),
(23, 11, 13, 'Sim, amanhã à tarde está bom pra mim.', 1, '2026-08-03 12:00:00'),
(24, 11, 20, 'Perfeito, te aguardo então.', 1, '2026-08-02 12:00:00'),
(25, 12, 14, 'Gostaria de mais informações sobre o animal.', 1, '2026-08-01 12:00:00'),
(26, 12, 3, 'Ele é muito dócil e saudável, sem problemas.', 0, '2026-07-31 12:00:00'),
(27, 13, 15, 'Obrigado pelas informações, vou pensar.', 0, '2026-07-31 12:00:00'),
(28, 13, 4, 'Qualquer dúvida estou à disposição.', 1, '2026-07-30 12:00:00'),
(29, 14, 16, 'Olá! Tenho muito interesse em adotar.', 1, '2026-07-30 12:00:00'),
(30, 14, 5, 'Claro! Podemos agendar uma visita?', 0, '2026-07-29 12:00:00'),
(31, 15, 17, 'Sim, amanhã à tarde está bom pra mim.', 1, '2026-07-27 12:00:00'),
(32, 15, 6, 'Perfeito, te aguardo então.', 0, '2026-07-26 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `HISTORICO_SOLICITACAO`
-- --------------------------------------------------------
INSERT INTO `HISTORICO_SOLICITACAO` (`historico_id`, `solicitacao_id`, `usuario_responsavel_id`, `status_antigo`, `status_novo`, `data_alteracao`) VALUES
(1, 2, 1, 'pendente', 'em_analise', '2026-08-17 12:00:00'),
(2, 3, 2, 'pendente', 'em_analise', '2026-08-16 12:00:00'),
(3, 3, 1, 'em_analise', 'aprovada', '2026-08-15 12:00:00'),
(4, 4, 3, 'pendente', 'em_analise', '2026-08-15 12:00:00'),
(5, 4, 4, 'em_analise', 'reprovada', '2026-08-14 12:00:00'),
(6, 5, 5, 'pendente', 'cancelada', '2026-08-14 12:00:00'),
(7, 6, 6, 'pendente', 'em_analise', '2026-08-13 12:00:00'),
(8, 6, 7, 'em_analise', 'aprovada', '2026-08-12 12:00:00'),
(9, 7, 8, 'pendente', 'em_analise', '2026-08-12 12:00:00'),
(10, 9, 9, 'pendente', 'em_analise', '2026-08-10 12:00:00'),
(11, 9, 10, 'em_analise', 'reprovada', '2026-08-09 12:00:00'),
(12, 10, 11, 'pendente', 'em_analise', '2026-08-09 12:00:00'),
(13, 11, 12, 'pendente', 'em_analise', '2026-08-08 12:00:00'),
(14, 11, 13, 'em_analise', 'aprovada', '2026-08-07 12:00:00'),
(15, 12, 14, 'pendente', 'cancelada', '2026-08-07 12:00:00'),
(16, 14, 15, 'pendente', 'em_analise', '2026-08-05 12:00:00'),
(17, 15, 16, 'pendente', 'em_analise', '2026-08-04 12:00:00'),
(18, 15, 17, 'em_analise', 'reprovada', '2026-08-03 12:00:00'),
(19, 16, 18, 'pendente', 'em_analise', '2026-08-03 12:00:00'),
(20, 16, 19, 'em_analise', 'aprovada', '2026-08-02 12:00:00'),
(21, 18, 20, 'pendente', 'em_analise', '2026-08-01 12:00:00'),
(22, 19, 1, 'pendente', 'em_analise', '2026-07-31 12:00:00'),
(23, 19, 2, 'em_analise', 'aprovada', '2026-07-30 12:00:00'),
(24, 20, 3, 'pendente', 'em_analise', '2026-07-30 12:00:00'),
(25, 20, 4, 'em_analise', 'reprovada', '2026-07-29 12:00:00'),
(26, 22, 5, 'pendente', 'cancelada', '2026-07-28 12:00:00'),
(27, 23, 6, 'pendente', 'em_analise', '2026-07-27 12:00:00'),
(28, 23, 7, 'em_analise', 'aprovada', '2026-07-26 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `HISTORICO_STATUS_ANIMAL`
-- --------------------------------------------------------
INSERT INTO `HISTORICO_STATUS_ANIMAL` (`historico_id`, `animal_id`, `status_antigo`, `status_novo`, `data_alteracao`) VALUES
(1, 1, NULL, 'disponivel', '2026-08-05 12:00:00'),
(2, 1, 'disponivel', 'em_analise', '2026-08-03 12:00:00'),
(3, 2, NULL, 'disponivel', '2026-08-04 12:00:00'),
(4, 3, NULL, 'disponivel', '2026-08-03 12:00:00'),
(5, 4, NULL, 'disponivel', '2026-08-02 12:00:00'),
(6, 5, NULL, 'disponivel', '2026-08-01 12:00:00'),
(7, 5, 'disponivel', 'em_analise', '2026-07-30 12:00:00'),
(8, 5, 'em_analise', 'adotado', '2026-07-28 12:00:00'),
(9, 6, NULL, 'disponivel', '2026-07-31 12:00:00'),
(10, 6, 'disponivel', 'desativado', '2026-07-29 12:00:00'),
(11, 7, NULL, 'disponivel', '2026-07-30 12:00:00'),
(12, 8, NULL, 'disponivel', '2026-07-29 12:00:00'),
(13, 9, NULL, 'disponivel', '2026-07-28 12:00:00'),
(14, 10, NULL, 'disponivel', '2026-07-27 12:00:00'),
(15, 11, NULL, 'disponivel', '2026-07-26 12:00:00'),
(16, 12, NULL, 'disponivel', '2026-07-25 12:00:00'),
(17, 12, 'disponivel', 'em_analise', '2026-07-23 12:00:00'),
(18, 13, NULL, 'disponivel', '2026-07-24 12:00:00'),
(19, 14, NULL, 'disponivel', '2026-07-23 12:00:00'),
(20, 15, NULL, 'disponivel', '2026-07-22 12:00:00'),
(21, 15, 'disponivel', 'desativado', '2026-07-20 12:00:00'),
(22, 16, NULL, 'disponivel', '2026-07-21 12:00:00'),
(23, 17, NULL, 'disponivel', '2026-07-20 12:00:00'),
(24, 18, NULL, 'disponivel', '2026-07-19 12:00:00'),
(25, 19, NULL, 'disponivel', '2026-07-18 12:00:00'),
(26, 19, 'disponivel', 'em_analise', '2026-07-16 12:00:00'),
(27, 19, 'em_analise', 'adotado', '2026-07-14 12:00:00'),
(28, 20, NULL, 'disponivel', '2026-07-17 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `DENUNCIA`
-- --------------------------------------------------------
INSERT INTO `DENUNCIA` (`denuncia_id`, `denunciante_id`, `denunciado_id`, `perfil_denunciado`, `solicitacao_id`, `chat_id`, `motivo`, `descricao`, `status_denuncia`, `decisao_admin`, `criado_em`) VALUES
(1, 3, 10, 'adotante', '1', '1', 'maus_tratos', 'Descrição detalhada do ocorrido relatado pelo denunciante.', 'aberta', NULL, '2026-08-18 12:00:00'),
(2, 5, 15, 'protetor', '5', '5', 'fraude', 'Descrição detalhada do ocorrido relatado pelo denunciante.', 'em_analise', 'colocar_em_analise', '2026-08-15 12:00:00'),
(3, 7, 20, 'usuario', NULL, NULL, 'assedio', 'Descrição detalhada do ocorrido relatado pelo denunciante.', 'aprovada', 'aprovar', '2026-08-10 12:00:00'),
(4, 2, 12, 'ong', '12', '12', 'outro', 'Descrição detalhada do ocorrido relatado pelo denunciante.', 'reprovada', 'reprovar', '2026-08-05 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `ADVERTENCIA`
-- --------------------------------------------------------
INSERT INTO `ADVERTENCIA` (`advertencia_id`, `usuario_id`, `denuncia_id`, `perfil_afetado`, `data_fim`, `status`, `peso_status`, `criado_em`) VALUES
(1, 20, 3, 'usuario', '2026-09-18', 'ativa', 'media', '2026-08-09 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `CONTESTACAO`
-- --------------------------------------------------------
INSERT INTO `CONTESTACAO` (`contestacao_id`, `advertencia_id`, `justificativa`, `parecer_admin`, `data_hora`) VALUES
(1, 1, 'Não concordo com a advertência, considero que houve um mal-entendido na situação.', 'Advertência mantida após análise do comitê administrativo.', '2026-08-08 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `NOTIFICACAO`
-- --------------------------------------------------------
INSERT INTO `NOTIFICACAO` (`notificacao_id`, `referencia_id`, `usuario_id`, `tipo_notificacao`, `lida`, `txt_notificacao`, `criado_em`) VALUES
(1, 1, 10, 'solicitacao', 0, 'Nova solicitação de adoção recebida.', '2026-08-19 12:00:00'),
(2, 1, 3, 'mensagem', 1, 'Você recebeu uma nova mensagem no chat.', '2026-08-17 12:00:00'),
(3, 2, 11, 'denuncia', 0, 'Uma denúncia foi registrada envolvendo seu perfil.', '2026-08-15 12:00:00'),
(4, 3, 7, 'contestacao', 1, 'Sua contestação foi analisada pela administração.', '2026-08-13 12:00:00'),
(5, 4, 20, 'advertencia', 0, 'Você recebeu uma advertência em sua conta.', '2026-08-11 12:00:00'),
(6, 5, 15, 'sistema', 1, 'Atualização importante do sistema CãoNectados.', '2026-08-09 12:00:00'),
(7, 6, 8, 'solicitacao', 0, 'Nova solicitação de adoção recebida.', '2026-08-07 12:00:00'),
(8, 7, 4, 'mensagem', 1, 'Você recebeu uma nova mensagem no chat.', '2026-08-05 12:00:00'),
(9, 8, 12, 'denuncia', 0, 'Uma denúncia foi registrada envolvendo seu perfil.', '2026-08-03 12:00:00'),
(10, 9, 9, 'contestacao', 0, 'Sua contestação foi analisada pela administração.', '2026-08-01 12:00:00'),
(11, 10, 16, 'advertencia', 1, 'Você recebeu uma advertência em sua conta.', '2026-07-30 12:00:00'),
(12, 11, 5, 'sistema', 0, 'Atualização importante do sistema CãoNectados.', '2026-07-28 12:00:00'),
(13, 12, 13, 'solicitacao', 1, 'Nova solicitação de adoção recebida.', '2026-07-26 12:00:00'),
(14, 13, 6, 'mensagem', 0, 'Você recebeu uma nova mensagem no chat.', '2026-07-24 12:00:00'),
(15, 14, 18, 'denuncia', 1, 'Uma denúncia foi registrada envolvendo seu perfil.', '2026-07-22 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `CODIGO_VERIFICACAO`
-- --------------------------------------------------------
INSERT INTO `CODIGO_VERIFICACAO` (`codigo_id`, `usuario_id`, `codigo`, `expira_em`, `usado`, `criado_em`) VALUES
(1, 3, '482735', '2026-08-18 12:00:00', 1, '2026-08-17 12:00:00'),
(2, 7, '193827', '2026-08-16 12:00:00', 0, '2026-08-15 12:00:00'),
(3, 12, '645291', '2026-08-14 12:00:00', 1, '2026-08-13 12:00:00'),
(4, 5, '837462', '2026-08-12 12:00:00', 0, '2026-08-11 12:00:00'),
(5, 19, '928374', '2026-08-10 12:00:00', 1, '2026-08-09 12:00:00'),
(6, 9, '562918', '2026-08-08 12:00:00', 0, '2026-08-07 12:00:00'),
(7, 14, '374829', '2026-08-06 12:00:00', 1, '2026-08-05 12:00:00'),
(8, 2, '182736', '2026-08-04 12:00:00', 0, '2026-08-03 12:00:00'),
(9, 17, '827364', '2026-08-02 12:00:00', 1, '2026-08-01 12:00:00'),
(10, 8, '564738', '2026-07-31 12:00:00', 0, '2026-07-30 12:00:00');

-- --------------------------------------------------------
-- Dados para a tabela `LOG_SISTEMA`
-- --------------------------------------------------------
INSERT INTO `LOG_SISTEMA` (`log_id`, `usuario_id`, `data_hora`, `acao`, `classe_afetada`, `registro_id`, `ip_usuario`) VALUES
(1, 3, '2026-08-18 12:00:00', 'Cadastrou animal', 'ANIMAL', 1, '192.168.1.1'),
(2, 7, '2026-08-16 12:00:00', 'Aprovou solicitação', 'SOLICITACAO_ADOCAO', 3, '10.0.0.1'),
(3, 12, '2026-08-14 12:00:00', 'Reprovou solicitação', 'SOLICITACAO_ADOCAO', 4, '172.16.0.1'),
(4, 5, '2026-08-12 12:00:00', 'Atualizou perfil', 'USUARIO', 10, '192.168.0.1'),
(5, 19, '2026-08-10 12:00:00', 'Registrou denúncia', 'DENUNCIA', 2, '10.10.10.1'),
(6, 9, '2026-08-08 12:00:00', 'Aplicou advertência', 'ADVERTENCIA', 1, '172.31.0.1'),
(7, 14, '2026-08-06 12:00:00', 'Validou protetor', 'PROTETOR', 2, '192.168.100.1'),
(8, 2, '2026-08-04 12:00:00', 'Cadastrou animal', 'ANIMAL', 5, '10.20.30.1'),
(9, 17, '2026-08-02 12:00:00', 'Aprovou solicitação', 'SOLICITACAO_ADOCAO', 6, '172.16.100.1'),
(10, 8, '2026-07-31 12:00:00', 'Reprovou solicitação', 'SOLICITACAO_ADOCAO', 9, '192.168.50.1'),
(11, 11, '2026-07-29 12:00:00', 'Atualizou perfil', 'USUARIO', 15, '10.0.0.5'),
(12, 6, '2026-07-27 12:00:00', 'Registrou denúncia', 'DENUNCIA', 4, '172.16.200.1');

--
-- Ajusta os AUTO_INCREMENT das tabelas alteradas
--
ALTER TABLE `REGIAO` AUTO_INCREMENT = 21;
ALTER TABLE `ESPECIE` AUTO_INCREMENT = 3;
ALTER TABLE `TRACO` AUTO_INCREMENT = 16;
ALTER TABLE `RACA` AUTO_INCREMENT = 9;
ALTER TABLE `USUARIO` AUTO_INCREMENT = 21;
ALTER TABLE `PROTETOR` AUTO_INCREMENT = 7;
ALTER TABLE `ADOTANTE` AUTO_INCREMENT = 8;
ALTER TABLE `ANIMAL` AUTO_INCREMENT = 21;
ALTER TABLE `ANIMAL_TRACO` AUTO_INCREMENT = 62;
ALTER TABLE `FOTO_ANIMAL` AUTO_INCREMENT = 24;
ALTER TABLE `PAGINA` AUTO_INCREMENT = 7;
ALTER TABLE `REDE` AUTO_INCREMENT = 13;
ALTER TABLE `SOLICITACAO_ADOCAO` AUTO_INCREMENT = 25;
ALTER TABLE `CHAT` AUTO_INCREMENT = 16;
ALTER TABLE `MENSAGEM` AUTO_INCREMENT = 33;
ALTER TABLE `HISTORICO_SOLICITACAO` AUTO_INCREMENT = 29;
ALTER TABLE `HISTORICO_STATUS_ANIMAL` AUTO_INCREMENT = 29;
ALTER TABLE `DENUNCIA` AUTO_INCREMENT = 5;
ALTER TABLE `ADVERTENCIA` AUTO_INCREMENT = 2;
ALTER TABLE `CONTESTACAO` AUTO_INCREMENT = 2;
ALTER TABLE `NOTIFICACAO` AUTO_INCREMENT = 16;
ALTER TABLE `CODIGO_VERIFICACAO` AUTO_INCREMENT = 11;
ALTER TABLE `LOG_SISTEMA` AUTO_INCREMENT = 13;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
