CREATE DATABASE IF NOT EXISTS caonectados
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE caonectados;

CREATE TABLE IF NOT EXISTS REGIAO (
    regiao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_regiao VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ESPECIE (
    especie_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS TRACO (
    traco_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    traco VARCHAR(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS USUARIO (
    usuario_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    regiao_id INT UNSIGNED NULL,
    telefone VARCHAR(20) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_perfil ENUM('usuario', 'adotante', 'protetor', 'administrador') NOT NULL DEFAULT 'usuario',
    status_conta ENUM('pendente', 'ativo', 'bloqueado', 'inativo') NOT NULL DEFAULT 'pendente',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(150) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    num_morada VARCHAR(20) NOT NULL,
    obs_casa TEXT NULL,
    dt_nasc DATE NULL,
    cpf VARCHAR(20) NOT NULL,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_usuario_regiao
        FOREIGN KEY (regiao_id) REFERENCES REGIAO (regiao_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS TUTOR (
    tutor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_morada ENUM('casa', 'apartamento', 'sitio') NOT NULL,
    foto_perfil VARCHAR(255) NULL,
    descricao TEXT NULL,
    tamanho_interno_morada ENUM('pequeno', 'medio', 'grande') NULL,
    detalhes TEXT NULL,
    CONSTRAINT fk_tutor_usuario
        FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PROTETOR (
    protetor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    validado BOOLEAN NOT NULL DEFAULT FALSE,
    data_validacao TIMESTAMP NULL DEFAULT NULL,
    codigo_documento VARCHAR(20) NOT NULL,
    tipo_documento ENUM('cpf', 'cnpj') NOT NULL,
    nome_fantasia VARCHAR(45) NOT NULL,
    CONSTRAINT fk_protetor_usuario
        FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS RACA (
    raca_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    especie_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    CONSTRAINT fk_raca_especie
        FOREIGN KEY (especie_id) REFERENCES ESPECIE (especie_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ANIMAL (
    animal_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    raca_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    dt_nasc DATE NULL,
    sexo ENUM('macho', 'femea', 'indefinido') NOT NULL,
    porte ENUM('pequeno', 'medio', 'grande') NOT NULL,
    status ENUM('disponivel', 'em_analise', 'adotado', 'desativado') NOT NULL DEFAULT 'disponivel',
    descricao TEXT NULL,
    vacinado BOOLEAN NOT NULL DEFAULT FALSE,
    castrado BOOLEAN NOT NULL DEFAULT FALSE,
    comportamento ENUM('calmo', 'ativo', 'docil', 'arisco', 'indefinido') NULL,
    historico_saude TEXT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    atualizado_em TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_animal_protetor
        FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_animal_raca
        FOREIGN KEY (raca_id) REFERENCES RACA (raca_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS FOTO_ANIMAL (
    foto_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_id INT UNSIGNED NOT NULL,
    caminho_foto VARCHAR(255) NOT NULL,
    foto_principal BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_foto_animal_animal
        FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PAGINA (
    pagina_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    descricao TEXT NULL,
    foto_fundo VARCHAR(255) NULL,
    foto_perfil VARCHAR(255) NULL,
    CONSTRAINT fk_pagina_protetor
        FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS REDE (
    rede_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protetor_id INT UNSIGNED NOT NULL,
    link_rede VARCHAR(255) NOT NULL,
    tipo_rede ENUM('instagram', 'facebook', 'whatsapp', 'site', 'tiktok', 'youtube', 'outro') NOT NULL,
    CONSTRAINT fk_rede_protetor
        FOREIGN KEY (protetor_id) REFERENCES PROTETOR (protetor_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS SOLICITACAO_ADOCAO (
    solicitacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT UNSIGNED NOT NULL,
    animal_id INT UNSIGNED NOT NULL,
    status_solicitacao ENUM('pendente', 'em_analise', 'aprovada', 'reprovada', 'cancelada') NOT NULL DEFAULT 'pendente',
    data_solicitacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    justificativa_recusa TEXT NULL,
    data_finalizacao TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_solicitacao_tutor
        FOREIGN KEY (tutor_id) REFERENCES TUTOR (tutor_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_solicitacao_animal
        FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CHAT (
    chat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT UNSIGNED NOT NULL,
    status ENUM('ativo', 'encerrado', 'arquivado') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_solicitacao
        FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACAO_ADOCAO (solicitacao_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS MENSAGEM (
    mensagem_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id INT UNSIGNED NOT NULL,
    remetente_id INT UNSIGNED NOT NULL,
    texto TEXT NOT NULL,
    lida BOOLEAN NOT NULL DEFAULT FALSE,
    data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensagem_chat
        FOREIGN KEY (chat_id) REFERENCES CHAT (chat_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_mensagem_usuario
        FOREIGN KEY (remetente_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS NOTIFICACAO (
    notificacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referencia_id INT UNSIGNED NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_notificacao ENUM('solicitacao', 'mensagem', 'denuncia', 'contestacao', 'advertencia', 'sistema') NOT NULL,
    lida BOOLEAN NOT NULL DEFAULT FALSE,
    txt_notificacao TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacao_usuario
        FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS DENUNCIA (
    denuncia_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    denunciante_id INT UNSIGNED NOT NULL,
    denunciado_id INT UNSIGNED NOT NULL,
    solicitacao_id INT UNSIGNED NULL,
    chat_id INT UNSIGNED NULL,
    motivo ENUM('maus_tratos', 'abandono', 'fraude', 'assedio', 'outro') NOT NULL,
    descricao TEXT NOT NULL,
    status_denuncia ENUM('aberta', 'em_analise', 'aprovada', 'reprovada', 'arquivada') NOT NULL DEFAULT 'aberta',
    decisao_admin ENUM('aprovar', 'reprovar', 'colocar_em_analise') NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_denuncia_denunciante
        FOREIGN KEY (denunciante_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_denuncia_denunciado
        FOREIGN KEY (denunciado_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_denuncia_solicitacao
        FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACAO_ADOCAO (solicitacao_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_denuncia_chat
        FOREIGN KEY (chat_id) REFERENCES CHAT (chat_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ADVERTENCIA (
    advertencia_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    denuncia_id INT UNSIGNED NOT NULL,
    data_fim DATE NULL,
    status ENUM('ativa', 'suspensa', 'encerrada') NOT NULL DEFAULT 'ativa',
    peso_status ENUM('leve', 'media', 'grave') NOT NULL DEFAULT 'leve',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_advertencia_usuario
        FOREIGN KEY (usuario_id) REFERENCES USUARIO (usuario_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_advertencia_denuncia
        FOREIGN KEY (denuncia_id) REFERENCES DENUNCIA (denuncia_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CONTESTACAO (
    contestacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advertencia_id INT UNSIGNED NOT NULL,
    justificativa TEXT NOT NULL,
    parecer_admin TEXT NULL,
    data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contestacao_advertencia
        FOREIGN KEY (advertencia_id) REFERENCES ADVERTENCIA (advertencia_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ANIMAL_TRACO (
    animal_traco_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_id INT UNSIGNED NOT NULL,
    traco_id INT UNSIGNED NOT NULL,
    opcao_id VARCHAR(200) NOT NULL,
    CONSTRAINT fk_animal_traco_animal
        FOREIGN KEY (animal_id) REFERENCES ANIMAL (animal_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_animal_traco_traco
        FOREIGN KEY (traco_id) REFERENCES TRACO (traco_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;