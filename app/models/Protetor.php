<?php

namespace app\models;

class Protetor
{
    private ?int $protetor_id = null;
    private ?int $usuario_id = null;
    private ?bool $validado = null;
    private ?string $data_validacao = null;
    private ?string $codigo_documento = null;
    private ?string $tipo_documento = null;
    private ?string $nome_fantasia = null;
    private ?string $data_abertura_cnpj = null; 
    private ?string $comprovante_documento = null;
    private ?string $criado_em = null;
    private ?string $deletado_em = null;

    // Getters e Setters
    public function getProtetorId(): ?int { return $this->protetor_id; }
    public function setProtetorId(?int $protetor_id): void { $this->protetor_id = $protetor_id; }

    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function setUsuarioId(?int $usuario_id): void { $this->usuario_id = $usuario_id; }

    public function getValidado(): ?bool { return $this->validado; }
    public function setValidado(?bool $validado): void { $this->validado = $validado; }

    public function getDataValidacao(): ?string { return $this->data_validacao; }
    public function setDataValidacao(?string $data_validacao): void { $this->data_validacao = $data_validacao; }

    public function getCodigoDocumento(): ?string { return $this->codigo_documento; }
    public function setCodigoDocumento(?string $codigo_documento): void { $this->codigo_documento = $codigo_documento; }

    public function getTipoDocumento(): ?string { return $this->tipo_documento; }
    public function setTipoDocumento(?string $tipo_documento): void { $this->tipo_documento = $tipo_documento; }

    public function getNomeFantasia(): ?string { return $this->nome_fantasia; }
    public function setNomeFantasia(?string $nome_fantasia): void { $this->nome_fantasia = $nome_fantasia; }

    public function getDataAberturaCnpj(): ?string { return $this->data_abertura_cnpj; }
    public function setDataAberturaCnpj(?string $data_abertura_cnpj): void { $this->data_abertura_cnpj = $data_abertura_cnpj; }

    public function getComprovanteDocumento(): ?string { return $this->comprovante_documento; }
    public function setComprovanteDocumento(?string $comprovante_documento): void { $this->comprovante_documento = $comprovante_documento; }

    public function getCriadoEm(): ?string { return $this->criado_em; }
    public function setCriadoEm(?string $criado_em): void { $this->criado_em = $criado_em; }

    public function getDeletadoEm(): ?string { return $this->deletado_em; }
    public function setDeletadoEm(?string $deletado_em): void { $this->deletado_em = $deletado_em; }
}