<?php

namespace app\models;

class Protetor
{
    private ?int $protetor_id = null;
    private ?int $usuario_id = null;
    private bool $validado = false;
    private ?string $data_validacao = null;
    private ?string $codigo_documento = null;
    private ?string $tipo_documento = null;
    private ?string $nome_fantasia = null;

    // Getters e Setters
    public function getProtetorId(): ?int { return $this->protetor_id; }
    public function setProtetorId(?int $protetor_id): void { $this->protetor_id = $protetor_id; }

    public function getUsuarioId(): ?int { return $this->usuario_id; }
    public function setUsuarioId(?int $usuario_id): void { $this->usuario_id = $usuario_id; }

    public function isValidado(): bool { return $this->validado; }
    public function setValidado(bool $validado): void { $this->validado = $validado; }

    public function getDataValidacao(): ?string { return $this->data_validacao; }
    public function setDataValidacao(?string $data_validacao): void { $this->data_validacao = $data_validacao; }

    public function getCodigoDocumento(): ?string { return $this->codigo_documento; }
    public function setCodigoDocumento(?string $codigo_documento): void { $this->codigo_documento = $codigo_documento; }

    public function getTipoDocumento(): ?string { return $this->tipo_documento; }
    public function setTipoDocumento(?string $tipo_documento): void { $this->tipo_documento = $tipo_documento; }

    public function getNomeFantasia(): ?string { return $this->nome_fantasia; }
    public function setNomeFantasia(?string $nome_fantasia): void { $this->nome_fantasia = $nome_fantasia; }
}