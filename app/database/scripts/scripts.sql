CREATE DATABASE IF NOT EXISTS caonectados
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE caonectados;

CREATE TABLE IF NOT EXISTS REGIOES (
	regiao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	nome_regiao VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ESPECIES (
	especie_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS TRACOS (
	traco_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	traco VARCHAR(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS USUARIOS (
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
    CONSTRAINT fk_usuarios_regioes
        FOREIGN KEY (regiao_id) REFERENCES REGIOES (regiao_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS TUTORES (
	tutor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	usuario_id INT UNSIGNED NOT NULL,
	tipo_morada ENUM('casa', 'apartamento', 'sitio') NOT NULL,
	foto_perfil VARCHAR(255) NULL,
	descricao TEXT NULL,
	tamanho_interno_morada ENUM('pequeno', 'medio', 'grande') NULL,
	detalhes TEXT NULL,
	CONSTRAINT fk_tutores_usuarios
		FOREIGN KEY (usuario_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PROTETORES (
	protetor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	usuario_id INT UNSIGNED NOT NULL,
	validado BOOLEAN NOT NULL DEFAULT FALSE,
	data_validacao TIMESTAMP NULL DEFAULT NULL,
	codigo_documento VARCHAR(20) NOT NULL,
	tipo_documento ENUM('cpf', 'cnpj') NOT NULL,
	nome_fantasia VARCHAR(45) NOT NULL,
	CONSTRAINT fk_protetores_usuarios
		FOREIGN KEY (usuario_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS RACA (
	raca_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	especie_id INT UNSIGNED NOT NULL,
	nome VARCHAR(100) NOT NULL,
	CONSTRAINT fk_raca_especies
		FOREIGN KEY (especie_id) REFERENCES ESPECIES (especie_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ANIMAIS (
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
	CONSTRAINT fk_animais_protetores
		FOREIGN KEY (protetor_id) REFERENCES PROTETORES (protetor_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_animais_raca
		FOREIGN KEY (raca_id) REFERENCES RACA (raca_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS FOTOS_ANIMAIS (
	foto_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	animal_id INT UNSIGNED NOT NULL,
	caminho_foto VARCHAR(255) NOT NULL,
	foto_principal BOOLEAN NOT NULL DEFAULT FALSE,
	CONSTRAINT fk_fotos_animais_animais
		FOREIGN KEY (animal_id) REFERENCES ANIMAIS (animal_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PAGINAS (
	pagina_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	protetor_id INT UNSIGNED NOT NULL,
	descricao TEXT NULL,
	foto_fundo VARCHAR(255) NULL,
	foto_perfil VARCHAR(255) NULL,
	CONSTRAINT fk_paginas_protetores
		FOREIGN KEY (protetor_id) REFERENCES PROTETORES (protetor_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS REDES (
	rede_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	protetor_id INT UNSIGNED NOT NULL,
	link_rede VARCHAR(255) NOT NULL,
	tipo_rede ENUM('instagram', 'facebook', 'whatsapp', 'site', 'tiktok', 'youtube', 'outro') NOT NULL,
	CONSTRAINT fk_redes_protetores
		FOREIGN KEY (protetor_id) REFERENCES PROTETORES (protetor_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS SOLICITACOES_ADOCAO (
	solicitacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	tutor_id INT UNSIGNED NOT NULL,
	animal_id INT UNSIGNED NOT NULL,
	status_solicitacao ENUM('pendente', 'em_analise', 'aprovada', 'reprovada', 'cancelada') NOT NULL DEFAULT 'pendente',
	data_solicitacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	justificativa_recusa TEXT NULL,
	data_finalizacao TIMESTAMP NULL DEFAULT NULL,
	CONSTRAINT fk_solicitacoes_tutores
		FOREIGN KEY (tutor_id) REFERENCES TUTORES (tutor_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_solicitacoes_animais
		FOREIGN KEY (animal_id) REFERENCES ANIMAIS (animal_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CHATS (
	chat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	solicitacao_id INT UNSIGNED NOT NULL,
	status ENUM('ativo', 'encerrado', 'arquivado') NOT NULL DEFAULT 'ativo',
	criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_chats_solicitacoes
		FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACOES_ADOCAO (solicitacao_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS MENSAGENS (
	mensagem_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	chat_id INT UNSIGNED NOT NULL,
	remetente_id INT UNSIGNED NOT NULL,
	texto TEXT NOT NULL,
	lida BOOLEAN NOT NULL DEFAULT FALSE,
	data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_mensagens_chats
		FOREIGN KEY (chat_id) REFERENCES CHATS (chat_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_mensagens_usuarios
		FOREIGN KEY (remetente_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS NOTIFICACOES (
	notificacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	referencia_id INT UNSIGNED NULL,
	usuario_id INT UNSIGNED NOT NULL,
	tipo_notificacao ENUM('solicitacao', 'mensagem', 'denuncia', 'contestacao', 'advertencia', 'sistema') NOT NULL,
	lida BOOLEAN NOT NULL DEFAULT FALSE,
	txt_notificacao TEXT NOT NULL,
	criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_notificacoes_usuarios
		FOREIGN KEY (usuario_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS DENUNCIAS (
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
	CONSTRAINT fk_denuncias_denunciante
		FOREIGN KEY (denunciante_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_denuncias_denunciado
		FOREIGN KEY (denunciado_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_denuncias_solicitacoes
		FOREIGN KEY (solicitacao_id) REFERENCES SOLICITACOES_ADOCAO (solicitacao_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_denuncias_chats
		FOREIGN KEY (chat_id) REFERENCES CHATS (chat_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ADVERTENCIAS (
	advertencia_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	usuario_id INT UNSIGNED NOT NULL,
	denuncia_id INT UNSIGNED NOT NULL,
	data_fim DATE NULL,
	status ENUM('ativa', 'suspensa', 'encerrada') NOT NULL DEFAULT 'ativa',
	peso_status ENUM('leve', 'media', 'grave') NOT NULL DEFAULT 'leve',
	criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_advertencias_usuarios
		FOREIGN KEY (usuario_id) REFERENCES USUARIOS (usuario_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_advertencias_denuncias
		FOREIGN KEY (denuncia_id) REFERENCES DENUNCIAS (denuncia_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CONTESTACOES (
	contestacao_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	advertencia_id INT UNSIGNED NOT NULL,
	justificativa TEXT NOT NULL,
	parecer_admin TEXT NULL,
	data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_contestacoes_advertencias
		FOREIGN KEY (advertencia_id) REFERENCES ADVERTENCIAS (advertencia_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ANIMAIS_TRACOS (
	animal_traco_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	animal_id INT UNSIGNED NOT NULL,
	traco_id INT UNSIGNED NOT NULL,
	opcao_id VARCHAR(200) NOT NULL,
	CONSTRAINT fk_animais_tracos_animais
		FOREIGN KEY (animal_id) REFERENCES ANIMAIS (animal_id)
		ON UPDATE CASCADE,
	CONSTRAINT fk_animais_tracos_tracos
		FOREIGN KEY (traco_id) REFERENCES TRACOS (traco_id)
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
