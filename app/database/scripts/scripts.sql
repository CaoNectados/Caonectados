-- Estrutura do banco CãoNectados (MySQL 5.7+ / MariaDB).
-- Selecione o banco correto no phpMyAdmin antes de executar este arquivo.
-- Para preencher catálogos e dados locais de demonstração, execute depois scripts_dados.sql.
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
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_especie_nome (nome)
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
    termos_aceitos_em TIMESTAMP NULL DEFAULT NULL,
    versao_termos VARCHAR(20) NULL DEFAULT NULL,
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
    petiscos_diarios INT UNSIGNED NOT NULL DEFAULT 10,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_adotante_usuario_id (usuario_id),
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
    motivo_recusa TEXT NULL,
    inadimplente BOOLEAN NOT NULL DEFAULT FALSE,
    motivo_inadimplencia TEXT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_protetor_usuario_id (usuario_id),
    CONSTRAINT fk_protetor_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id) ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS RACA (
    raca_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    especie_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_raca_especie_nome (especie_id, nome),
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
    UNIQUE KEY uq_pagina_protetor_id (protetor_id),
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
    INDEX idx_solicitacao_adotante_dia (adotante_id, data_solicitacao),
    INDEX idx_solicitacao_animal_estado (animal_id, status_solicitacao),
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
CREATE TABLE IF NOT EXISTS HISTORICO_CLASSIFICACAO_PROTETOR (
    historico_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    inadimplente BOOLEAN NOT NULL,
    motivo TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_classificacao_protetor FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id),
    CONSTRAINT fk_classificacao_admin FOREIGN KEY (admin_id) REFERENCES USUARIO (usuario_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
